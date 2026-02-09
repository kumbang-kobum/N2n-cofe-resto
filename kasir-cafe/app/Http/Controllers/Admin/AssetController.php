<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::orderBy('name')->paginate(20);
        return view('admin.assets.index', compact('assets'));
    }

    public function create()
    {
        return view('admin.assets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['required', 'in:GOOD,MINOR,DAMAGED,DISPOSED'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['purchase_cost'] = $data['purchase_cost'] ?? 0;

        Asset::create($data);

        return redirect()->route('admin.assets.index')->with('status', 'Inventaris berhasil ditambahkan.');
    }

    public function edit(Asset $asset)
    {
        return view('admin.assets.edit', compact('asset'));
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['required', 'in:GOOD,MINOR,DAMAGED,DISPOSED'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['purchase_cost'] = $data['purchase_cost'] ?? 0;

        $asset->update($data);

        return redirect()->route('admin.assets.index')->with('status', 'Inventaris berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('admin.assets.index')->with('status', 'Inventaris berhasil dihapus.');
    }
}
