@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Edit Laporan Kerusakan/Pemusnahan</h1>

<div class="bg-white border rounded-lg p-4 max-w-2xl">
  <form method="POST" action="{{ route('admin.asset_incidents.update', $incident) }}" class="space-y-4">
    @csrf
    @method('PUT')

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Aset</label>
      <select name="asset_id" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
        @foreach($assets as $a)
          <option value="{{ $a->id }}" @selected(old('asset_id', $incident->asset_id) == $a->id)>{{ $a->name }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
      <select name="type" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
        <option value="DAMAGE" @selected(old('type', $incident->type) === 'DAMAGE')>DAMAGE</option>
        <option value="DISPOSAL" @selected(old('type', $incident->type) === 'DISPOSAL')>DISPOSAL</option>
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
      <input type="date" name="incident_date" value="{{ old('incident_date', $incident->incident_date->format('Y-m-d')) }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
      <textarea name="description" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" rows="3">{{ old('description', $incident->description) }}</textarea>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Biaya</label>
      <input type="number" name="cost" value="{{ old('cost', $incident->cost) }}" min="0" step="1000"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
      <select name="status" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
        <option value="OPEN" @selected(old('status', $incident->status) === 'OPEN')>OPEN</option>
        <option value="RESOLVED" @selected(old('status', $incident->status) === 'RESOLVED')>RESOLVED</option>
      </select>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.asset_incidents.index') }}" class="px-3 py-2 rounded border text-sm">Batal</a>
      <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Simpan</button>
    </div>
  </form>
</div>
@endsection
