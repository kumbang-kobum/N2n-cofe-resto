<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Models\StockOpnameLine;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Services\FefoAllocator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::withCount('lines')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.stock_opname.index', compact('opnames'));
    }

    public function create()
    {
        $items = Item::with('baseUnit')->orderBy('name')->get();
        $units = Unit::orderBy('symbol')->get();

        return view('admin.stock_opname.create', compact('items', 'units'));
    }

    /**
     * Konversi qty dari unit input -> base unit item.
     */
    private function toBaseQty(int $itemBaseUnitId, int $inputUnitId, float $qtyInput): float
    {
        if ($qtyInput < 0) {
            return 0;
        }

        if ($inputUnitId === $itemBaseUnitId) {
            return $qtyInput;
        }

        $conv = UnitConversion::query()
            ->where('from_unit_id', $inputUnitId)
            ->where('to_unit_id', $itemBaseUnitId)
            ->first();

        if (!$conv) {
            // Coba arah sebaliknya (jika yang tersimpan to->from)
            $reverse = UnitConversion::query()
                ->where('from_unit_id', $itemBaseUnitId)
                ->where('to_unit_id', $inputUnitId)
                ->first();

            if ($reverse && (float)$reverse->multiplier != 0.0) {
                return $qtyInput / (float) $reverse->multiplier;
            }

            throw new \RuntimeException("Konversi unit tidak ditemukan.");
        }

        return $qtyInput * (float) $conv->multiplier;
    }

    public function store(Request $request)
    {
        // ✅ Form mengirim physical_qty & unit_id, bukan physical_qty_base
        $request->validate([
            'counted_at' => ['required', 'date'],
            'note'       => ['nullable', 'string'],

            'lines'              => ['required', 'array', 'min:1'],
            'lines.*.item_id'    => ['required', 'exists:items,id'],
            'lines.*.physical_qty' => ['required', 'numeric', 'min:0'],
            'lines.*.unit_id'    => ['required', 'exists:units,id'],
            'lines.*.expired_at' => ['nullable', 'date'],
            'lines.*.unit_cost_base' => ['nullable', 'numeric', 'min:0'],
        ], [
            'lines.*.physical_qty.required' => 'Qty fisik wajib diisi.',
            'lines.*.unit_id.required'      => 'Unit wajib dipilih.',
        ]);

        return DB::transaction(function () use ($request) {

            $countedAt = $request->date('counted_at');

            $opname = StockOpname::create([
                'code'       => StockOpname::nextCode($countedAt),
                'counted_at' => $countedAt,
                'status'     => 'DRAFT',
                'note'       => $request->note,
                'created_by' => auth()->id(),
            ]);

            // Optimasi query: ambil semua item sekali
            $itemIds = collect($request->lines)->pluck('item_id')->unique()->values();
            $items = Item::with('baseUnit')
                ->whereIn('id', $itemIds)
                ->get()
                ->keyBy('id');

            $linesInsert = [];

            foreach ($request->lines as $row) {
                /** @var Item $item */
                $item = $items[(int)$row['item_id']] ?? null;
                if (!$item) {
                    continue;
                }

                $systemQtyBase = (float) ItemBatch::query()
                    ->where('item_id', $item->id)
                    ->where('status', 'ACTIVE')
                    ->sum('qty_on_hand_base');

                $qtyInput = (float) $row['physical_qty'];
                $unitId   = (int) $row['unit_id'];

                try {
                    $physicalQtyBase = (float) $this->toBaseQty((int)$item->base_unit_id, $unitId, $qtyInput);
                } catch (\Throwable $e) {
                    return back()->withErrors([
                        "Konversi unit untuk item {$item->name} tidak ditemukan. Tambahkan di Unit Conversion."
                    ])->withInput();
                }

                $diff = $physicalQtyBase - $systemQtyBase;

                $linesInsert[] = [
                    'stock_opname_id'  => $opname->id,
                    'item_id'          => $item->id,
                    'system_qty_base'  => $systemQtyBase,
                    'physical_qty_base'=> $physicalQtyBase,
                    'diff_qty_base'    => $diff,

                    // boleh diisi dari create (opsional)
                    'expired_at'       => $row['expired_at'] ?? null,
                    'unit_cost_base'   => $row['unit_cost_base'] ?? null,

                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            StockOpnameLine::insert($linesInsert);

            AuditLog::log(auth()->id(), 'STOCK_OPNAME_CREATED', $opname, [
                'code'       => $opname->code,
                'counted_at' => (string) $opname->counted_at,
                'lines'      => count($linesInsert),
            ]);

            return redirect()->route('admin.stock_opname.show', $opname->id)
                ->with('status', 'Stock opname dibuat.');
        });
    }

    public function show($id)
    {
        $opname = StockOpname::with([
            'lines.item.baseUnit',
            'audits' => fn ($q) => $q->orderBy('id'),
        ])->findOrFail($id);

        return view('admin.stock_opname.show', compact('opname'));
    }

    public function edit($id)
    {
        $opname = StockOpname::with(['lines.item.baseUnit'])
            ->findOrFail($id);

        abort_if($opname->status !== 'DRAFT', 403, 'Hanya DRAFT yang bisa diedit.');

        $units = Unit::orderBy('symbol')->get();

        return view('admin.stock_opname.edit', compact('opname', 'units'));
    }

    public function update(Request $request, $id)
    {
        $opname = StockOpname::with(['lines.item.baseUnit'])->findOrFail($id);

        abort_if($opname->status !== 'DRAFT', 403, 'Hanya DRAFT yang bisa diedit.');

        // ✅ konsisten: edit juga pakai physical_qty + unit_id
        $request->validate([
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['required', 'exists:stock_opname_lines,id'],
            'lines.*.physical_qty' => ['required', 'numeric', 'min:0'],
            'lines.*.unit_id' => ['required', 'exists:units,id'],
            'lines.*.expired_at' => ['nullable', 'date'],
            'lines.*.unit_cost_base' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($request, $opname) {

            $opname->note = $request->note;
            $opname->save();

            foreach ($request->lines as $row) {
                /** @var StockOpnameLine|null $line */
                $line = $opname->lines->firstWhere('id', (int) $row['id']);
                if (!$line) continue;

                $item = $line->item;

                $systemQtyBase = (float) ItemBatch::query()
                    ->where('item_id', $item->id)
                    ->where('status', 'ACTIVE')
                    ->sum('qty_on_hand_base');

                $qtyInput = (float) $row['physical_qty'];
                $unitId   = (int) $row['unit_id'];

                try {
                    $physicalQtyBase = (float) $this->toBaseQty((int)$item->base_unit_id, $unitId, $qtyInput);
                } catch (\Throwable $e) {
                    return back()->withErrors([
                        "Konversi unit untuk item {$item->name} tidak ditemukan. Tambahkan di Unit Conversion."
                    ])->withInput();
                }

                $diff = $physicalQtyBase - $systemQtyBase;

                $line->system_qty_base   = $systemQtyBase;
                $line->physical_qty_base = $physicalQtyBase;
                $line->diff_qty_base     = $diff;

                $line->expired_at     = $row['expired_at'] ?? null;
                $line->unit_cost_base = $row['unit_cost_base'] ?? null;

                $line->save();
            }

            AuditLog::log(auth()->id(), 'STOCK_OPNAME_UPDATED', $opname, [
                'lines_updated' => count($request->lines),
            ]);

            return redirect()->route('admin.stock_opname.show', $opname->id)
                ->with('status', 'Stock opname berhasil diupdate.');
        });
    }

    public function post($id)
    {
        $allocator = app(FefoAllocator::class);

        return DB::transaction(function () use ($id, $allocator) {

            /** @var StockOpname $opname */
            $opname = StockOpname::with(['lines.item'])
                ->lockForUpdate()
                ->findOrFail($id);

            abort_if($opname->status !== 'DRAFT', 403, 'Hanya DRAFT yang bisa di-POST.');

            // Validasi: jika diff plus, expired_at wajib
            $missingExpired = $opname->lines
                ->where('diff_qty_base', '>', 0)
                ->whereNull('expired_at')
                ->count();

            if ($missingExpired > 0) {
                return back()->withErrors([
                    "Ada {$missingExpired} item selisih plus yang belum diisi expired."
                ]);
            }

            foreach ($opname->lines as $line) {
                $item = $line->item;

                $diff = (float) $line->diff_qty_base;
                if (abs($diff) < 0.000001) continue;

                // PLUS: buat batch baru
                if ($diff > 0) {
                    $batch = ItemBatch::create([
                        'item_id'          => $item->id,
                        'received_at'      => $opname->counted_at, // date => 00:00
                        'expired_at'       => $line->expired_at,
                        'qty_on_hand_base' => $diff,
                        'unit_cost_base'   => (float) ($line->unit_cost_base ?? 0),
                        'status'           => 'ACTIVE',
                    ]);

                    StockMove::create([
                        'moved_at'    => $opname->counted_at,
                        'item_id'     => $item->id,
                        'batch_id'    => $batch->id,
                        'qty_base'    => $diff,
                        'type'        => 'ADJUSTMENT',
                        'ref_type'    => 'stock_opname',
                        'ref_id'      => $opname->id,
                        'created_by'  => auth()->id(),
                        'note'        => $opname->code,
                    ]);
                }

                // MINUS: FEFO kurangi dari batch aktif
                if ($diff < 0) {
                    $need = abs($diff);

                    $allocs = $allocator->allocate($item->id, $need);

                    if ($allocs['unfilled'] > 0.000001) {
                        return back()->withErrors([
                            "Stok tidak cukup untuk item {$item->name}. Dibutuhkan {$need}, tersedia " . ($need - $allocs['unfilled'])
                        ]);
                    }

                    foreach ($allocs['lines'] as $a) {
                        $batch = ItemBatch::lockForUpdate()->findOrFail($a['batch_id']);
                        $take  = (float) $a['qty_base'];

                        $batch->qty_on_hand_base = max(0, (float) $batch->qty_on_hand_base - $take);

                        if ($batch->qty_on_hand_base <= 0.000001) {
                            $batch->qty_on_hand_base = 0;
                            $batch->status = 'DEPLETED';
                        }

                        $batch->save();

                        StockMove::create([
                            'moved_at'    => $opname->counted_at,
                            'item_id'     => $item->id,
                            'batch_id'    => $batch->id,
                            'qty_base'    => -$take,
                            'type'        => 'ADJUSTMENT',
                            'ref_type'    => 'stock_opname',
                            'ref_id'      => $opname->id,
                            'created_by'  => auth()->id(),
                            'note'        => $opname->code,
                        ]);
                    }
                }
            }

            $opname->status    = 'POSTED';
            $opname->posted_by = auth()->id();
            $opname->posted_at = now();
            $opname->save();

            AuditLog::log(auth()->id(), 'STOCK_OPNAME_POSTED', $opname, [
                'code' => $opname->code,
            ]);

            return redirect()->route('admin.stock_opname.show', $opname->id)
                ->with('status', 'Stock opname berhasil diposting.');
        });
    }

    public function cancel(Request $request, $id)
    {
        $opname = StockOpname::findOrFail($id);

        abort_if($opname->status !== 'DRAFT', 403, 'Hanya DRAFT yang bisa dicancel.');

        $opname->status        = 'CANCELLED';
        $opname->cancelled_at  = now();
        $opname->cancelled_by  = auth()->id();
        $opname->cancel_reason = $request->input('reason');
        $opname->save();

        AuditLog::log(auth()->id(), 'STOCK_OPNAME_CANCELLED', $opname, [
            'reason' => $opname->cancel_reason,
        ]);

        return redirect()->route('admin.stock_opname.show', $opname->id)
            ->with('status', 'Stock opname dibatalkan.');
    }

    public function pdf($id)
    {
        $opname = StockOpname::with(['lines.item.baseUnit'])->findOrFail($id);

        AuditLog::log(auth()->id(), 'STOCK_OPNAME_PDF_PRINTED', $opname, [
            'code' => $opname->code,
        ]);

        $pdf = Pdf::loadView('admin.stock_opname.pdf', compact('opname'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("StockOpname-{$opname->code}.pdf");
    }
}