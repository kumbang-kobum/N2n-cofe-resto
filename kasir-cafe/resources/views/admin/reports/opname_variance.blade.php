@extends('layouts.dashboard')

@section('content')
@php
  $f = $filters;
  $s = $summary;
@endphp

<div class="flex flex-wrap items-start justify-between gap-3 mb-4">
  <div>
    <h1 class="text-xl font-semibold">Laporan Selisih Stock Opname</h1>
    <div class="text-sm text-gray-600">
      Periode: <b>{{ $f['from'] }}</b> s/d <b>{{ $f['to'] }}</b> • Status: <b>{{ $f['status'] }}</b>
    </div>
  </div>

  <div class="flex flex-wrap gap-2">
    <a
      class="px-3 py-2 rounded border text-sm"
      href="{{ route('admin.reports.opname_variance.pdf', request()->query()) }}"
      target="_blank"
    >
      Print PDF
    </a>
  </div>
</div>

<div class="bg-white border rounded-lg p-4 mb-4">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
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
          <option value="{{ $st }}" @selected($f['status']===$st)>{{ $st }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="text-xs text-gray-600">Cari Item</label>
      <input name="q" value="{{ $f['q'] }}" placeholder="Nama bahan..." class="w-full border rounded p-2 text-sm">
    </div>

    <div class="md:col-span-4 flex gap-2">
      <button class="px-3 py-2 rounded bg-blue-600 text-white text-sm">Filter</button>
      <a href="{{ route('admin.reports.opname_variance') }}" class="px-3 py-2 rounded border text-sm">Reset</a>
    </div>
  </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
  <div class="bg-white border rounded-lg p-3">
    <div class="text-xs text-gray-600">Total Selisih Plus</div>
    <div class="text-lg font-semibold">{{ number_format($s['plus_qty'], 3, ',', '.') }}</div>
    <div class="text-sm text-gray-600">Nilai: Rp {{ number_format($s['plus_val'], 0, ',', '.') }}</div>
  </div>

  <div class="bg-white border rounded-lg p-3">
    <div class="text-xs text-gray-600">Total Selisih Minus</div>
    <div class="text-lg font-semibold">{{ number_format($s['minus_qty'], 3, ',', '.') }}</div>
    <div class="text-sm text-gray-600">Nilai: Rp {{ number_format($s['minus_val'], 0, ',', '.') }}</div>
  </div>

  <div class="bg-white border rounded-lg p-3">
    <div class="text-xs text-gray-600">Net Qty</div>
    <div class="text-lg font-semibold">{{ number_format($s['net_qty'], 3, ',', '.') }}</div>
  </div>

  <div class="bg-white border rounded-lg p-3">
    <div class="text-xs text-gray-600">Net Nilai</div>
    <div class="text-lg font-semibold">Rp {{ number_format($s['net_val'], 0, ',', '.') }}</div>
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
          <th class="p-2 text-right">Sistem</th>
          <th class="p-2 text-right">Fisik</th>
          <th class="p-2 text-right">Selisih</th>
          <th class="p-2 text-right">Cost</th>
          <th class="p-2 text-right">Nilai Selisih</th>
        </tr>
      </thead>
      <tbody>
        @forelse($lines as $l)
          @php
            $diff = (float)$l->diff_qty_base;
            $cost = (float)($l->unit_cost_base ?? 0);
            $val  = $diff * $cost;
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
            <td class="p-2 text-right">{{ number_format((float)$l->system_qty_base, 3, ',', '.') }}</td>
            <td class="p-2 text-right">{{ number_format((float)$l->physical_qty_base, 3, ',', '.') }}</td>
            <td class="p-2 text-right {{ $diff < 0 ? 'text-red-600' : ($diff > 0 ? 'text-green-600' : '') }}">
              {{ number_format($diff, 3, ',', '.') }}
            </td>
            <td class="p-2 text-right">{{ number_format($cost, 3, ',', '.') }}</td>
            <td class="p-2 text-right {{ $val < 0 ? 'text-red-600' : ($val > 0 ? 'text-green-600' : '') }}">
              Rp {{ number_format($val, 0, ',', '.') }}
            </td>
          </tr>
        @empty
          <tr class="border-t">
            <td class="p-3 text-gray-600" colspan="9">Tidak ada data.</td>
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