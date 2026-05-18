<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ItemBatch;
use App\Models\Sale;
use App\Models\SaleRefund;
use App\Models\SaleRefundLine;
use App\Services\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SaleRefundController extends Controller
{
    public function index()
    {
        $refunds = SaleRefund::with(['sale.cashier', 'requestedBy', 'lines.saleLine.product'])
            ->where('status', 'PENDING')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.refunds.index', compact('refunds'));
    }

    public function create(Sale $sale)
    {
        abort_if($sale->status === 'DRAFT', 400, 'Hanya transaksi PAID yang bisa di-refund.');
        abort_if(
            $sale->refunds()->where('status', 'PENDING')->exists(),
            403,
            'Sudah ada permintaan refund yang menunggu persetujuan untuk transaksi ini.'
        );

        $sale->load(['lines.product', 'lines.refundLines' => function ($q) {
            $q->whereHas('refund', fn ($r) => $r->where('status', 'APPROVED'));
        }]);

        return view('cashier.refund', compact('sale'));
    }

    public function store(Request $request, Sale $sale)
    {
        $this->normalizeNumericInputs($request, ['lines.*.qty']);

        abort_if($sale->status === 'DRAFT', 400, 'Hanya transaksi PAID yang bisa di-refund.');
        abort_if(
            $sale->refunds()->where('status', 'PENDING')->exists(),
            403,
            'Sudah ada permintaan refund yang menunggu persetujuan.'
        );

        $sale->load(['lines.product', 'lines.refundLines' => function ($q) {
            $q->whereHas('refund', fn ($r) => $r->where('status', 'APPROVED'));
        }]);

        $request->validate([
            'lines'        => ['required', 'array', 'min:1'],
            'lines.*.qty'  => ['nullable', 'numeric', 'min:0'],
            'note'         => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $sale) {
            $totalRefund = 0;

            $refund = SaleRefund::create([
                'sale_id'      => $sale->id,
                'total_refund' => 0,
                'refunded_at'  => now(),
                'refunded_by'  => auth()->id(),
                'note'         => $request->note,
                'status'       => 'PENDING',
            ]);

            foreach ($sale->lines as $line) {
                $reqQty = (float) ($request->input("lines.{$line->id}.qty") ?? 0);
                if ($reqQty <= 0) {
                    continue;
                }

                $alreadyRefunded = (float) $line->refundLines->sum('qty');
                $maxRefund       = max(0, (float) $line->qty - $alreadyRefunded);
                $qty             = min($reqQty, $maxRefund);

                if ($qty <= 0) {
                    continue;
                }

                $amount       = $qty * (float) $line->price;
                $totalRefund += $amount;

                SaleRefundLine::create([
                    'refund_id'   => $refund->id,
                    'sale_line_id' => $line->id,
                    'qty'         => $qty,
                    'amount'      => $amount,
                ]);
            }

            if ($totalRefund <= 0) {
                throw ValidationException::withMessages([
                    'lines' => 'Masukkan minimal satu item yang ingin di-refund.',
                ]);
            }

            $refund->total_refund = $totalRefund;
            $refund->save();

            AuditLog::log(auth()->id(), 'SALE_REFUND_REQUEST', $sale, [
                'sale_id'      => $sale->id,
                'refund_id'    => $refund->id,
                'total_refund' => $totalRefund,
            ]);
        });

        $salesRoute = request()->routeIs('manager.*')
            ? 'manager.reports.sales'
            : (request()->routeIs('cashier.*') ? 'cashier.reports.sales' : 'admin.reports.sales');

        return redirect()
            ->route($salesRoute)
            ->with('status', 'Permintaan refund berhasil diajukan dan menunggu persetujuan.');
    }

    public function approve(SaleRefund $refund, UnitConverter $converter)
    {
        abort_unless(auth()->user()->can('action.refund.approve'), 403);
        abort_if($refund->status !== 'PENDING', 400, 'Hanya permintaan refund PENDING yang bisa disetujui.');

        $refund->load(['lines.saleLine.product.recipe.lines.item', 'sale']);
        $sale = $refund->sale;

        DB::transaction(function () use ($refund, $sale, $converter) {
            $itemsReturned = [];

            foreach ($refund->lines as $refundLine) {
                $saleLine = $refundLine->saleLine;
                $recipe   = $saleLine?->product?->recipe;
                $qty      = (float) $refundLine->qty;

                if ($recipe) {
                    foreach ($recipe->lines as $detail) {
                        $item = $detail->item;
                        if (! $item || ! $item->base_unit_id) {
                            continue;
                        }

                        try {
                            $qtyBase = $converter->toBase(
                                (float) $detail->qty,
                                (int) $detail->unit_id,
                                (int) $item->base_unit_id
                            ) * $qty;
                        } catch (RuntimeException) {
                            continue;
                        }

                        $itemsReturned[$item->id] = ($itemsReturned[$item->id] ?? 0) + $qtyBase;
                    }
                }
            }

            // Kembalikan stok ke batch aktif
            foreach ($itemsReturned as $itemId => $qtyBase) {
                $batch = ItemBatch::where('item_id', $itemId)
                    ->where('status', 'ACTIVE')
                    ->whereDate('expired_at', '>=', now()->toDateString())
                    ->orderBy('expired_at', 'asc')
                    ->lockForUpdate()
                    ->first();

                if (! $batch) {
                    $defaultExpiry = ItemBatch::where('item_id', $itemId)
                        ->orderByDesc('expired_at')
                        ->value('expired_at') ?? now()->addDays(30)->toDateString();

                    $batch = ItemBatch::create([
                        'item_id'           => $itemId,
                        'received_at'       => now(),
                        'expired_at'        => $defaultExpiry,
                        'qty_on_hand_base'  => 0,
                        'unit_cost_base'    => 0,
                        'status'            => 'ACTIVE',
                    ]);
                }

                $batch->qty_on_hand_base += $qtyBase;
                $batch->save();

                \App\Models\StockMove::create([
                    'moved_at'   => now(),
                    'item_id'    => $itemId,
                    'batch_id'   => $batch->id,
                    'qty_base'   => $qtyBase,
                    'type'       => 'REFUND',
                    'ref_type'   => 'sale_refund',
                    'ref_id'     => $refund->id,
                    'created_by' => auth()->id(),
                    'note'       => 'Refund sale #' . $sale->id,
                ]);
            }

            // Tandai APPROVED dulu agar hitungan fully-refunded ikut baris ini
            $refund->status      = 'APPROVED';
            $refund->approved_by = auth()->id();
            $refund->approved_at = now();
            $refund->save();

            // Update refund_total di sale
            $sale->refund_total = (float) $sale->refund_total + (float) $refund->total_refund;

            // Cek apakah semua item sudah ter-refund (hanya hitung APPROVED)
            $sale->load('lines');
            $fullyRefunded = $sale->lines->every(function ($line) {
                $approvedQty = (float) $line->refundLines()
                    ->whereHas('refund', fn ($q) => $q->where('status', 'APPROVED'))
                    ->sum('qty');
                return abs($approvedQty - (float) $line->qty) <= 0.000001;
            });

            $sale->status = $fullyRefunded ? 'REFUND' : 'PAID';
            $sale->save();

            AuditLog::log(auth()->id(), 'SALE_REFUND_APPROVED', $sale, [
                'sale_id'      => $sale->id,
                'refund_id'    => $refund->id,
                'total_refund' => $refund->total_refund,
                'items_returned' => $itemsReturned,
            ]);
        });

        return redirect()
            ->route(request()->routeIs('manager.*') ? 'manager.refunds.index' : 'admin.refunds.index')
            ->with('status', 'Refund #' . $refund->id . ' disetujui. Stok sudah dikembalikan.');
    }

    public function reject(Request $request, SaleRefund $refund)
    {
        abort_unless(auth()->user()->can('action.refund.approve'), 403);
        abort_if($refund->status !== 'PENDING', 400, 'Hanya permintaan refund PENDING yang bisa ditolak.');

        $data = $request->validate([
            'rejection_note' => ['required', 'string', 'max:255'],
        ]);

        $sale = $refund->sale;

        $refund->update([
            'status'         => 'REJECTED',
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
            'rejection_note' => $data['rejection_note'],
        ]);

        AuditLog::log(auth()->id(), 'SALE_REFUND_REJECTED', $sale, [
            'sale_id'        => $sale->id,
            'refund_id'      => $refund->id,
            'rejection_note' => $data['rejection_note'],
        ]);

        return redirect()
            ->route(request()->routeIs('manager.*') ? 'manager.refunds.index' : 'admin.refunds.index')
            ->with('status', 'Permintaan refund #' . $refund->id . ' ditolak.');
    }
}
