@extends('layouts.dashboard')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
  <h1 class="text-xl font-semibold">Kasir (POS)</h1>

  <form method="POST" action="{{ route('cashier.pos.new') }}">
    @csrf
    <button class="px-3 py-2 rounded bg-gray-900 text-white">+ Transaksi Baru</button>
  </form>
</div>

@if(!$sale)
  <div class="bg-white border rounded-lg p-4 text-gray-700">
    Klik <b>Transaksi Baru</b> untuk mulai.
  </div>
@else
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <div class="bg-white border rounded-lg p-4">
      <div class="font-semibold mb-3">Tambah Menu</div>

      <form method="POST" action="{{ route('cashier.pos.add') }}" class="space-y-3">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">

        <div>
          <label class="text-sm text-gray-600">Menu</label>
          <select name="product_id" class="w-full rounded border p-2">
            @foreach($products as $p)
              <option value="{{ $p->id }}">{{ $p->name }} — Rp {{ number_format($p->price_default,0,',','.') }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-600">Qty</label>
          <input name="qty" type="number" step="0.001" value="1" class="w-full rounded border p-2">
        </div>

        <button class="px-3 py-2 rounded bg-gray-900 text-white">Tambah</button>
      </form>
    </div>

    <div class="bg-white border rounded-lg p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="font-semibold">Keranjang</div>
        <div class="text-sm text-gray-600">#{{ $sale->id }}</div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-2">Menu</th>
              <th class="text-right p-2">Qty</th>
              <th class="text-right p-2">Harga</th>
              <th class="text-right p-2">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sale->lines as $l)
              <tr class="border-t">
                <td class="p-2">{{ $l->product->name }}</td>
                <td class="p-2 text-right">{{ $l->qty }}</td>
                <td class="p-2 text-right">{{ number_format($l->price,0,',','.') }}</td>
                <td class="p-2 text-right">{{ number_format($l->qty * $l->price,0,',','.') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-600">Total</div>
        <div class="text-lg font-semibold">Rp {{ number_format($sale->total,0,',','.') }}</div>
      </div>

      <form method="POST" action="{{ route('cashier.pos.pay') }}" class="mt-4 space-y-2">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">
        <select name="payment_method" class="w-full rounded border p-2">
          <option value="CASH">CASH</option>
          <option value="QRIS">QRIS</option>
          <option value="DEBIT">DEBIT</option>
        </select>
        <button class="w-full px-3 py-2 rounded bg-green-600 text-white">Bayar</button>
      </form>
    </div>
  </div>
@endif
@endsection