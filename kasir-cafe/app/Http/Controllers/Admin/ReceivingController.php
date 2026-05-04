<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\Unit;
use App\Models\StockMove;
use App\Models\AuditLog;
use App\Services\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $purchases = Purchase::with('lines.item', 'lines.unit')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('supplier_name', 'like', '%' . $q . '%')
                        ->orWhere('id', $q)
                        ->orWhereHas('lines.item', fn ($lineQuery) => $lineQuery->where('name', 'like', '%' . $q . '%'));
                });
            })
            ->orderByDesc('received_at')
            ->get();

        return view('admin.receivings.index', compact('purchases', 'q'));
    }

    public function create()
    {
        $items = Item::with('baseUnit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('symbol')->get();

        $receivingItems = $items->map(function (Item $item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'base' => optional($item->baseUnit)->symbol ?? '',
                'base_unit_id' => $item->base_unit_id,
            ];
        })->values();

        $receivingUnits = $units->map(function (Unit $unit) {
            return [
                'id' => $unit->id,
                'symbol' => $unit->symbol,
            ];
        })->values();

        return view('admin.receivings.create', compact('items', 'units', 'receivingItems', 'receivingUnits'));
    }

    public function store(Request $request, UnitConverter $converter)
    {
        $this->normalizeNumericInputs($request, ['lines.*.qty', 'lines.*.unit_cost']);

        $request->validate([
            'received_at' => ['required', 'date'],
            'supplier_name' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', 'exists:items,id'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_id' => ['required', 'exists:units,id'],
            'lines.*.unit_cost' => ['required', 'numeric', 'gte:0'],
            'lines.*.cost_mode' => ['nullable', 'in:UNIT,TOTAL'],
            'lines.*.expired_at' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($request, $converter) {

            $purchase = Purchase::create([
                'received_at' => $request->received_at,
                'supplier_name' => $request->supplier_name,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->lines as $line) {
                $item = Item::findOrFail($line['item_id']);
                $qty = (float) $line['qty'];
                $unitId = (int) $line['unit_id'];
                $costMode = (string) ($line['cost_mode'] ?? 'UNIT');
                $inputCost = (float) $line['unit_cost'];

                // Izinkan 2 mode input biaya:
                // UNIT  = harga per unit input
                // TOTAL = total harga untuk qty pada baris ini
                $unitCost = $costMode === 'TOTAL' ? ($inputCost / max($qty, 0.000001)) : $inputCost;

                // konversi ke base unit untuk stok; qty input tetap disimpan apa adanya
                $qtyBase = $converter->toBase(
                    $qty,
                    $unitId,
                    (int) $item->base_unit_id
                );

                $costBase = $converter->costToBase(
                    $unitCost,
                    $unitId,
                    (int) $item->base_unit_id
                );

                // simpan audit purchase_line
                $purchaseLineData = [
                    'purchase_id' => $purchase->id,
                    'item_id' => $item->id,
                    'qty' => $qty,
                    'unit_id' => $unitId,
                    'unit_cost' => $unitCost,
                    'expired_at' => $line['expired_at'],
                ];
                if (DB::getSchemaBuilder()->hasColumn('purchase_lines', 'qty_base')) {
                    $purchaseLineData['qty_base'] = $qtyBase;
                }
                if (DB::getSchemaBuilder()->hasColumn('purchase_lines', 'unit_cost_base')) {
                    $purchaseLineData['unit_cost_base'] = $costBase;
                }
                $pl = PurchaseLine::create($purchaseLineData);

                // cek perubahan harga terakhir (unit_cost_base)
                $lastCost = ItemBatch::where('item_id', $item->id)
                    ->whereNotNull('unit_cost_base')
                    ->orderByDesc('id')
                    ->value('unit_cost_base');

                // batch expired
                $batch = ItemBatch::create([
                    'item_id' => $item->id,
                    'received_at' => $purchase->received_at,
                    'expired_at' => $line['expired_at'],
                    'qty_on_hand_base' => $qtyBase,
                    'unit_cost_base' => $costBase,
                    'status' => 'ACTIVE',
                ]);

                // ledger receipt
                StockMove::create([
                    'moved_at' => $purchase->received_at,
                    'item_id' => $item->id,
                    'batch_id' => $batch->id,
                    'qty_base' => $qtyBase,
                    'type' => 'RECEIPT',
                    'ref_type' => 'purchase_line',
                    'ref_id' => $pl->id,
                    'created_by' => auth()->id(),
                ]);

                AuditLog::log(auth()->id(), 'STOCK_RECEIPT', $batch, [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'qty_base' => (float) $qtyBase,
                    'unit_cost_base' => (float) $costBase,
                    'purchase_id' => $purchase->id,
                ]);

                if ($lastCost !== null && abs((float) $lastCost - (float) $costBase) > 0.000001) {
                    AuditLog::log(auth()->id(), 'ITEM_COST_CHANGED', $batch, [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'old_cost_base' => (float) $lastCost,
                        'new_cost_base' => (float) $costBase,
                        'purchase_id' => $purchase->id,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.receivings.index')
            ->with('status', 'Penerimaan stok berhasil');
    }
}
