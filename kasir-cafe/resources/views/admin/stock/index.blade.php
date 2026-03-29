@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Stok Saat Ini</h1>

<div class="mb-4 flex items-center justify-between gap-2">
  <div class="text-sm text-gray-600">
    Ringkasan stok aktif per bahan dan batch yang mendekati expired.
  </div>
  <div class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
    {{ $items->count() }} bahan aktif
  </div>
</div>

<form method="GET" class="mb-4">
  <div class="flex items-center gap-2">
    <input type="text"
           name="q"
           value="{{ $q ?? '' }}"
           placeholder="Cari bahan..."
           class="w-full max-w-sm rounded border px-3 py-2 text-sm">
    <button type="submit"
            class="rounded bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
      Cari
    </button>
    @if(!empty($q))
      <a href="{{ route('admin.stock.index') }}"
         class="rounded border px-3 py-2 text-sm hover:bg-gray-50">
        Reset
      </a>
    @endif
  </div>
</form>

<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] gap-4">
  <div class="rounded-lg border bg-white">
    <div class="border-b px-4 py-3">
      <div class="font-semibold">Ringkasan Stok</div>
      <div class="text-xs text-gray-500">Stok aktif, harga rata-rata, dan nilai stok per bahan.</div>
    </div>
    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
      <table class="w-full text-left text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
          <tr>
            <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Bahan</th>
            <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Satuan Dasar</th>
            <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Stok Aktif</th>
            <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Harga / Base</th>
            <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Nilai Stok</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $item)
            <tr class="border-t">
              <td class="px-3 py-2 font-medium text-slate-800">{{ $item->name }}</td>
              <td class="px-3 py-2">{{ $item->baseUnit?->symbol }}</td>
              <td class="px-3 py-2 text-right">{{ number_format((float) $item->stock_base, 3, ',', '.') }}</td>
              <td class="px-3 py-2 text-right">Rp {{ number_format((float) ($item->avg_unit_cost_base ?? 0), 2, ',', '.') }}</td>
              <td class="px-3 py-2 text-right">Rp {{ number_format((float) ($item->stock_value ?? 0), 2, ',', '.') }}</td>
            </tr>
          @empty
            <tr class="border-t">
              <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">
                Belum ada stok aktif yang cocok dengan pencarian.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="rounded-lg border bg-white">
    <div class="border-b px-4 py-3">
      <div class="font-semibold">Mendekati Expired (≤ 7 hari)</div>
      <div class="text-xs text-gray-500">Batch aktif yang perlu segera diprioritaskan.
      </div>
    </div>
    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
      <table class="w-full text-left text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
          <tr>
            <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Bahan</th>
            <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Qty</th>
            <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Nilai Batch</th>
            <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Expired</th>
          </tr>
        </thead>
        <tbody>
          @forelse($batchesExpSoon as $batch)
            <tr class="border-t">
              <td class="px-3 py-2 font-medium text-slate-800">{{ $batch->item->name }}</td>
              <td class="px-3 py-2 text-right">{{ number_format((float) $batch->qty_on_hand_base, 3, ',', '.') }}</td>
              <td class="px-3 py-2 text-right">Rp {{ number_format((float) $batch->qty_on_hand_base * (float) $batch->unit_cost_base, 2, ',', '.') }}</td>
              <td class="px-3 py-2">{{ \Carbon\Carbon::parse($batch->expired_at)->format('d M Y') }}</td>
            </tr>
          @empty
            <tr class="border-t">
              <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">
                Tidak ada batch yang mendekati expired.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
