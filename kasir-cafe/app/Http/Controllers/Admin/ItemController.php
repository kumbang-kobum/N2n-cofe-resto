<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $items = Item::with('baseUnit')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%');
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $batchStats = DB::table('item_batches')
            ->selectRaw('item_id, SUM(qty_on_hand_base) as stock_base, SUM(qty_on_hand_base * unit_cost_base) as stock_value')
            ->where('status', 'ACTIVE')
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');

        $items->getCollection()->transform(function ($item) use ($batchStats) {
            $stats = $batchStats->get($item->id);
            $stockBase = (float) ($stats->stock_base ?? 0);
            $stockValue = (float) ($stats->stock_value ?? 0);

            $item->stock_base = $stockBase;
            $item->stock_value = $stockValue;
            $item->avg_unit_cost_base = $stockBase > 0 ? ($stockValue / $stockBase) : 0;

            return $item;
        });

        return view('admin.items.index', compact('items', 'q'));
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
