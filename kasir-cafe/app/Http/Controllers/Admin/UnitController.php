<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name')->get();

        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:20'],
        ]);

        Unit::create($data);

        return redirect()
            ->route('admin.units.index')
            ->with('status', 'Satuan berhasil dibuat.');
    }

    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:20'],
        ]);

        $unit->update($data);

        return redirect()
            ->route('admin.units.index')
            ->with('status', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        // Kalau nanti mau dicegah delete jika sudah dipakai, bisa ditambah pengecekan di sini.
        $unit->delete();

        return redirect()
            ->route('admin.units.index')
            ->with('status', 'Satuan berhasil dihapus.');
    }
}