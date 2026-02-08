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

    public function store(Request $request)
    {
        $request->validate([
            'counted_at' => ['required', 'date'],
            'note'       => ['nullable', 'string'],

            'lines'                => ['required', 'array', 'min:1'],
            'lines.*.item_id'      => ['required', 'exists:items,id'],
            'lines.*.unit_id'      => ['required', 'exists:units,id'],
            'lines.*.physical_qty' => ['required', 'numeric', 'min:0'],
            'lines.*.expired_at'   => ['nullable', 'date'],
            'lines.*.unit_cost'    => ['nullable', 'numeric', 'min:0'],
        ], [
            'lines.required'                => 'Minimal 1 baris opname.',
            'lines.*.item_id.required'      => 'Item wajib dipilih.',
            'lines.*.physical_qty.required' => 'Qty fisik wajib diisi.',
        ]);

        $opname = DB::transaction(function () use ($request) {

            /** @var \App\Models\StockOpname $opname */
            $opname = StockOpname::create([
                'code'        => StockOpname::nextCode($request->date('counted_at')),
                'counted_at'  => $request->date('counted_at'),
                'status'      => 'DRAFT',
                'note'        => $request->note,
                'created_by'  => auth()->id(),
            ]);

            $linesToInsert = [];

            foreach ($request->lines as $line) {
                // item & unit input
                $item = Item::with('baseUnit')->findOrFail($line['item_id']);
                $unit = Unit::findOrFail($line['unit_id']);

                // konversi qty input ke base
                $factor       = $unit->to_base_factor ?? 1;
                $physicalBase = (float) $line['physical_qty'] * $factor;

                // stok sistem (base) per item
                $systemBase = (float) ItemBatch::query()
                    ->where('item_id', $item->id)
                    ->where('status', 'ACTIVE')
                    ->sum('qty_on_hand_base');

                $diffBase = $physicalBase - $systemBase;

                // cost per base (kalau diisi di satuan input)
                $unitCostBase = null;
                if (isset($line['unit_cost']) && $line['unit_cost'] !== '') {
                    $inputCost   = (float) $line['unit_cost'];  // harga per unit input
                    $unitCostBase = $factor > 0 ? $inputCost / $factor : 0; // harga per base
                }

                $linesToInsert[] = [
                    'stock_opname_id'   => $opname->id,
                    'item_id'           => $item->id,
                    'system_qty_base'   => $systemBase,
                    'physical_qty_base' => $physicalBase,
                    'diff_qty_base'     => $diffBase,
                    'input_unit_id'     => $unit->id,
                    'expired_at'        => $line['expired_at'] ?? null,
                    'unit_cost_base'    => $unitCostBase,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            StockOpnameLine::insert($linesToInsert);

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

            foreach ($request->lines as $row) {
                /** @var \App\Models\StockOpnameLine $line */
                $line = $opname->lines->firstWhere('id', (int) $row['id']);
                if (! $line) {
                    continue;
                }

                // hitung ulang stok sistem (base) dari batch aktif
                $systemQtyBase = (float) ItemBatch::query()
                    ->where('item_id', $line->item_id)
                    ->where('status', 'ACTIVE')
                    ->sum('qty_on_hand_base');

                $physicalQtyBase = (float) $row['physical_qty_base'];
                $diff = $physicalQtyBase - $systemQtyBase;

                $line->system_qty_base   = $systemQtyBase;
                $line->physical_qty_base = $physicalQtyBase;
                $line->diff_qty_base     = $diff;

                // expired & cost hanya relevan jika selisih plus
                if ($diff > 0) {
                    $line->expired_at     = $row['expired_at'] ?? null;
                    $line->unit_cost_base = $row['unit_cost_base'] ?? 0;
                } else {
                    // kalau selisih 0 atau minus, kosongkan saja
                    $line->expired_at     = null;
                    $line->unit_cost_base = null;
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

                StockMove::create([
                    'moved_at'   => $opname->counted_at,
                    'item_id'    => $item->id,
                    'batch_id'   => $batch->id,
                    'qty_base'   => $diff,          // + masuk
                    'type'       => 'ADJUSTMENT',   // enum di migration
                    'ref_type'   => 'stock_opname',
                    'ref_id'     => $opname->id,
                    'created_by' => auth()->id(),
                    'note'       => $opname->code,
                ]);
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
                    StockMove::create([
                        'moved_at'   => $opname->counted_at,
                        'item_id'    => $item->id,
                        'batch_id'   => $batch->id,
                        'qty_base'   => -$take,        // - keluar
                        'type'       => 'ADJUSTMENT',  // enum di migration
                        'ref_type'   => 'stock_opname',
                        'ref_id'     => $opname->id,
                        'created_by' => auth()->id(),
                        'note'       => $opname->code,
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
}