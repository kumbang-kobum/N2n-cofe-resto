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

    {{-- KOLOM KIRI: KATALOG MENU --}}
    <div class="bg-white border rounded-lg p-4 flex flex-col h-full">
      <div class="flex items-center justify-between gap-2 mb-3">
        <div>
          <div class="font-semibold">Katalog Menu</div>
          <div class="text-xs text-gray-500">
            Klik kartu menu untuk menambah ke keranjang
          </div>
        </div>

        {{-- Pencarian sederhana di sisi client --}}
        <div class="w-40">
          <input
            type="text"
            placeholder="Cari menu..."
            class="w-full border rounded px-2 py-1 text-sm"
            oninput="filterProducts(this.value)"
          >
        </div>
      </div>

      @if($products->isEmpty())
        <div class="border rounded-lg p-4 text-center text-gray-500 text-sm">
          Belum ada menu aktif yang bisa dijual.
        </div>
      @else
        <div id="product-grid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 overflow-y-auto max-h-[540px] pr-1">
          @foreach($products as $p)
            <form
              method="POST"
              action="{{ route('cashier.pos.add') }}"
              class="product-card border rounded-xl overflow-hidden flex flex-col hover:shadow-md transition-shadow bg-white"
              data-name="{{ strtolower($p->name) }}"
            >
              @csrf
              <input type="hidden" name="sale_id" value="{{ $sale->id }}">
              <input type="hidden" name="product_id" value="{{ $p->id }}">

              {{-- Gambar produk --}}
              <div class="aspect-[4/3] w-full bg-gray-100 overflow-hidden">
                @if(!empty($p->photo_path))
                  <img
                    src="{{ asset('storage/'.$p->photo_path) }}"
                    alt="{{ $p->name }}"
                    class="w-full h-full object-cover"
                  >
                @else
                  <div class="w-full h-full flex items-center justify-center text-xs text-gray-400">
                    Tidak ada gambar
                  </div>
                @endif
              </div>

              {{-- Info + input qty + tombol --}}
              <div class="flex-1 flex flex-col p-2">
                <div class="text-sm font-semibold leading-tight line-clamp-2">
                  {{ $p->name }}
                </div>
                <div class="mt-1 text-xs text-gray-500">
                  Rp {{ number_format($p->price_default, 0, ',', '.') }}
                </div>

                <div class="mt-2 flex items-center justify-between gap-2">
                  <div class="flex items-center gap-1">
                    <span class="text-xs text-gray-500">Qty</span>
                    <input
                      name="qty"
                      type="number"
                      min="0.001"
                      step="0.001"
                      value="1"
                      class="w-16 border rounded px-1 py-0.5 text-xs text-right"
                    >
                  </div>

                  <button
                    type="submit"
                    class="px-2 py-1 rounded text-xs bg-gray-900 text-white hover:bg-gray-800"
                  >
                    Tambah
                  </button>
                </div>
              </div>
            </form>
          @endforeach
        </div>
      @endif
    </div>

    {{-- KOLOM KANAN: KERANJANG & PEMBAYARAN --}}
    <div class="bg-white border rounded-lg p-4 flex flex-col h-full">
      <div class="flex items-center justify-between mb-3">
        <div class="font-semibold">Keranjang</div>
        <div class="text-sm text-gray-600">
          Transaksi #{{ $sale->id }}
        </div>
      </div>

      <div class="overflow-x-auto flex-1">
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
            @forelse($sale->lines as $l)
              <tr class="border-t">
                <td class="p-2">{{ $l->product->name }}</td>
                <td class="p-2 text-right">{{ $l->qty }}</td>
                <td class="p-2 text-right">
                  {{ number_format($l->price, 0, ',', '.') }}
                </td>
                <td class="p-2 text-right">
                  {{ number_format($l->qty * $l->price, 0, ',', '.') }}
                </td>
              </tr>
            @empty
              <tr class="border-t">
                <td colspan="4" class="p-3 text-center text-gray-500 text-sm">
                  Belum ada item di keranjang.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-600">Total</div>
        <div class="text-lg font-semibold">
          Rp {{ number_format($sale->total, 0, ',', '.') }}
        </div>
      </div>

      <form method="POST" action="{{ route('cashier.pos.pay') }}" class="mt-4 space-y-2">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">

        <select name="payment_method" class="w-full rounded border p-2">
          <option value="CASH">CASH</option>
          <option value="QRIS">QRIS</option>
          <option value="DEBIT">DEBIT</option>
        </select>

        <button class="w-full px-3 py-2 rounded bg-green-600 text-white">
          Bayar
        </button>
      </form>
    </div>

  </div>
@endif

{{-- Script kecil untuk filter kartu produk --}}
<script>
  function filterProducts(keyword) {
    keyword = (keyword || '').toLowerCase();
    const cards = document.querySelectorAll('#product-grid .product-card');
    cards.forEach(card => {
      const name = card.getAttribute('data-name') || '';
      card.style.display = name.includes(keyword) ? '' : 'none';
    });
  }
</script>
@endsection