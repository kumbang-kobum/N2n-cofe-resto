@extends('layouts.dashboard')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
    <h1 class="text-xl font-semibold">Kasir (POS)</h1>

    <form method="POST" action="{{ route('cashier.pos.new') }}">
        @csrf
        <button type="submit"
            class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
            + Transaksi Baru
        </button>
    </form>
</div>

@if (session('status'))
    <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
        {{ session('status') }}
    </div>
@endif

@if (!$sale)
    {{-- Jika belum ada transaksi --}}
    <div class="bg-white border rounded-lg p-6 text-gray-700">
        Belum ada transaksi. Klik
        <span class="font-semibold">+ Transaksi Baru</span>
        untuk mulai.
    </div>
@else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- KATALOG MENU --}}
        <div class="bg-white border rounded-lg p-4">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <div class="font-semibold">Katalog Menu</div>
                    <div class="text-xs text-gray-500">
                        Klik kartu menu untuk menambah ke keranjang.
                    </div>
                </div>

                <div class="w-full sm:w-56">
                    <input type="text"
                        id="product-search"
                        placeholder="Cari menu..."
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            @if ($products->isEmpty())
                <div class="text-sm text-gray-500">
                    Menu tidak ditemukan.
                </div>
            @else
                <div id="product-grid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach ($products as $p)
                        <form method="POST"
                              action="{{ route('cashier.pos.add') }}"
                              class="product-card group bg-gray-50 rounded-lg border hover:border-blue-400 hover:bg-blue-50/40 transition cursor-pointer flex flex-col"
                              data-name="{{ Str::lower($p->name) }}"
                              data-warning="{{ $p->stock_warning ?? '' }}">
                            @csrf
                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                            <input type="hidden" name="product_id" value="{{ $p->id }}">

                            {{-- GAMBAR PRODUK --}}
                            <div class="w-full h-24 rounded-t-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                @if ($p->image_path)
                                    <img src="{{ asset('storage/' . $p->image_path) }}"
                                         alt="{{ $p->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-150">
                                @else
                                    <span class="text-[11px] text-gray-500">
                                        Tidak ada gambar
                                    </span>
                                @endif
                            </div>

                            {{-- INFO + QTY --}}
                            <div class="flex-1 flex flex-col p-2.5">
                                <div class="text-sm font-semibold leading-tight line-clamp-2">
                                    {{ $p->name }}
                                </div>
                                @if (!empty($p->stock_warning))
                                    <div class="mt-1 text-[10px] font-medium text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                                        {{ $p->stock_warning }}
                                    </div>
                                @endif
                                <div class="text-xs text-gray-500">
                                    Rp {{ number_format($p->price_default, 0, ',', '.') }}
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-1">
                                    <label class="text-[11px] text-gray-500">
                                        Qty
                                    </label>
                                    <input type="number"
                                           name="qty"
                                           value="1"
                                           min="1"
                                           step="1"
                                           class="w-16 rounded border border-gray-300 px-1.5 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <button type="submit"
                                        class="mt-2 w-full text-[11px] font-medium text-blue-600 group-hover:text-blue-700">
                                    Tambah ke keranjang
                                </button>
                            </div>
                        </form>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- KERANJANG --}}
        <div class="bg-white border rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">Keranjang</div>
                <div class="text-xs text-gray-500">
                    Transaksi #{{ $sale->id }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs md:text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-2">Menu</th>
                            <th class="text-right p-2">Qty</th>
                            <th class="text-right p-2">Harga</th>
                            <th class="text-right p-2">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sale->lines as $l)
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
                            <tr>
                                <td colspan="4" class="p-3 text-center text-xs text-gray-500">
                                    Belum ada item di keranjang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @php
                $taxRate = (float) config('pos.tax_rate', 0.10);
                $discountAmount = (float) ($sale->discount_amount ?? 0);
                $taxBase = max(0, (float) $sale->total - $discountAmount);
                $taxAmount = round($taxBase * $taxRate, 2);
                $grandTotal = $taxBase + $taxAmount;
            @endphp

            <div class="mt-4 text-sm text-gray-600 space-y-1"
                 data-summary
                 data-subtotal="{{ (float) $sale->total }}"
                 data-tax-rate="{{ (float) $taxRate }}">
                <div class="flex items-center justify-between">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-800">
                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Diskon</span>
                    <span class="font-semibold text-gray-800" data-discount>
                        Rp {{ number_format($discountAmount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Pajak ({{ (int) ($taxRate * 100) }}%)</span>
                    <span class="font-semibold text-gray-800" data-tax>
                        Rp {{ number_format($taxAmount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-base">
                    <span>Total</span>
                    <span class="font-semibold" data-total>
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('cashier.pos.pay') }}" class="mt-4 space-y-2">
                @csrf
                <input type="hidden" name="sale_id" value="{{ $sale->id }}">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Diskon (Rp)</label>
                    <input type="number"
                           name="discount_amount"
                           value="{{ old('discount_amount', 0) }}"
                           min="0"
                           step="100"
                           class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <select name="payment_method"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="CASH">CASH</option>
                    <option value="QRIS">QRIS</option>
                    <option value="DEBIT">DEBIT</option>
                </select>

                <button type="submit"
                        class="w-full px-3 py-2 rounded-md bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">
                    Bayar
                </button>
            </form>
        </div>
    </div>
@endif

{{-- Pencarian katalog (client side) --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('product-search');
        const cards      = document.querySelectorAll('.product-card');
        const forms      = document.querySelectorAll('.product-card');
        const discountInput = document.querySelector('input[name="discount_amount"]');
        const summaryEl = document.querySelector('[data-summary]');

        if (!searchInput) return;

        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();

            cards.forEach(card => {
                const name = card.dataset.name || '';
                card.style.display = name.includes(q) ? '' : 'none';
            });
        });

        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                const warning = (this.dataset.warning || '').trim();
                if (!warning) return;

                const ok = window.confirm('Perhatian: ' + warning + '.\nTetap tambahkan ke keranjang?');
                if (!ok) {
                    e.preventDefault();
                }
            });
        });

        if (discountInput && summaryEl) {
            const subtotal = parseFloat(summaryEl.dataset.subtotal || '0');
            const taxRate = parseFloat(summaryEl.dataset.taxRate || '0');
            const discountDisplay = summaryEl.querySelector('[data-discount]');
            const taxDisplay = summaryEl.querySelector('[data-tax]');
            const totalDisplay = summaryEl.querySelector('[data-total]');

            const formatRp = (n) => new Intl.NumberFormat('id-ID').format(n);

            const recalc = () => {
                let discount = parseFloat(discountInput.value || '0');
                if (Number.isNaN(discount) || discount < 0) discount = 0;
                if (discount > subtotal) discount = subtotal;

                const taxBase = Math.max(0, subtotal - discount);
                const tax = Math.round(taxBase * taxRate);
                const total = taxBase + tax;

                if (discountDisplay) discountDisplay.textContent = 'Rp ' + formatRp(discount);
                if (taxDisplay) taxDisplay.textContent = 'Rp ' + formatRp(tax);
                if (totalDisplay) totalDisplay.textContent = 'Rp ' + formatRp(total);
            };

            discountInput.addEventListener('input', recalc);
            recalc();
        }
    });
</script>
@endpush

@endsection
