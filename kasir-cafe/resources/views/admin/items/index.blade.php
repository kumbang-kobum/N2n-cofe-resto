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

<form method="GET" class="mb-4">
  <div class="flex items-center gap-2">
    <input type="text"
           name="q"
           value="{{ $q ?? '' }}"
           placeholder="Cari nama bahan..."
           class="w-full max-w-sm rounded border px-3 py-2 text-sm">
    <button type="submit"
            class="rounded bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
      Cari
    </button>
    @if(!empty($q))
      <a href="{{ route('admin.items.index') }}"
         class="rounded border px-3 py-2 text-sm hover:bg-gray-50">
        Reset
      </a>
    @endif
  </div>
</form>

<div class="rounded-lg border bg-white">
  <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
    <table class="w-full text-left text-sm">
    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
      <tr>
        <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Nama</th>
        <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Min Stok</th>
        <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Satuan Dasar</th>
        <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Harga / Base</th>
        <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Stok Aktif</th>
        <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Nilai Stok</th>
        <th class="px-3 py-2 text-center sticky top-0 z-10 bg-gray-50">Track Expired</th>
        <th class="px-3 py-2 text-center sticky top-0 z-10 bg-gray-50">Aktif</th>
        <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Aksi</th>
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
          <td class="px-3 py-2 text-right">
            Rp {{ number_format((float) ($item->avg_unit_cost_base ?? 0), 2, ',', '.') }}
          </td>
          <td class="px-3 py-2 text-right">
            {{ number_format((float) ($item->stock_base ?? 0), 3, ',', '.') }}
          </td>
          <td class="px-3 py-2 text-right">
            Rp {{ number_format((float) ($item->stock_value ?? 0), 2, ',', '.') }}
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
            <form action="{{ route('admin.items.destroy', $item) }}"
                  method="POST"
                  class="inline"
                  onsubmit="return confirm('Hapus bahan ini?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="ml-2 text-xs text-red-600 hover:underline">Hapus</button>
            </form>
          </td>
        </tr>
      @empty
        <tr class="border-t">
          <td colspan="9" class="px-3 py-4 text-center text-sm text-gray-500">
            Belum ada data bahan.
          </td>
        </tr>
      @endforelse
    </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  {{ $items->links() }}
</div>
@endsection
