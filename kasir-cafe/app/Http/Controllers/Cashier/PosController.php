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
        $saleId = $request->query('sale_id');
        $sale = $saleId ? Sale::with('lines.product')->find($saleId) : null;

        $products = Product::where('is_active', true)->orderBy('name')->get();

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
        abort_if($sale->status !== 'DRAFT', 400, 'Transaksi tidak bisa diubah.');

        $product = Product::findOrFail($request->product_id);

        SaleLine::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => (float)$request->qty,
            'price' => (float)$product->price_default,
        ]);

        // update total
        $total = (float) SaleLine::where('sale_id', $sale->id)->sum(DB::raw('qty * price'));
        $sale->update(['total' => $total]);

        return redirect()->route('cashier.pos', ['sale_id' => $sale->id]);
    }

    public function pay(Request $request, FefoAllocator $allocator, UnitConverter $converter)
    {
        $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($request, $allocator, $converter) {

            $sale = Sale::with('lines.product.recipe.lines.item')->lockForUpdate()->findOrFail($request->sale_id);
            abort_if($sale->status !== 'DRAFT', 400, 'Transaksi sudah diproses.');

            // hitung total dari lines (jaga-jaga)
            $total = (float) $sale->lines->sum(fn($l) => (float)$l->qty * (float)$l->price);

            // 1) kumpulkan kebutuhan bahan base unit: item_id => qty_base_needed
            $needs = []; // item_id => qty_base
            foreach ($sale->lines as $line) {
                $product = $line->product;
                $recipeLines = $product->recipe?->lines ?? collect();

                foreach ($recipeLines as $rl) {
                    $item = $rl->item;
                    $qtyPerProduct = (float)$rl->qty;
                    $unitId = (int)$rl->unit_id;

                    $qtyNeeded = $qtyPerProduct * (float)$line->qty;
                    $qtyBase = $converter->toBase($qtyNeeded, $unitId, $item->base_unit_id);

                    $needs[$item->id] = ($needs[$item->id] ?? 0) + $qtyBase;
                }
            }

            // 2) allocate FEFO + kurangi batch + stock_moves + hitung COGS
            $cogs = 0.0;

            foreach ($needs as $itemId => $qtyBaseNeed) {
                $allocs = $allocator->allocate($itemId, $qtyBaseNeed, now());

                foreach ($allocs as $a) {
                    $batch = $a['batch'];
                    $take = (float)$a['take'];

                    // reduce batch
                    $batch->qty_on_hand_base = (float)$batch->qty_on_hand_base - $take;
                    if ($batch->qty_on_hand_base <= 0.000001) {
                        $batch->qty_on_hand_base = 0;
                        $batch->status = 'DEPLETED';
                    }
                    $batch->save();

                    // ledger
                    StockMove::create([
                        'moved_at' => now(),
                        'item_id' => $itemId,
                        'batch_id' => $batch->id,
                        'qty_base' => -$take,
                        'type' => 'CONSUMPTION',
                        'ref_type' => 'sale',
                        'ref_id' => $sale->id,
                        'created_by' => auth()->id(),
                        'note' => 'POS paid',
                    ]);

                    $cogs += $take * (float)$batch->unit_cost_base;
                }
            }

            $profit = $total - $cogs;

            $sale->update([
                'status' => 'PAID',
                'paid_at' => now(),
                'total' => $total,
                'cogs_total' => round($cogs, 2),
                'profit_gross' => round($profit, 2),
                'payment_method' => $request->payment_method,
            ]);
        });

        return redirect()->route('cashier.pos')->with('status', 'Pembayaran berhasil.');
    }
}