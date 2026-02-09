<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetLocation;
use Illuminate\Http\Request;

class AssetLocationController extends Controller
{
    public function index()
    {
        $locations = AssetLocation::orderBy('name')->paginate(20);
        return view('admin.assets.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.assets.locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:asset_locations,name'],
        ]);

        AssetLocation::create($data);

        return redirect()->route('admin.asset_locations.index')->with('status', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(AssetLocation $asset_location)
    {
        return view('admin.assets.locations.edit', ['location' => $asset_location]);
    }

    public function update(Request $request, AssetLocation $asset_location)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:asset_locations,name,' . $asset_location->id],
        ]);

        $asset_location->update($data);

        return redirect()->route('admin.asset_locations.index')->with('status', 'Lokasi berhasil diperbarui.');
    }
}
