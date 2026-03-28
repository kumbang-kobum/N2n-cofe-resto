<?php

namespace App\Http\Controllers\Admin;
//aktual base dari mbak Arum 2A

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Models\StockOpnameLine;
use App\Models\Unit;
use App\Services\FefoAllocator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::withCount('lines')
            ->orderByDesc('id')
            ->paginate(20);

        $opnames->getCollection()->load('lines');
        $opnames->getCollection()->transform(function ($opname) {
            $estimatedValueTotal = (float) $opname->lines->sum(function ($line) {
                return ((float) $line->physical_qty_base) * ((float) $line->unit_cost_base);
            });
            $totalPhysicalQtyBase = (float) $opname->lines->sum('physical_qty_base');

            $opname->estimated_value_total = $estimatedValueTotal;
            $opname->avg_unit_cost_base = $totalPhysicalQtyBase > 0 ? ($estimatedValueTotal / $totalPhysicalQtyBase) : 0;

            return $opname;
        });

        return view('admin.stock_opname.index', compact('opnames'));
    }

    public function create()
    {
        $items = Item::with('baseUnit')->orderBy('name')->get();
        $units = Unit::orderBy('symbol')->get();
        $systemQtyMap = $this->activeStockMap($items->pluck('id')->all());

        return view('admin.stock_opname.create', compact('items', 'units', 'systemQtyMap'));
    }

    public function store(Request $request)
    {
        @set_time_limit(300);

        $request->validate([
            'counted_at' => ['required', 'date'],
            'note'       => ['nullable', 'string'],
            'lines'      => ['required', 'array'],
        ]);

        $rawLines = $request->input('lines', []);
        $selectedLines = array_values(array_filter($rawLines, function ($line) {
            return ! empty($line['include']);
        }));

        if (count($selectedLines) === 0) {
            return back()->withErrors(['Pilih minimal 1 item untuk opname.'])->withInput();
        }

        foreach ($selectedLines as $line) {
            $validator = validator($line, [
                'item_id'      => ['required', 'exists:items,id'],
                'unit_id'      => ['required', 'exists:units,id'],
                'physical_qty' => ['required', 'numeric', 'min:0'],
                'expired_at'   => ['nullable', 'date'],
                'unit_cost'    => ['nullable', 'numeric', 'min:0'],
            ], [
                'item_id.required'      => 'Item wajib dipilih.',
                'physical_qty.required' => 'Qty fisik wajib diisi.',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        }

        $itemIds = collect($selectedLines)->pluck('item_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        $unitIds = collect($selectedLines)->pluck('unit_id')->map(fn ($id) => (int) $id)->unique()->values()->all();

        $items = Item::with('baseUnit')->whereIn('id', $itemIds)->get()->keyBy('id');
        $units = Unit::whereIn('id', $unitIds)->get()->keyBy('id');
        $systemQtyMap = $this->activeStockMap($itemIds);

        $opname = DB::transaction(function () use ($request, $selectedLines, $items, $units, $systemQtyMap) {

            /** @var \App\Models\StockOpname $opname */
            $opname = StockOpname::create([
                'code'        => StockOpname::nextCode($request->date('counted_at')),
                'counted_at'  => $request->date('counted_at'),
                'status'      => 'DRAFT',
                'note'        => $request->note,
                'created_by'  => auth()->id(),
            ]);

            $linesToInsert = $this->buildOpnameLines(
                $selectedLines,
                $items->all(),
                $units->all(),
                $systemQtyMap,
                $opname->id,
            );

            foreach (array_chunk($linesToInsert, 250) as $chunk) {
                StockOpnameLine::insert($chunk);
            }

            AuditLog::log(auth()->id(), 'STOCK_OPNAME_CREATED', $opname, [
                'code'       => $opname->code,
                'counted_at' => (string) $opname->counted_at,
                'lines'      => count($linesToInsert),
            ]);

            return $opname;
        });

        return redirect()
            ->route('admin.stock_opname.show', $opname->id)
            ->with('status', 'Stock opname dibuat.');
    }

    public function show($id)
    {
        $opname = StockOpname::with([
            'lines.item.baseUnit',
            'audits' => fn ($q) => $q->orderBy('created_at')->orderBy('id'),
        ])->findOrFail($id);

        return view('admin.stock_opname.show', compact('opname'));
    }

    public function edit($id)
    {
        $opname = StockOpname::with(['lines.item.baseUnit'])
            ->findOrFail($id);

        abort_if($opname->status !== 'DRAFT', 403, 'Hanya DRAFT yang bisa diedit.');

        return view('admin.stock_opname.edit', compact('opname'));
    }

    public function update(Request $request, $id)
    {
        @set_time_limit(300);

        /** @var StockOpname $opname */
        $opname = StockOpname::with(['lines.item'])->findOrFail($id);

        abort_if($opname->status !== 'DRAFT', 403, 'Hanya DRAFT yang bisa diedit.');

        // VALIDASI – sudah TIDAK ada input_unit_id & physical_qty_input
        $request->validate([
            'note' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['required', 'exists:stock_opname_lines,id'],
            'lines.*.physical_qty_base' => ['required', 'numeric', 'min:0'],
            'lines.*.expired_at' => ['nullable', 'date'],
            'lines.*.unit_cost_base' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($request, $opname) {

            // update note
            $opname->note = $request->note;
            $opname->save();

            $linesById = $opname->lines->keyBy('id');
            $systemQtyMap = $this->activeStockMap($opname->lines->pluck('item_id')->unique()->all());

            foreach ($request->lines as $row) {
                /** @var \App\Models\StockOpnameLine $line */
                $line = $linesById->get((int) $row['id']);
                if (! $line) {
                    continue;
                }

                $systemQtyBase = (float) ($systemQtyMap[$line->item_id] ?? 0);

                $physicalQtyBase = (float) $row['physical_qty_base'];
                $diff = $physicalQtyBase - $systemQtyBase;

                $line->system_qty_base   = $systemQtyBase;
                $line->physical_qty_base = $physicalQtyBase;
                $line->diff_qty_base     = $diff;

                // expired & cost hanya relevan jika selisih plus
                if ($diff > 0) {
                    $line->expired_at     = $row['expired_at'] ?? null;
                    $line->unit_cost_base = isset($row['unit_cost_base']) && $row['unit_cost_base'] !== ''
                        ? (float) $row['unit_cost_base']
                        : 0;
                } else {
                    // kalau selisih 0 atau minus, expired boleh dikosongkan,
                    // tapi unit_cost_base jangan NULL karena kolom NOT NULL
                    $line->expired_at     = null;
                    $line->unit_cost_base = 0;
                }

                // catatan: kolom input_unit_id & physical_qty_input di tabel
                // dibiarkan apa adanya (nilai awal dari saat create)
                $line->save();
            }

            AuditLog::log(auth()->id(), 'STOCK_OPNAME_UPDATED', $opname, [
                'lines_updated' => count($request->lines),
            ]);

            return redirect()
                ->route('admin.stock_opname.show', $opname->id)
                ->with('status', 'Stock opname berhasil diupdate.');
        });
    }

    public function post($id)
{
    @set_time_limit(300);

    $allocator = app(FefoAllocator::class);

    return DB::transaction(function () use ($id, $allocator) {
        $actorId = auth()->id();
        $stockMovesToInsert = [];
        $auditLogsToInsert = [];
        $now = now();

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

            if (abs($diff) < 0.000001) {
                continue;
            }

            // ===== SELISIH PLUS -> BUAT BATCH BARU =====
            if ($diff > 0) {
                $batch = ItemBatch::create([
                    'item_id'          => $item->id,
                    'received_at'      => $opname->counted_at,
                    'expired_at'       => $line->expired_at,
                    'qty_on_hand_base' => $diff,
                    'unit_cost_base'   => (float) ($line->unit_cost_base ?? 0),
                    'status'           => 'ACTIVE',
                ]);

                $stockMovesToInsert[] = [
                    'moved_at'   => $opname->counted_at,
                    'item_id'    => $item->id,
                    'batch_id'   => $batch->id,
                    'qty_base'   => $diff,          // + masuk
                    'type'       => 'ADJUSTMENT',   // enum di migration
                    'ref_type'   => 'stock_opname',
                    'ref_id'     => $opname->id,
                    'created_by' => $actorId,
                    'note'       => $opname->code,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $auditLogsToInsert[] = $this->makeAuditLogRow($actorId, 'STOCK_ADJUSTMENT', $batch, [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'qty_base' => (float) $diff,
                    'unit_cost_base' => (float) ($line->unit_cost_base ?? 0),
                    'reason' => 'stock_opname_plus',
                    'opname_id' => $opname->id,
                    'opname_code' => $opname->code,
                ], $now);
            }

            // ===== SELISIH MINUS -> FEFO KELUAR BATCH =====
            if ($diff < 0) {
                $need = abs($diff);

                try {
                    // FefoAllocator sekarang mengembalikan array of ['batch' => ItemBatch, 'take' => float]
                    $allocs = $allocator->allocate($item->id, $need);
                } catch (\RuntimeException $e) {
                    // kalau stok tidak cukup, tampilkan pesan dari allocator
                    return back()->withErrors([$e->getMessage()]);
                }

                foreach ($allocs as $alloc) {
                    /** @var \App\Models\ItemBatch $batch */
                    $batch = $alloc['batch'];
                    $take  = (float) $alloc['take'];

                    // kurangi qty batch
                    $batch->qty_on_hand_base = max(0, (float) $batch->qty_on_hand_base - $take);

                    if ($batch->qty_on_hand_base <= 0.000001) {
                        $batch->qty_on_hand_base = 0;
                        $batch->status = 'DEPLETED';
                    }

                    $batch->save();

                    // catat pergerakan stok keluar
                    $stockMovesToInsert[] = [
                        'moved_at'   => $opname->counted_at,
                        'item_id'    => $item->id,
                        'batch_id'   => $batch->id,
                        'qty_base'   => -$take,        // - keluar
                        'type'       => 'ADJUSTMENT',  // enum di migration
                        'ref_type'   => 'stock_opname',
                        'ref_id'     => $opname->id,
                        'created_by' => $actorId,
                        'note'       => $opname->code,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $auditLogsToInsert[] = $this->makeAuditLogRow($actorId, 'STOCK_ADJUSTMENT', $batch, [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'qty_base' => -$take,
                        'reason' => 'stock_opname_minus',
                        'opname_id' => $opname->id,
                        'opname_code' => $opname->code,
                    ], $now);
                }
            }
        }

        foreach (array_chunk($stockMovesToInsert, 250) as $chunk) {
            StockMove::insert($chunk);
        }

        $opname->status    = 'POSTED';
        $opname->posted_by = $actorId;
        $opname->posted_at = now();
        $opname->save();

        $auditLogsToInsert[] = $this->makeAuditLogRow($actorId, 'STOCK_OPNAME_POSTED', $opname, [
            'code' => $opname->code,
        ], $now);

        foreach (array_chunk($auditLogsToInsert, 250) as $chunk) {
            AuditLog::insert($chunk);
        }

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

        return redirect()
            ->route('admin.stock_opname.show', $opname->id)
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

    /**
     * @param  array<int>  $itemIds
     * @return array<int, float>
     */
    protected function activeStockMap(array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }

        return ItemBatch::query()
            ->select('item_id', DB::raw('SUM(qty_on_hand_base) as total_qty_base'))
            ->whereIn('item_id', $itemIds)
            ->where('status', 'ACTIVE')
            ->groupBy('item_id')
            ->pluck('total_qty_base', 'item_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $selectedLines
     * @param  array<int, \App\Models\Item>  $items
     * @param  array<int, \App\Models\Unit>  $units
     * @param  array<int, float>  $systemQtyMap
     * @return array<int, array<string, mixed>>
     */
    protected function buildOpnameLines(array $selectedLines, array $items, array $units, array $systemQtyMap, int $opnameId): array
    {
        $linesToInsert = [];
        $now = now();

        foreach ($selectedLines as $line) {
            $itemId = (int) $line['item_id'];
            $unitId = (int) $line['unit_id'];
            $item = $items[$itemId] ?? null;
            $unit = $units[$unitId] ?? null;

            if (! $item || ! $unit) {
                throw ValidationException::withMessages([
                    'lines' => ['Ada item atau satuan opname yang tidak valid. Silakan refresh halaman dan coba lagi.'],
                ]);
            }

            $factor = (float) ($unit->to_base_factor ?? 1);
            $physicalBase = (float) $line['physical_qty'] * $factor;
            $systemBase = (float) ($systemQtyMap[$itemId] ?? 0);
            $diffBase = $physicalBase - $systemBase;

            $unitCostBase = 0.0;
            if (isset($line['unit_cost']) && $line['unit_cost'] !== '') {
                $inputCost = (float) $line['unit_cost'];
                $unitCostBase = $factor > 0 ? $inputCost / $factor : 0;
            }

            $linesToInsert[] = [
                'stock_opname_id'   => $opnameId,
                'item_id'           => $itemId,
                'system_qty_base'   => $systemBase,
                'physical_qty_base' => $physicalBase,
                'diff_qty_base'     => $diffBase,
                'input_unit_id'     => $unitId,
                'expired_at'        => $line['expired_at'] ?? null,
                'unit_cost_base'    => $unitCostBase,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        return $linesToInsert;
    }

    protected function makeAuditLogRow(?int $actorId, string $action, $auditable, array $meta, $timestamp): array
    {
        return [
            'actor_id' => $actorId,
            'action' => $action,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->getKey(),
            'meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}
