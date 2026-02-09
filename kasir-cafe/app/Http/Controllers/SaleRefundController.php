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

class SaleRefundController extends Controller
{
    public function create(Sale $sale)
    {
        $sale->load(['lines.product.recipe.lines.item', 'lines.refundLines']);

        abort_if($sale->status === 'DRAFT', 400, 'Hanya transaksi PAID yang bisa di-refund.');

        return view('cashier.refund', compact('sale'));
    }

    public function store(Request $request, Sale $sale, UnitConverter $converter)
    {
        $sale->load(['lines.product.recipe.lines.item', 'lines.refundLines']);

        abort_if($sale->status === 'DRAFT', 400, 'Hanya transaksi PAID yang bisa di-refund.');

        $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.qty' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $sale, $converter) {
            $totalRefund = 0;
            $refund = SaleRefund::create([
                'sale_id' => $sale->id,
                'total_refund' => 0,
                'refunded_at' => now(),
                'refunded_by' => auth()->id(),
                'note' => $request->note,
            ]);

            $itemsReturned = [];

            foreach ($sale->lines as $line) {
                $reqQty = (float) ($request->input("lines.{$line->id}.qty") ?? 0);
                if ($reqQty <= 0) {
                    continue;
                }

                $alreadyRefunded = (float) $line->refundLines->sum('qty');
                $maxRefund = max(0, (float) $line->qty - $alreadyRefunded);
                $qty = min($reqQty, $maxRefund);

                if ($qty <= 0) {
                    continue;
                }

                $amount = $qty * (float) $line->price;
                $totalRefund += $amount;

                SaleRefundLine::create([
                    'refund_id' => $refund->id,
                    'sale_line_id' => $line->id,
                    'qty' => $qty,
                    'amount' => $amount,
                ]);

                $recipe = $line->product?->recipe;
                if ($recipe) {
                    foreach ($recipe->lines as $detail) {
                        $item = $detail->item;
                        if (! $item || ! $item->base_unit_id) {
                            continue;
                        }

                        $qtyBase = $converter->toBase(
                            (float) $detail->qty,
                            (int) $detail->unit_id,
                            (int) $item->base_unit_id
                        ) * $qty;

                        $itemsReturned[$item->id] = ($itemsReturned[$item->id] ?? 0) + $qtyBase;
                    }
                }
            }

            // Kembalikan stok ke batch aktif (earliest expiry)
            foreach ($itemsReturned as $itemId => $qtyBase) {
                $remaining = $qtyBase;

                while ($remaining > 0.000001) {
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
                            'item_id' => $itemId,
                            'received_at' => now(),
                            'expired_at' => $defaultExpiry,
                            'qty_on_hand_base' => 0,
                            'unit_cost_base' => 0,
                            'status' => 'ACTIVE',
                        ]);
                    }

                    $batch->qty_on_hand_base += $remaining;
                    $batch->save();

                    $remaining = 0;

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
            }

            $refund->total_refund = $totalRefund;
            $refund->save();

            $sale->refund_total = (float) $sale->refund_total + $totalRefund;
            if ($sale->refund_total >= (float) $sale->grand_total) {
                $sale->status = 'REFUND';
            }
            $sale->save();

            AuditLog::log(auth()->id(), 'SALE_REFUND', $sale, [
                'sale_id' => $sale->id,
                'refund_id' => $refund->id,
                'total_refund' => $totalRefund,
                'items_returned' => $itemsReturned,
            ]);
        });

        return redirect()
            ->route(request()->routeIs('manager.*') ? 'manager.reports.sales' : (request()->routeIs('cashier.*') ? 'cashier.reports.sales' : 'admin.reports.sales'))
            ->with('status', 'Refund berhasil diproses.');
    }
}
