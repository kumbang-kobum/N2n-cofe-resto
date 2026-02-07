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
            ->where('is_active', true)
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
                    $line['physical_qty'],
                    $line['unit_id'],
                    $item->base_unit_id
                );

                $systemBase = (float) ItemBatch::where('item_id', $item->id)
                    ->where('status', 'ACTIVE')
                    ->sum('qty_on_hand_base');

                $diff = $physicalBase - $systemBase;

                // simpan semua line (audit), tapi kamu boleh skip jika physical_qty kosong
                $unitCostBase = 0;
                if (!empty($line['unit_cost'])) {
                    $unitCostBase = $converter->costToBase(
                        $line['unit_cost'],
                        $line['unit_id'],
                        $item->base_unit_id
                    );
                }

                StockOpnameLine::create([
                    'stock_opname_id' => $opname->id,
                    'item_id' => $item->id,
                    'system_qty_base' => $systemBase,
                    'physical_qty_base' => $physicalBase,
                    'diff_qty_base' => $diff,
                    'physical_qty' => $line['physical_qty'],
                    'input_unit_id' => $line['unit_id'],
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

    public function post(int $id, UnitConverter $converter, FefoAllocator $allocator)
    {
        DB::transaction(function () use ($id, $converter, $allocator) {
            $opname = StockOpname::lockForUpdate()->with(['lines.item'])->findOrFail($id);

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
                        $take  = $a['take'];

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