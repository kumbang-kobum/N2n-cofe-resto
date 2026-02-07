@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Admin Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">Omzet ({{ $from }} s/d {{ $to }})</div>
    <div class="text-lg font-semibold">Rp {{ number_format($summary['omzet'],0,',','.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">COGS</div>
    <div class="text-lg font-semibold">Rp {{ number_format($summary['cogs'],0,',','.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">Profit Kotor</div>
    <div class="text-lg font-semibold">Rp {{ number_format($summary['profit'],0,',','.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">Transaksi</div>
    <div class="text-lg font-semibold">{{ $summary['trx'] }}</div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="p-4 font-semibold">Stok Menipis</div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Bahan</th>
            <th class="text-right p-2">Stok</th>
            <th class="text-right p-2">Min</th>
          </tr>
        </thead>
        <tbody>
          @forelse($lowStock as $it)
            <tr class="border-t">
              <td class="p-2">{{ $it->name }} <span class="text-gray-500">({{ $it->baseUnit->symbol }})</span></td>
              <td class="p-2 text-right">{{ number_format($it->stock_base, 3, ',', '.') }}</td>
              <td class="p-2 text-right">{{ number_format((float)$it->min_stock, 3, ',', '.') }}</td>
            </tr>
          @empty
            <tr class="border-t"><td class="p-2 text-gray-600" colspan="3">Aman.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="p-4 font-semibold">Mendekati Expired (≤ 7 hari)</div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Bahan</th>
            <th class="text-right p-2">Qty</th>
            <th class="text-left p-2">Expired</th>
          </tr>
        </thead>
        <tbody>
          @forelse($expSoon as $b)
            <tr class="border-t">
              <td class="p-2">{{ $b->item->name }}</td>
              <td class="p-2 text-right">{{ number_format($b->qty_on_hand_base, 3, ',', '.') }}</td>
              <td class="p-2">{{ \Carbon\Carbon::parse($b->expired_at)->format('d M Y') }}</td>
            </tr>
          @empty
            <tr class="border-t"><td class="p-2 text-gray-600" colspan="3">Tidak ada.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection