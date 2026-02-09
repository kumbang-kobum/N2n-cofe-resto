<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetIncident;
use Illuminate\Http\Request;

class AssetIncidentController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', '');
        $status = $request->query('status', '');

        $query = AssetIncident::with('asset')
            ->orderByDesc('incident_date');

        if ($type !== '') {
            $query->where('type', $type);
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        $incidents = $query->paginate(20)->withQueryString();

        return view('admin.assets.incidents.index', compact('incidents', 'type', 'status'));
    }

    public function create()
    {
        $assets = Asset::orderBy('name')->get();
        return view('admin.assets.incidents.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'type' => ['required', 'in:DAMAGE,DISPOSAL'],
            'incident_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:OPEN,RESOLVED'],
        ]);

        $data['cost'] = $data['cost'] ?? 0;
        $data['reported_by'] = auth()->id();

        $incident = AssetIncident::create($data);

        if ($incident->type === 'DISPOSAL') {
            $incident->asset->update([
                'condition' => 'DISPOSED',
                'is_active' => false,
            ]);
        }

        if ($incident->type === 'DAMAGE' && $incident->status === 'RESOLVED') {
            $incident->asset->update([
                'condition' => 'MINOR',
            ]);
        }

        return redirect()->route('admin.asset_incidents.index')->with('status', 'Laporan berhasil dibuat.');
    }

    public function edit(AssetIncident $asset_incident)
    {
        $assets = Asset::orderBy('name')->get();
        return view('admin.assets.incidents.edit', [
            'incident' => $asset_incident,
            'assets' => $assets,
        ]);
    }

    public function update(Request $request, AssetIncident $asset_incident)
    {
        $data = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'type' => ['required', 'in:DAMAGE,DISPOSAL'],
            'incident_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:OPEN,RESOLVED'],
        ]);

        $data['cost'] = $data['cost'] ?? 0;
        $asset_incident->update($data);

        if ($data['type'] === 'DISPOSAL') {
            $asset_incident->asset->update([
                'condition' => 'DISPOSED',
                'is_active' => false,
            ]);
        }

        if ($data['type'] === 'DAMAGE' && $data['status'] === 'RESOLVED') {
            $asset_incident->asset->update([
                'condition' => 'MINOR',
            ]);
        }

        return redirect()->route('admin.asset_incidents.index')->with('status', 'Laporan berhasil diperbarui.');
    }
}
