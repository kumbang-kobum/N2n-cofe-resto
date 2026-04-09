@extends('layouts.dashboard')

@section('content')
<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
  <div>
    <h1 class="text-xl font-semibold text-slate-900">Stok Bahan (Master Bahan)</h1>
    <p class="mt-1 text-sm text-slate-600">Daftar bahan utama yang dipakai di resep, stok, receiving, dan stock opname.</p>
  </div>
  <a href="{{ route('admin.items.create') }}" class="btn-primary">
    + Tambah Bahan
  </a>
</div>

@if (session('status'))
  <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
    {{ session('status') }}
  </div>
@endif

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
  <div class="stat-card">
    <div class="stat-label">Total Bahan</div>
    <div class="stat-value">{{ number_format($items->total(), 0, ',', '.') }}</div>
    <div class="stat-meta">Termasuk bahan aktif dan nonaktif.</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Filter Aktif</div>
    <div class="stat-value text-2xl">{{ !empty($q) ? $q : 'Semua' }}</div>
    <div class="stat-meta">Gunakan pencarian untuk audit bahan lebih cepat.</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Halaman Saat Ini</div>
    <div class="stat-value">{{ $items->currentPage() }}</div>
    <div class="stat-meta">Navigasi data tetap ringan saat jumlah bahan bertambah.</div>
  </div>
</div>

<div class="panel-section mb-4">
  <form method="GET" class="flex flex-wrap items-center gap-2">
    <input type="text"
           name="q"
           value="{{ $q ?? '' }}"
           placeholder="Cari nama bahan..."
           class="dashboard-input max-w-sm">
    <button type="submit" class="btn-primary">
      Cari
    </button>
    @if(!empty($q))
      <a href="{{ route('admin.items.index') }}" class="btn-secondary">
        Reset
      </a>
    @endif
  </form>
</div>

<div class="table-shell">
  <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
    <table class="w-full text-left text-sm">
    <thead class="table-head">
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
            <div class="font-medium text-slate-900">{{ $item->name }}</div>
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
               class="text-xs font-medium text-blue-600 hover:underline">Edit</a>
            <form action="{{ route('admin.items.destroy', $item) }}"
                  method="POST"
                  class="inline"
                  onsubmit="return confirm('Hapus bahan ini?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="ml-2 text-xs font-medium text-red-600 hover:underline">Hapus</button>
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

<div class="mt-4">
  {{ $items->links() }}
</div>
@endsection
