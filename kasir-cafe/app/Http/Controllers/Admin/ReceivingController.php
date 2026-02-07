<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\Purchase;
use App\Models\PurchaseLine;
use App\Models\Unit;
use App\Models\StockMove;
use App\Services\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('lines.item')
            ->orderByDesc('received_at')
            ->get();

        return view('admin.receivings.index', compact('purchases'));
    }

    public function create()
    {
        $items = Item::with('baseUnit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('symbol')->get();

        return view('admin.receivings.create', compact('items', 'units'));
    }

    public function store(Request $request, UnitConverter $converter)
    {
        $request->validate([
            'received_at' => ['required', 'date'],
            'supplier_name' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['required', 'exists:items,id'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_id' => ['required', 'exists:units,id'],
            'lines.*.unit_cost' => ['required', 'numeric', 'gte:0'],
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

                // simpan audit purchase_line
                $pl = PurchaseLine::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $item->id,
                    'qty' => $line['qty'],
                    'unit_id' => $line['unit_id'],
                    'unit_cost' => $line['unit_cost'],
                    'expired_at' => $line['expired_at'],
                ]);

                // konversi ke base unit
                $qtyBase = $converter->toBase(
                    $line['qty'],
                    $line['unit_id'],
                    $item->base_unit_id
                );

                $costBase = $converter->costToBase(
                    $line['unit_cost'],
                    $line['unit_id'],
                    $item->base_unit_id
                );

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
            }
        });

        return redirect()
            ->route('admin.receivings.index')
            ->with('status', 'Penerimaan stok berhasil');
    }
}