@extends('layouts.dashboard')

@section('content')
@php
    // Normalisasi filters dari controller + fallback ke request
    $f = $filters ?? [];

    $f = array_merge([
        'from'   => $f['from']   ?? request('from', now()->startOfMonth()->toDateString()),
        'to'     => $f['to']     ?? request('to', now()->toDateString()),
        'status' => $f['status'] ?? request('status', 'POSTED'),
        'q'      => $f['q']      ?? request('q'),
    ], $f);

    // Normalisasi summary
    $s = $summary ?? [];
    $s = array_merge([
        'total_rows'  => $s['total_rows']  ?? 0,
        'total_plus'  => $s['total_plus']  ?? 0,
        'total_minus' => $s['total_minus'] ?? 0,
    ], $s);

    $s['net_qty'] = ($s['total_plus'] + $s['total_minus']);

    // ⚠️ BARIS BARU: samakan nama variabel data detail
    // controller lama pakai $rows, view pakai $lines
    $lines = $lines ?? $rows ?? collect(); // bisa plus / minus / nol
@endphp

<div class="flex flex-wrap items-start justify-between gap-3 mb-4">
  <div>
    <h1 class="text-xl font-semibold">Laporan Selisih Stock Opname</h1>
    <div class="text-sm text-gray-600">
      Periode:
      <b>{{ $f['from'] }}</b> s/d <b>{{ $f['to'] }}</b>
      • Status:
      <b>{{ $f['status'] }}</b>
      @if(!empty($f['q']))
        • Item mengandung: <b>{{ $f['q'] }}</b>
      @endif
    </div>
  </div>

  {{-- Tombol PDF belum ada routenya, jadi sementara disembunyikan dulu
  <div class="flex flex-wrap gap-2">
    <a
      class="px-3 py-2 rounded border text-sm"
      href="{{ route('admin.reports.opname_variance.pdf', request()->query()) }}"
      target="_blank"
    >
      Print PDF
    </a>
  </div>
  --}}
</div>

<div class="bg-white border rounded-lg p-4 mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
    <div>
      <label class="text-xs text-gray-600">Dari</label>
      <input type="date" name="from" value="{{ $f['from'] }}" class="w-full border rounded p-2 text-sm">
    </div>

    <div>
      <label class="text-xs text-gray-600">Sampai</label>
      <input type="date" name="to" value="{{ $f['to'] }}" class="w-full border rounded p-2 text-sm">
    </div>

    <div>
      <label class="text-xs text-gray-600">Status Opname</label>
      <select name="status" class="w-full border rounded p-2 text-sm">
        @foreach(['POSTED','DRAFT','CANCELLED','ALL'] as $st)
          <option value="{{ $st }}" @selected($f['status'] === $st)>{{ $st }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="text-xs text-gray-600">Cari Item</label>
      <input
        name="q"
        value="{{ $f['q'] }}"
        placeholder="Nama bahan..."
        class="w-full border rounded p-2 text-sm"
      >
    </div>

    <div class="md:col-span-1 flex items-end gap-2">
      <button class="px-3 py-2 rounded bg-blue-600 text-white text-sm">Filter</button>
      <a href="{{ route('admin.reports.opname_variance') }}" class="px-3 py-2 rounded border text-sm">Reset</a>
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
    <div class="text-lg font-semibold text-green-600">
      {{ number_format($s['total_plus'], 3, ',', '.') }}
    </div>
  </div>

  <div class="bg-white border rounded-lg p-3">
    <div class="text-xs text-gray-600">Total Selisih Minus (Qty)</div>
    <div class="text-lg font-semibold text-red-600">
      {{ number_format($s['total_minus'], 3, ',', '.') }}
    </div>
  </div>
</div>

<div class="bg-white border rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-2 text-left">Opname</th>
          <th class="p-2 text-left">Tanggal</th>
          <th class="p-2 text-left">Status</th>
          <th class="p-2 text-left">Item</th>
          <th class="p-2 text-right">Qty Sistem (Base)</th>
          <th class="p-2 text-right">Qty Fisik (Base)</th>
          <th class="p-2 text-right">Selisih (Base)</th>
        </tr>
      </thead>
      <tbody>
        @forelse($lines as $l)
          @php
            $diff = (float) $l->diff_qty_base;
          @endphp
          <tr class="border-t">
            <td class="p-2">
              <a class="underline" href="{{ route('admin.stock_opname.show', $l->stock_opname_id) }}">
                {{ $l->opname->code ?? ('#'.$l->stock_opname_id) }}
              </a>
            </td>
            <td class="p-2">{{ optional($l->opname)->counted_at }}</td>
            <td class="p-2">{{ optional($l->opname)->status }}</td>
            <td class="p-2">
              {{ $l->item->name ?? '-' }}
              <div class="text-xs text-gray-500">
                Base: {{ $l->item->baseUnit->symbol ?? '-' }}
              </div>
            </td>
            <td class="p-2 text-right">
              {{ number_format((float) $l->system_qty_base, 3, ',', '.') }}
            </td>
            <td class="p-2 text-right">
              {{ number_format((float) $l->physical_qty_base, 3, ',', '.') }}
            </td>
            <td class="p-2 text-right {{ $diff < 0 ? 'text-red-600' : ($diff > 0 ? 'text-green-600' : '') }}">
              {{ number_format($diff, 3, ',', '.') }}
            </td>
          </tr>
        @empty
          <tr class="border-t">
            <td class="p-3 text-gray-600" colspan="7">Tidak ada data.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="p-3">
    {{ $lines->links() }}
  </div>
</div>
@endsection