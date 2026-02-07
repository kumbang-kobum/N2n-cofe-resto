<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StockMove;
use App\Services\FefoAllocator;
use App\Services\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $sale = null;
        if ($request->sale_id) {
            $sale = Sale::with('lines.product')->find($request->sale_id);
        }

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cashier.pos', compact('sale', 'products'));
    }

    public function newSale()
    {
        $sale = Sale::create([
            'status' => 'DRAFT',
            'cashier_id' => auth()->id(),
            'total' => 0,
        ]);

        return redirect()->route('cashier.pos', ['sale_id' => $sale->id]);
    }

    public function addLine(Request $request)
    {
        $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'numeric', 'gt:0'],
        ]);

        $sale = Sale::findOrFail($request->sale_id);
        abort_if($sale->status !== 'DRAFT', 400);

        $product = Product::findOrFail($request->product_id);

        SaleLine::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => $request->qty,
            'price' => $product->price_default,
        ]);

        $sale->update([
            'total' => SaleLine::where('sale_id', $sale->id)
                ->sum(DB::raw('qty * price'))
        ]);

        return redirect()->route('cashier.pos', ['sale_id' => $sale->id]);
    }

    public function pay(
        Request $request,
        FefoAllocator $allocator,
        UnitConverter $converter
    ) {
        $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'payment_method' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request, $allocator, $converter) {

            $sale = Sale::with('lines.product.recipe.lines.item')
                ->lockForUpdate()
                ->findOrFail($request->sale_id);

            abort_if($sale->status !== 'DRAFT', 400);

            $needs = [];

            // hitung kebutuhan bahan
            foreach ($sale->lines as $line) {
                foreach ($line->product->recipe->lines as $rl) {
                    $item = $rl->item;

                    $qtyNeeded = $rl->qty * $line->qty;

                    $qtyBase = $converter->toBase(
                        $qtyNeeded,
                        $rl->unit_id,
                        $item->base_unit_id
                    );

                    $needs[$item->id] = ($needs[$item->id] ?? 0) + $qtyBase;
                }
            }

            $cogs = 0;

            foreach ($needs as $itemId => $qtyBase) {
                $allocs = $allocator->allocate($itemId, $qtyBase);

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
                        'moved_at' => now(),
                        'item_id' => $itemId,
                        'batch_id' => $batch->id,
                        'qty_base' => -$take,
                        'type' => 'CONSUMPTION',
                        'ref_type' => 'sale',
                        'ref_id' => $sale->id,
                        'created_by' => auth()->id(),
                    ]);

                    $cogs += $take * $batch->unit_cost_base;
                }
            }

            $sale->update([
                'status' => 'PAID',
                'paid_at' => now(),
                'payment_method' => $request->payment_method,
                'cogs_total' => round($cogs, 2),
                'profit_gross' => round($sale->total - $cogs, 2),
            ]);
        });

        return redirect()
            ->route('cashier.pos')
            ->with('status', 'Pembayaran berhasil');
    }
}