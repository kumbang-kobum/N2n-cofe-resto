@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Stok Bahan (Master Bahan)</h1>

@if (session('status'))
  <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
    {{ session('status') }}
  </div>
@endif

<div class="mb-4 flex items-center justify-between gap-2">
  <div class="text-sm text-gray-600">
    Daftar bahan yang dipakai di resep & stok.
  </div>
  <a href="{{ route('admin.items.create') }}"
     class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
    + Tambah Bahan
  </a>
</div>

<div class="overflow-x-auto rounded-lg border bg-white">
  <table class="w-full text-left text-sm">
    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
      <tr>
        <th class="px-3 py-2">Nama</th>
        <th class="px-3 py-2 text-right">Min Stok</th>
        <th class="px-3 py-2">Satuan Dasar</th>
        <th class="px-3 py-2 text-center">Track Expired</th>
        <th class="px-3 py-2 text-center">Aktif</th>
        <th class="px-3 py-2 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $item)
        <tr class="border-t">
          <td class="px-3 py-2">
            {{ $item->name }}
          </td>
          <td class="px-3 py-2 text-right">
            {{ number_format((float) $item->min_stock, 3, ',', '.') }}
          </td>
          <td class="px-3 py-2">
            {{ $item->baseUnit?->symbol }}
          </td>
          <td class="px-3 py-2 text-center">
            @if($item->track_expiry)
              <span class="rounded bg-blue-50 px-2 py-0.5 text-xs text-blue-700">Ya</span>
            @else
              <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-500">Tidak</span>
            @endif
          </td>
          <td class="px-3 py-2 text-center">
            @if($item->is_active)
              <span class="rounded bg-green-50 px-2 py-0.5 text-xs text-green-700">Aktif</span>
            @else
              <span class="rounded bg-red-50 px-2 py-0.5 text-xs text-red-700">Nonaktif</span>
            @endif
          </td>
          <td class="px-3 py-2 text-right">
            <a href="{{ route('admin.items.edit', $item) }}"
               class="text-xs text-blue-600 hover:underline">Edit</a>
          </td>
        </tr>
      @empty
        <tr class="border-t">
          <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">
            Belum ada data bahan.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">
  {{ $items->links() }}
</div>
@endsection