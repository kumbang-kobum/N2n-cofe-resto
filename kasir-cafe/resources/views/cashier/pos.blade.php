@extends('layouts.dashboard')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
    <div>
        <h1 class="text-xl font-semibold">Kasir (POS)</h1>
        <div class="text-xs text-gray-500">Kelola transaksi dine-in / takeaway</div>
    </div>

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

@if ($errors->any())
    <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc ml-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (!empty($openSales) && $openSales->count() > 0)
    <div class="mb-4 bg-white border rounded-lg p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <div>
                <div class="font-semibold">Open Bills</div>
                <div class="text-xs text-gray-500">Transaksi belum dibayar</div>
            </div>
            <form method="GET" action="{{ route('cashier.pos') }}" class="w-full sm:w-64">
                <input type="text"
                       name="open_q"
                       value="{{ $openQuery ?? '' }}"
                       placeholder="Cari nama tamu / meja / ID"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($openSales as $os)
                <div class="rounded-lg border px-3 py-2 text-sm hover:border-blue-400 hover:bg-blue-50/40 transition">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold">#{{ $os->id }}</div>
                        <span class="text-[11px] px-2 py-0.5 rounded-full {{ $os->status === 'OPEN' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $os->status }}
                        </span>
                    </div>
                    <div class="mt-1 text-xs text-gray-600">
                        Meja: {{ $os->table_no ?? '-' }} | Tamu: {{ $os->customer_name ?? '-' }}
                    </div>
                    <div class="mt-1 text-xs text-gray-600">
                        Subtotal: Rp {{ number_format($os->total, 0, ',', '.') }}
                    </div>
                    <div class="mt-1 text-[11px] text-gray-400">
                        Update: {{ optional($os->updated_at)->format('d/m/Y H:i') }}
                    </div>
                    <div class="mt-2 flex items-center justify-between gap-2">
                        <a href="{{ route('cashier.pos', ['sale_id' => $os->id]) }}"
                           class="text-[11px] px-2 py-1 rounded bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100">
                            Buka
                        </a>
                        <form method="POST" action="{{ route('cashier.pos.cancel') }}" onsubmit="return confirm('Batalkan transaksi ini?');">
                            @csrf
                            <input type="hidden" name="sale_id" value="{{ $os->id }}">
                            <button type="submit"
                                    class="text-[11px] px-2 py-1 rounded bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">
                                Batalkan
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if (!$sale)
    <div class="bg-white border rounded-lg p-6 text-gray-700">
        Belum ada transaksi aktif. Klik
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
                <div>
                    <div class="font-semibold">Keranjang</div>
                    <div class="text-xs text-gray-500">Transaksi #{{ $sale->id }} • Status {{ $sale->status }}</div>
                </div>
                <div class="flex items-center gap-2">
                    @if ($sale->lines->count() > 0)
                        <form method="POST" action="{{ route('cashier.pos.clear') }}" onsubmit="return confirm('Kosongkan semua item di keranjang?');">
                            @csrf
                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                            <button type="submit"
                                    class="text-[11px] px-2 py-1 rounded bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">
                                Kosongkan
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('cashier.pos.cancel') }}" onsubmit="return confirm('Batalkan transaksi ini?');">
                        @csrf
                        <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                        <button type="submit"
                                class="text-[11px] px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">
                            Batalkan
                        </button>
                    </form>
                </div>
            </div>

            <div class="mb-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">No. Meja</label>
                    <input type="text"
                           name="table_no"
                           form="pos-payment-form"
                           value="{{ old('table_no', $sale->table_no) }}"
                           class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Tamu</label>
                    <input type="text"
                           name="customer_name"
                           form="pos-payment-form"
                           value="{{ old('customer_name', $sale->customer_name) }}"
                           class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="sm:col-span-2 flex items-center justify-between">
                    <div class="text-[11px] text-gray-500">Isi meja/tamu agar mudah dicari saat bayar di akhir.</div>
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
                            <th class="text-right p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sale->lines as $l)
                            <tr class="border-t">
                                <td class="p-2">{{ $l->product->name }}</td>
                                <td class="p-2 text-right">
                                    <form method="POST"
                                          action="{{ route('cashier.pos.line.update', $l) }}"
                                          class="flex items-center justify-end gap-2">
                                        @csrf
                                        <input type="number"
                                               name="qty"
                                               value="{{ $l->qty }}"
                                               min="1"
                                               step="1"
                                               class="w-16 rounded border border-gray-300 px-2 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <button type="submit"
                                                class="text-[11px] px-2 py-1 rounded bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td class="p-2 text-right">
                                    {{ number_format($l->price, 0, ',', '.') }}
                                </td>
                                <td class="p-2 text-right">
                                    {{ number_format($l->qty * $l->price, 0, ',', '.') }}
                                </td>
                                <td class="p-2 text-right">
                                    <form method="POST"
                                          action="{{ route('cashier.pos.line.delete', $l) }}"
                                          onsubmit="return confirm('Hapus item ini?');">
                                        @csrf
                                        <button type="submit"
                                                class="text-[11px] px-2 py-1 rounded bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-3 text-center text-xs text-gray-500">
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

            <form id="pos-payment-form" method="POST" action="{{ route('cashier.pos.pay') }}" class="mt-4 space-y-2">
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

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Uang Dibayar (Rp)</label>
                    <input type="number"
                           name="paid_amount"
                           value="{{ old('paid_amount', 0) }}"
                           min="0"
                           step="1000"
                           class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Kembalian</span>
                    <span class="font-semibold text-gray-800" id="change-display">Rp 0</span>
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

                <button type="submit"
                        formnovalidate
                        formaction="{{ route('cashier.pos.hold') }}"
                        class="w-full px-3 py-2 rounded-md bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold">
                    Simpan & Tahan (Bayar Belakangan)
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
            const paidInput = document.querySelector('input[name="paid_amount"]');
            const changeDisplay = document.getElementById('change-display');

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

                if (paidInput && changeDisplay) {
                    let paid = parseFloat(paidInput.value || '0');
                    if (Number.isNaN(paid) || paid < 0) paid = 0;
                    const change = Math.max(0, paid - total);
                    changeDisplay.textContent = 'Rp ' + formatRp(change);
                }
            };

            discountInput.addEventListener('input', recalc);
            if (paidInput) paidInput.addEventListener('input', recalc);
            recalc();
        }
    });
</script>
@endpush

@endsection
