@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Laporan Kerusakan & Pemusnahan</h1>

@if (session('status'))
  <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
    {{ session('status') }}
  </div>
@endif

<div class="mb-4 flex items-center justify-between gap-2">
  <div class="text-sm text-gray-600">Catatan kerusakan & pemusnahan inventaris.</div>
  <a href="{{ route('admin.asset_incidents.create') }}"
     class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
    + Tambah Laporan
  </a>
</div>

<div class="bg-white border rounded-lg p-4 mb-4">
  <form method="GET" action="{{ route('admin.asset_incidents.index') }}" class="flex flex-wrap items-end gap-3">
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
      <select name="type" class="border rounded px-3 py-2 text-sm">
        <option value="">Semua</option>
        <option value="DAMAGE" @selected($type === 'DAMAGE')>DAMAGE</option>
        <option value="DISPOSAL" @selected($type === 'DISPOSAL')>DISPOSAL</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
      <select name="status" class="border rounded px-3 py-2 text-sm">
        <option value="">Semua</option>
        <option value="OPEN" @selected($status === 'OPEN')>OPEN</option>
        <option value="RESOLVED" @selected($status === 'RESOLVED')>RESOLVED</option>
      </select>
    </div>
    <div>
      <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium">
        Tampilkan
      </button>
    </div>
  </form>
</div>

<div class="overflow-x-auto rounded-lg border bg-white">
  <table class="w-full text-left text-sm">
    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
      <tr>
        <th class="px-3 py-2">Tanggal</th>
        <th class="px-3 py-2">Aset</th>
        <th class="px-3 py-2">Tipe</th>
        <th class="px-3 py-2">Status</th>
        <th class="px-3 py-2 text-right">Biaya</th>
        <th class="px-3 py-2 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($incidents as $i)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $i->incident_date->format('d/m/Y') }}</td>
          <td class="px-3 py-2">{{ $i->asset->name }}</td>
          <td class="px-3 py-2">{{ $i->type }}</td>
          <td class="px-3 py-2">{{ $i->status }}</td>
          <td class="px-3 py-2 text-right">{{ number_format($i->cost, 0, ',', '.') }}</td>
          <td class="px-3 py-2 text-right">
            <a href="{{ route('admin.asset_incidents.edit', $i) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
          </td>
        </tr>
      @empty
        <tr class="border-t">
          <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada data.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">
  {{ $incidents->links() }}
</div>
@endsection
