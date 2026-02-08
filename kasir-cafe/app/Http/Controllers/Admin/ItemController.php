<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('baseUnit')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.items.index', compact('items'));
    }

    public function create()
    {
        $units = Unit::orderBy('name')->get();

        return view('admin.items.create', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'base_unit_id'  => ['required', 'exists:units,id'],
            'min_stock'     => ['nullable', 'numeric', 'min:0'],
            'track_expiry'  => ['nullable', 'boolean'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $data['track_expiry'] = $request->boolean('track_expiry');
        $data['is_active']    = $request->boolean('is_active');

        Item::create($data);

        return redirect()
            ->route('admin.items.index')
            ->with('status', 'Bahan berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $units = Unit::orderBy('name')->get();

        return view('admin.items.edit', compact('item', 'units'));
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'base_unit_id'  => ['required', 'exists:units,id'],
            'min_stock'     => ['nullable', 'numeric', 'min:0'],
            'track_expiry'  => ['nullable', 'boolean'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $data['track_expiry'] = $request->boolean('track_expiry');
        $data['is_active']    = $request->boolean('is_active');

        $item->update($data);

        return redirect()
            ->route('admin.items.index')
            ->with('status', 'Bahan berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        // Opsional: bisa dikasih pengecekan kalau sudah dipakai di batch / resep.
        $item->delete();

        return redirect()
            ->route('admin.items.index')
            ->with('status', 'Bahan berhasil dihapus.');
    }
}