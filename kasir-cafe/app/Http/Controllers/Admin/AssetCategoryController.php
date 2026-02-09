<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $categories = AssetCategory::orderBy('name')->paginate(20);
        return view('admin.assets.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.assets.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:asset_categories,name'],
        ]);

        AssetCategory::create($data);

        return redirect()->route('admin.asset_categories.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(AssetCategory $asset_category)
    {
        return view('admin.assets.categories.edit', ['category' => $asset_category]);
    }

    public function update(Request $request, AssetCategory $asset_category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:asset_categories,name,' . $asset_category->id],
        ]);

        $asset_category->update($data);

        return redirect()->route('admin.asset_categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }
}
