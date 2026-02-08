<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'price_default' => ['required', 'numeric', 'min:0'],
            'is_active'     => ['required', 'boolean'],
            'image'         => ['nullable', 'image', 'max:2048'], // 2MB
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'name'          => $data['name'],
            'price_default' => $data['price_default'],
            'is_active'     => $data['is_active'],
            'image_path'    => $imagePath,
        });

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'price_default' => ['required', 'numeric', 'min:0'],
            'is_active'     => ['required', 'boolean'],
            'image'         => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $product->name          = $data['name'];
        $product->price_default = $data['price_default'];
        $product->is_active     = $data['is_active'];
        $product->save();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil diupdate.');
    }

    public function destroy(Product $product)
    {
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk dihapus.');
    }
}