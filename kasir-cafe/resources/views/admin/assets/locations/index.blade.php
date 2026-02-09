@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Lokasi Inventaris</h1>

@if (session('status'))
  <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
    {{ session('status') }}
  </div>
@endif

<div class="mb-4 flex items-center justify-between gap-2">
  <div class="text-sm text-gray-600">Master lokasi untuk dropdown.</div>
  <a href="{{ route('admin.asset_locations.create') }}"
     class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
    + Tambah Lokasi
  </a>
</div>

<div class="overflow-x-auto rounded-lg border bg-white">
  <table class="w-full text-left text-sm">
    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
      <tr>
        <th class="px-3 py-2">Nama</th>
        <th class="px-3 py-2 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($locations as $l)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $l->name }}</td>
          <td class="px-3 py-2 text-right">
            <a href="{{ route('admin.asset_locations.edit', $l) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
          </td>
        </tr>
      @empty
        <tr class="border-t">
          <td colspan="2" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada data.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">
  {{ $locations->links() }}
</div>
@endsection
