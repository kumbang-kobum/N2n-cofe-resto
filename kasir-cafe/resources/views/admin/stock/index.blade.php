@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Stok</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="p-4 font-semibold">Ringkasan Stok</div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Bahan</th>
            <th class="text-right p-2">Stok (base)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $it)
            <tr class="border-t">
              <td class="p-2">{{ $it->name }} <span class="text-gray-500">({{ $it->baseUnit->symbol }})</span></td>
              <td class="p-2 text-right">{{ number_format($it->stock_base, 3, ',', '.') }}</td>
            </tr>
          @endforeach
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
          @foreach($batchesExpSoon as $b)
            <tr class="border-t">
              <td class="p-2">{{ $b->item->name }}</td>
              <td class="p-2 text-right">{{ number_format($b->qty_on_hand_base, 3, ',', '.') }}</td>
              <td class="p-2">{{ \Carbon\Carbon::parse($b->expired_at)->format('d M Y') }}</td>
            </tr>
          @endforeach
          @if($batchesExpSoon->isEmpty())
            <tr class="border-t"><td class="p-2 text-gray-600" colspan="3">Tidak ada.</td></tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection