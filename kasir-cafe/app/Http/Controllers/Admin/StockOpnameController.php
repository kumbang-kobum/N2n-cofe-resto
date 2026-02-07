<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Models\StockOpnameLine;
use App\Models\Unit;
use App\Services\FefoAllocator;
use App\Services\UnitConverter;
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
        $items = Item::with('baseUnit')
            ->where('is_active', 1) // ✅ lebih aman daripada true
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('symbol')->get();

        $systemStock = ItemBatch::query()
            ->select('item_id', DB::raw("SUM(qty_on_hand_base) as qty_base"))
            ->where('status', 'ACTIVE')
            ->groupBy('item_id')
            ->pluck('qty_base', 'item_id');

        return view('admin.stock_opname.create', compact('items', 'units', 'systemStock'));
    }

    public function store(Request $request, UnitConverter $converter)
    {
        $request->validate([
            'counted_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],

            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', 'exists:items,id'],
            'lines.*.physical_qty' => ['required', 'numeric', 'gte:0'],
            'lines.*.unit_id' => ['required', 'exists:units,id'],
            'lines.*.expired_at' => ['nullable', 'date'],
            'lines.*.unit_cost' => ['nullable', 'numeric', 'gte:0'],
        ]);

        $opname = DB::transaction(function () use ($request, $converter) {

            $code = $this->nextCode($request->counted_at);

            $opname = StockOpname::create([
                'code' => $code,
                'counted_at' => $request->counted_at,
                'status' => 'DRAFT',
                'note' => $request->note,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                $item = Item::findOrFail($line['item_id']);

                $physicalBase = $converter->toBase(
                    (float)$line['physical_qty'],
                    (int)$line['unit_id'],
                    (int)$item->base_unit_id
                );

                $systemBase = (float) ItemBatch::where('item_id', $item->id)
                    ->where('status', 'ACTIVE')
                    ->sum('qty_on_hand_base');

                $diff = $physicalBase - $systemBase;

                $unitCostBase = 0.0;
                if (!empty($line['unit_cost'])) {
                    $unitCostBase = $converter->costToBase(
                        (float)$line['unit_cost'],
                        (int)$line['unit_id'],
                        (int)$item->base_unit_id
                    );
                }

                StockOpnameLine::create([
                    'stock_opname_id' => $opname->id,
                    'item_id' => $item->id,

                    'system_qty_base' => $systemBase,
                    'physical_qty_base' => $physicalBase,
                    'diff_qty_base' => $diff,

                    'physical_qty' => (float)$line['physical_qty'],
                    'input_unit_id' => (int)$line['unit_id'],

                    'expired_at' => $line['expired_at'] ?? null,
                    'unit_cost_base' => $unitCostBase,
                ]);
            }

            return $opname;
        });

        return redirect()->route('admin.stock_opname.show', $opname->id)
            ->with('status', 'Dokumen stock opname dibuat. Silakan POST untuk menerapkan ke stok.');
    }

    public function show(int $id)
    {
        $opname = StockOpname::with(['lines.item.baseUnit', 'creator', 'poster'])
            ->findOrFail($id);

        return view('admin.stock_opname.show', compact('opname'));
    }

    // ✅ NEW: edit untuk isi expired/cost sebelum post
    public function edit(int $id)
    {
        $opname = StockOpname::with(['lines.item.baseUnit'])->findOrFail($id);
        abort_if($opname->status !== 'DRAFT', 400, 'Opname sudah diposting / dibatalkan.');

        return view('admin.stock_opname.edit', compact('opname'));
    }

    // ✅ NEW: update untuk expired/cost sebelum post
    public function update(Request $request, int $id)
    {
        $opname = StockOpname::with(['lines.item'])->findOrFail($id);
        abort_if($opname->status !== 'DRAFT', 400, 'Opname sudah diposting / dibatalkan.');

        $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.id' => ['required', 'exists:stock_opname_lines,id'],
            'lines.*.expired_at' => ['nullable', 'date'],
            'lines.*.unit_cost_base' => ['nullable', 'numeric', 'gte:0'],
        ]);

        foreach ($request->lines as $l) {
            $line = $opname->lines->firstWhere('id', (int)$l['id']);
            if (!$line) continue;

            // expired WAJIB kalau diff plus
            if ((float)$line->diff_qty_base > 0 && empty($l['expired_at'])) {
                return back()
                    ->withErrors(["expired_at" => "Expired wajib untuk item {$line->item->name} karena selisih plus."])
                    ->withInput();
            }

            $line->expired_at = $l['expired_at'] ?? null;

            if (isset($l['unit_cost_base']) && $l['unit_cost_base'] !== null && $l['unit_cost_base'] !== '') {
                $line->unit_cost_base = (float)$l['unit_cost_base'];
            }

            $line->save();
        }

        return redirect()->route('admin.stock_opname.show', $opname->id)
            ->with('status', 'Opname diupdate. Sekarang bisa POST.');
    }

    public function post(int $id, UnitConverter $converter, FefoAllocator $allocator)
    {
        DB::transaction(function () use ($id, $allocator) {
            $opname = StockOpname::lockForUpdate()
                ->with(['lines.item'])
                ->findOrFail($id);

            if ($opname->status !== 'DRAFT') {
                abort(400, 'Opname sudah diposting / dibatalkan.');
            }

            foreach ($opname->lines as $line) {
                $item = $line->item;
                $diff = (float) $line->diff_qty_base;

                if (abs($diff) < 0.000001) continue;

                // + tambah stok → butuh expired
                if ($diff > 0) {
                    if (!$line->expired_at) {
                        abort(422, "Expired wajib untuk item {$item->name} karena selisih plus.");
                    }

                    $batch = ItemBatch::create([
                        'item_id' => $item->id,
                        'received_at' => $opname->counted_at,
                        'expired_at' => $line->expired_at,
                        'qty_on_hand_base' => $diff,
                        'unit_cost_base' => (float) $line->unit_cost_base,
                        'status' => 'ACTIVE',
                    ]);

                    StockMove::create([
                        'moved_at' => $opname->counted_at,
                        'item_id' => $item->id,
                        'batch_id' => $batch->id,
                        'qty_base' => $diff,
                        'type' => 'STOCK_OPNAME',
                        'ref_type' => 'stock_opname',
                        'ref_id' => $opname->id,
                        'created_by' => auth()->id(),
                        'note' => $opname->code,
                    ]);
                }

                // - kurangi stok → FEFO
                if ($diff < 0) {
                    $need = abs($diff);
                    $allocs = $allocator->allocate($item->id, $need);

                    foreach ($allocs as $a) {
                        $batch = $a['batch'];
                        $take  = (float)$a['take'];

                        $batch->qty_on_hand_base -= $take;
                        if ($batch->qty_on_hand_base <= 0) {
                            $batch->qty_on_hand_base = 0;
                            $batch->status = 'DEPLETED';
                        }
                        $batch->save();

                        StockMove::create([
                            'moved_at' => $opname->counted_at,
                            'item_id' => $item->id,
                            'batch_id' => $batch->id,
                            'qty_base' => -$take,
                            'type' => 'STOCK_OPNAME',
                            'ref_type' => 'stock_opname',
                            'ref_id' => $opname->id,
                            'created_by' => auth()->id(),
                            'note' => $opname->code,
                        ]);
                    }
                }
            }

            $opname->status = 'POSTED';
            $opname->posted_by = auth()->id();
            $opname->posted_at = now();
            $opname->save();
        });

        return redirect()->route('admin.stock_opname.show', $id)
            ->with('status', 'Stock opname berhasil diposting dan stok sudah disesuaikan.');
    }

    private function nextCode(string $countedAt): string
    {
        // SOP-YYYYMMDD-0001
        $date = \Carbon\Carbon::parse($countedAt)->format('Ymd');
        $prefix = "SOP-{$date}-";

        $last = StockOpname::where('code', 'like', "{$prefix}%")
            ->orderByDesc('code')
            ->value('code');

        $seq = 1;
        if ($last) {
            $seq = (int) substr($last, -4) + 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}