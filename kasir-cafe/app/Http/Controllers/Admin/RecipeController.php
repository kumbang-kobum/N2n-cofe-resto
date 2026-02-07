<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeLine;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->orderBy('name')
            ->get();

        return view('admin.recipes.index', compact('products'));
    }

    public function edit(int $productId)
    {
        $product = Product::with(['recipe.lines.item', 'recipe.lines'])->findOrFail($productId);

        $items = Item::with('baseUnit')->where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('symbol')->get();

        $recipe = $product->recipe ?: Recipe::make(['product_id' => $product->id]);
        $lines = $product->recipe?->lines ?? collect();

        return view('admin.recipes.edit', compact('product', 'recipe', 'lines', 'items', 'units'));
    }

    public function update(Request $request, int $productId)
    {
        $request->validate([
            'lines' => ['array'],
            'lines.*.item_id' => ['required', 'exists:items,id'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_id' => ['required', 'exists:units,id'],
        ]);

        DB::transaction(function () use ($request, $productId) {
            $product = Product::findOrFail($productId);

            $recipe = Recipe::firstOrCreate(['product_id' => $product->id]);

            // replace all lines (MVP simple)
            RecipeLine::where('recipe_id', $recipe->id)->delete();

            $lines = $request->input('lines', []);
            foreach ($lines as $line) {
                RecipeLine::create([
                    'recipe_id' => $recipe->id,
                    'item_id' => (int)$line['item_id'],
                    'qty' => (float)$line['qty'],
                    'unit_id' => (int)$line['unit_id'],
                ]);
            }
        });

        return redirect()->route('admin.recipes.edit', $productId)
            ->with('status', 'Resep berhasil disimpan.');
    }
}