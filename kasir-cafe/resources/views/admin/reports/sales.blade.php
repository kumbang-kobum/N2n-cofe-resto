@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Laporan Penjualan</h1>

<form class="bg-white border rounded-lg p-4 mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
  <div>
    <label class="text-sm text-gray-600">From</label>
    <input type="date" name="from" value="{{ $from }}" class="w-full rounded border p-2">
  </div>
  <div>
    <label class="text-sm text-gray-600">To</label>
    <input type="date" name="to" value="{{ $to }}" class="w-full rounded border p-2">
  </div>
  <div class="flex items-end">
    <button class="w-full px-3 py-2 rounded bg-gray-900 text-white">Filter</button>
  </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">Omzet</div>
    <div class="text-lg font-semibold">Rp {{ number_format($summary['omzet'],0,',','.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">COGS (HPP)</div>
    <div class="text-lg font-semibold">Rp {{ number_format($summary['cogs'],0,',','.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">Profit Kotor</div>
    <div class="text-lg font-semibold">Rp {{ number_format($summary['profit'],0,',','.') }}</div>
  </div>
</div>

<div class="bg-white border rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-2">Waktu</th>
          <th class="text-right p-2">Total</th>
          <th class="text-right p-2">COGS</th>
          <th class="text-right p-2">Profit</th>
          <th class="text-left p-2">Metode</th>
        </tr>
      </thead>
      <tbody>
        @foreach($sales as $s)
          <tr class="border-t">
            <td class="p-2">{{ \Carbon\Carbon::parse($s->paid_at)->format('d M Y H:i') }}</td>
            <td class="p-2 text-right">{{ number_format($s->total,0,',','.') }}</td>
            <td class="p-2 text-right">{{ number_format($s->cogs_total,0,',','.') }}</td>
            <td class="p-2 text-right">{{ number_format($s->profit_gross,0,',','.') }}</td>
            <td class="p-2">{{ $s->payment_method }}</td>
          </tr>
        @endforeach
        @if($sales->isEmpty())
          <tr class="border-t"><td class="p-2 text-gray-600" colspan="5">Tidak ada transaksi pada range ini.</td></tr>
        @endif
      </tbody>
    </table>
  </div>
</div>
@endsection