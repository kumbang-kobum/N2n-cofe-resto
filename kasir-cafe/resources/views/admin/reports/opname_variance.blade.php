@extends('layouts.dashboard')

@section('content')
@php
    $f = $filters ?? [];
    $f = array_merge([
        'from'   => $f['from']   ?? request('from', now()->startOfMonth()->toDateString()),
        'to'     => $f['to']     ?? request('to', now()->toDateString()),
        'status' => $f['status'] ?? request('status', 'POSTED'),
        'q'      => $f['q']      ?? request('q'),
    ], $f);

    $s = $summary ?? [];
    $s = array_merge([
        'total_rows'  => $s['total_rows']  ?? 0,
        'total_plus'  => $s['total_plus']  ?? 0,
        'total_minus' => $s['total_minus'] ?? 0,
    ], $s);

    $lines = $lines ?? $rows ?? collect();
@endphp

<div class="flex flex-wrap items-start justify-between gap-3 mb-4">
  <div>
    <h1 class="text-xl font-semibold">Laporan Selisih Stock Opname</h1>
    <div class="text-sm text-gray-600">
      Periode <b>{{ $f['from'] }}</b> s/d <b>{{ $f['to'] }}</b>
      • Status <b>{{ $f['status'] }}</b>
      @if(!empty($f['q']))
        • Item <b>{{ $f['q'] }}</b>
      @endif
    </div>
  </div>
</div>

<div class="bg-white border rounded-lg p-4 mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
    <div>
      <label class="text-xs text-gray-600">Dari</label>
      <input type="date" name="from" value="{{ $f['from'] }}" class="w-full rounded border p-2 text-sm">
    </div>
    <div>
      <label class="text-xs text-gray-600">Sampai</label>
      <input type="date" name="to" value="{{ $f['to'] }}" class="w-full rounded border p-2 text-sm">
    </div>
    <div>
      <label class="text-xs text-gray-600">Status Opname</label>
      <select name="status" class="w-full rounded border p-2 text-sm">
        @foreach(['POSTED','DRAFT','CANCELLED','ALL'] as $st)
          <option value="{{ $st }}" @selected($f['status'] === $st)>{{ $st }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs text-gray-600">Cari Item</label>
      <input name="q" value="{{ $f['q'] }}" placeholder="Nama bahan..." class="w-full rounded border p-2 text-sm">
    </div>
    <div class="flex items-end gap-2">
      <button class="px-3 py-2 rounded bg-blue-600 text-white text-sm">Filter</button>
      <a href="{{ route('admin.reports.opname_variance') }}" class="px-3 py-2 rounded border text-sm hover:bg-gray-50">Reset</a>
    </div>
  </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
  <div class="bg-white border rounded-lg p-3">
    <div class="text-xs text-gray-600">Total Baris Selisih</div>
    <div class="text-lg font-semibold">{{ number_format($s['total_rows'], 0, ',', '.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-3">
    <div class="text-xs text-gray-600">Total Selisih Plus (Qty)</div>
    <div class="text-lg font-semibold text-green-600">{{ number_format($s['total_plus'], 3, ',', '.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-3">
    <div class="text-xs text-gray-600">Total Selisih Minus (Qty)</div>
    <div class="text-lg font-semibold text-red-600">{{ number_format($s['total_minus'], 3, ',', '.') }}</div>
  </div>
</div>

<div class="rounded-lg border bg-white">
  <div class="border-b px-4 py-3">
    <div class="font-semibold">Detail Selisih</div>
    <div class="text-xs text-gray-500">Tampilkan hanya baris yang benar-benar berbeda antara stok sistem dan hitungan fisik.</div>
  </div>
  <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs uppercase text-gray-500">
        <tr>
          <th class="p-2 text-left sticky top-0 z-10 bg-gray-50">Opname</th>
          <th class="p-2 text-left sticky top-0 z-10 bg-gray-50">Tanggal</th>
          <th class="p-2 text-left sticky top-0 z-10 bg-gray-50">Status</th>
          <th class="p-2 text-left sticky top-0 z-10 bg-gray-50">Item</th>
          <th class="p-2 text-right sticky top-0 z-10 bg-gray-50">Qty Sistem</th>
          <th class="p-2 text-right sticky top-0 z-10 bg-gray-50">Qty Fisik</th>
          <th class="p-2 text-right sticky top-0 z-10 bg-gray-50">Selisih</th>
        </tr>
      </thead>
      <tbody>
        @forelse($lines as $line)
          @php($diff = (float) $line->diff_qty_base)
          <tr class="border-t">
            <td class="p-2">
              <a class="font-medium text-blue-600 hover:underline" href="{{ route('admin.stock_opname.show', $line->stock_opname_id) }}">
                {{ $line->opname->code ?? ('#'.$line->stock_opname_id) }}
              </a>
            </td>
            <td class="p-2">{{ optional($line->opname)->counted_at }}</td>
            <td class="p-2">{{ optional($line->opname)->status }}</td>
            <td class="p-2">
              {{ $line->item->name ?? '-' }}
              <div class="text-xs text-gray-500">Base: {{ $line->item->baseUnit->symbol ?? '-' }}</div>
            </td>
            <td class="p-2 text-right">{{ number_format((float) $line->system_qty_base, 3, ',', '.') }}</td>
            <td class="p-2 text-right">{{ number_format((float) $line->physical_qty_base, 3, ',', '.') }}</td>
            <td class="p-2 text-right font-medium {{ $diff < 0 ? 'text-red-600' : ($diff > 0 ? 'text-green-600' : '') }}">
              {{ number_format($diff, 3, ',', '.') }}
            </td>
          </tr>
        @empty
          <tr class="border-t">
            <td class="p-3 text-center text-gray-500" colspan="7">Tidak ada data.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="p-3 border-t">
    {{ $lines->links() }}
  </div>
</div>
@endsection
