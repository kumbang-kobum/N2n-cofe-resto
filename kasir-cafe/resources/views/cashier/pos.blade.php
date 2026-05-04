@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($sale)
        @php
            $oldSaleId = old('sale_id');
            $useOld = $oldSaleId && (int) $oldSaleId === (int) $sale->id;
            $taxRate = (float) ($taxRate ?? config('pos.tax_rate', 0.10));
            $discountAmount = (float) ($sale->discount_amount ?? 0);
            $taxBase = max(0, (float) $sale->total - $discountAmount);
            $taxAmount = round($taxBase * $taxRate, 2);
            $grandTotal = $taxBase + $taxAmount;
        @endphp

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <section class="panel-section">
                    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div class="section-title">Katalog Menu</div>
                            <p class="muted-copy mt-1">Klik kartu menu untuk menambah item ke keranjang aktif.</p>
                        </div>
                        <div class="w-full lg:max-w-sm">
                            <input
                                type="text"
                                id="product-search"
                                placeholder="Cari menu..."
                                class="dashboard-input"
                            >
                        </div>
                    </div>

                    @if ($products->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                            Menu tidak ditemukan.
                        </div>
                    @else
                        <div class="max-h-[760px] overflow-y-auto pr-1">
                            <div id="product-grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                @foreach ($products as $p)
                                    <form
                                        method="POST"
                                        action="{{ route('cashier.pos.add') }}"
                                        class="product-card flex h-full flex-col overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-[0_10px_28px_rgba(15,31,82,0.06)] transition duration-200 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-[0_18px_34px_rgba(37,87,190,0.12)]"
                                        data-name="{{ Str::lower($p->name) }}"
                                        data-warning="{{ $p->stock_warning ?? '' }}"
                                        data-margin-warning="{{ $p->low_margin_warning ?? '' }}"
                                        data-price="{{ (float) $p->price_default }}"
                                        data-estimated-cost="{{ (float) ($p->estimated_cost ?? 0) }}"
                                    >
                                        @csrf
                                        <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                        <input type="hidden" name="product_id" value="{{ $p->id }}">
                                        <input type="hidden" name="table_no" value="">
                                        <input type="hidden" name="customer_name" value="">

                                        <div class="relative h-36 overflow-hidden bg-slate-100">
                                            @if ($p->image_path)
                                                <img
                                                    src="{{ asset('storage/' . $p->image_path) }}"
                                                    alt="{{ $p->name }}"
                                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                                >
                                            @else
                                                <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_top_left,rgba(37,87,190,0.18),transparent_50%),linear-gradient(180deg,#eff4ff,#f8fbff)] text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                                    No Image
                                                </div>
                                            @endif
                                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/55 to-transparent px-3 py-2">
                                                <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/80">Menu aktif</div>
                                            </div>
                                        </div>

                                        <div class="flex flex-1 flex-col p-4">
                                            <div class="min-h-[48px] text-[14px] font-semibold leading-5 text-slate-900">
                                                {{ $p->name }}
                                            </div>

                                            @if (!empty($p->stock_warning))
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                                                        {{ $p->stock_warning }}
                                                    </span>
                                                </div>
                                            @endif

                                            <div class="mt-4 flex items-end justify-between gap-3">
                                                <div>
                                                    <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Harga jual</div>
                                                    <div class="mt-1 text-lg font-semibold tracking-tight text-slate-900">
                                                        Rp {{ number_format($p->price_default, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4 flex items-end gap-2">
                                                <div class="min-w-0 flex-none">
                                                    <label class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                                        Qty
                                                    </label>
                                                    <input
                                                        type="number"
                                                        name="qty"
                                                        value="1"
                                                        min="1"
                                                        step="1"
                                                        class="dashboard-input w-[68px] py-1 text-center text-sm"
                                                    >
                                                </div>
                                                <button
                                                    type="submit"
                                                    class="btn-primary flex-1 px-2.5 py-1.5 text-sm"
                                                >
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

                @if (!empty($openSales) && $openSales->count() > 0)
                    <section class="table-shell">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <div class="section-title text-lg">Open Bills</div>
                                <p class="muted-copy mt-1">Transaksi belum dibayar, siap dibuka kembali untuk finalisasi.</p>
                            </div>
                            <form method="GET" action="{{ route('cashier.pos') }}" class="w-full lg:max-w-sm">
                                <input
                                    type="text"
                                    name="open_q"
                                    value="{{ $openQuery ?? '' }}"
                                    placeholder="Cari nama tamu / meja / ID"
                                    class="dashboard-input"
                                >
                            </form>
                        </div>

                        <div class="max-h-[320px] overflow-auto">
                            <table class="min-w-full text-sm">
                                <thead class="table-head sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Nama / Meja</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                        @if(auth()->user()?->hasRole('admin'))
                                            <th class="px-4 py-3 text-left">Kasir</th>
                                        @endif
                                        <th class="px-4 py-3 text-right">Subtotal</th>
                                        <th class="px-4 py-3 text-left">Update</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($openSales as $os)
                                        <tr class="hover:bg-slate-50/80">
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-slate-800">{{ $os->customer_name ?? '-' }}</div>
                                                <div class="text-xs text-slate-500">Meja: {{ $os->table_no ?? '-' }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                                                    OPEN #{{ $os->id }}
                                                </span>
                                            </td>
                                            @if(auth()->user()?->hasRole('admin'))
                                                <td class="px-4 py-3 text-slate-500">{{ optional($os->cashier)->name ?? '-' }}</td>
                                            @endif
                                            <td class="px-4 py-3 text-right font-medium text-slate-700">
                                                Rp {{ number_format($os->total, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-500">
                                                {{ optional($os->updated_at)->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a
                                                        href="{{ route('cashier.pos', ['sale_id' => $os->id]) }}"
                                                        class="btn-secondary px-3 py-2 text-xs"
                                                    >
                                                        Buka
                                                    </a>
                                                    <form method="POST" action="{{ route('cashier.pos.cancel') }}" onsubmit="return confirm('Batalkan transaksi ini?');">
                                                        @csrf
                                                        <input type="hidden" name="sale_id" value="{{ $os->id }}">
                                                        <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">
                                                            Batalkan
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()?->hasRole('admin') ? 6 : 5 }}" class="px-4 py-5 text-center text-sm text-slate-500">
                                                Tidak ada transaksi open.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="xl:col-span-4">
                <div class="panel-card sticky top-5 overflow-hidden">
                    <div class="border-b border-slate-200 bg-[radial-gradient(circle_at_top_left,rgba(37,87,190,0.12),transparent_48%),linear-gradient(180deg,#ffffff,#f8fbff)] px-5 py-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="section-title text-xl">Keranjang</div>
                                <p class="muted-copy mt-1">Transaksi #{{ $sale->id }} • Status {{ $sale->status }}</p>
                            </div>
                            <span class="dashboard-badge">{{ $sale->lines->count() }} item</span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($sale->lines->count() > 0)
                                <form method="POST" action="{{ route('cashier.pos.clear') }}" onsubmit="return confirm('Kosongkan semua item di keranjang?');">
                                    @csrf
                                    <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                    <button type="submit" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                        Kosongkan
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('cashier.pos.cancel') }}" onsubmit="return confirm('Batalkan transaksi ini?');">
                                @csrf
                                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">
                                    Batalkan
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="space-y-5 p-5">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">No. Meja</label>
                                <input
                                    type="text"
                                    name="table_no"
                                    form="pos-payment-form"
                                    value="{{ $useOld ? old('table_no') : ($sale->table_no ?? '') }}"
                                    class="dashboard-input"
                                >
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nama Tamu</label>
                                <input
                                    type="text"
                                    name="customer_name"
                                    form="pos-payment-form"
                                    value="{{ $useOld ? old('customer_name') : ($sale->customer_name ?? '') }}"
                                    class="dashboard-input"
                                >
                            </div>
                        </div>

                        <p class="text-xs leading-5 text-slate-500">
                            Isi meja dan nama tamu agar transaksi open bill lebih mudah dicari saat pembayaran akhir.
                        </p>

                        <div class="table-shell shadow-none">
                            <div class="max-h-[320px] overflow-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="table-head sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-left">Menu</th>
                                            <th class="px-4 py-3 text-right">Qty</th>
                                            <th class="px-4 py-3 text-right">Harga</th>
                                            <th class="px-4 py-3 text-right">Subtotal</th>
                                            <th class="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($sale->lines as $l)
                                            <tr class="hover:bg-slate-50/80">
                                                <td class="px-4 py-3">
                                                    <div class="font-medium text-slate-800">{{ $l->product->name }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <form method="POST" action="{{ route('cashier.pos.line.update', $l) }}" class="flex items-center justify-end gap-2">
                                                        @csrf
                                                        <input
                                                            type="number"
                                                            name="qty"
                                                            value="{{ $l->qty }}"
                                                            min="1"
                                                            step="1"
                                                            class="dashboard-input w-16 py-2 text-center"
                                                        >
                                                        <button type="submit" class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                                            Update
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="px-4 py-3 text-right text-slate-600">{{ number_format($l->price, 0, ',', '.') }}</td>
                                                <td class="px-4 py-3 text-right font-medium text-slate-800">{{ number_format($l->qty * $l->price, 0, ',', '.') }}</td>
                                                <td class="px-4 py-3 text-right">
                                                    <form method="POST" action="{{ route('cashier.pos.line.delete', $l) }}" onsubmit="return confirm('Hapus item ini?');">
                                                        @csrf
                                                        <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                                                    Belum ada item di keranjang.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 text-sm text-slate-600"
                            data-summary
                            data-subtotal="{{ (float) $sale->total }}"
                            data-tax-rate="{{ (float) $taxRate }}"
                        >
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span>Subtotal</span>
                                    <span class="font-semibold text-slate-800">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Diskon</span>
                                    <span class="font-semibold text-slate-800" data-discount>Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                                </div>
                                @if ($taxRate > 0)
                                    <div class="flex items-center justify-between" data-tax-row>
                                        <span>Pajak ({{ (int) ($taxRate * 100) }}%)</span>
                                        <span class="font-semibold text-slate-800" data-tax>Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3 border-t border-slate-200 pt-3">
                                <div class="flex items-center justify-between text-base">
                                    <span class="font-semibold text-slate-700">Total</span>
                                    <span class="text-xl font-semibold tracking-tight text-slate-900" data-total>
                                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <form id="pos-payment-form" method="POST" action="{{ route('cashier.pos.pay') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">

                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Diskon (Rp)</label>
                                <input
                                    type="number"
                                    name="discount_amount"
                                    value="{{ $useOld ? old('discount_amount', 0) : 0 }}"
                                    min="0"
                                    step="100"
                                    class="dashboard-input"
                                >
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Uang Dibayar (Rp)</label>
                                <input
                                    type="number"
                                    name="paid_amount"
                                    value="{{ $useOld ? old('paid_amount', 0) : 0 }}"
                                    min="0"
                                    step="1000"
                                    class="dashboard-input"
                                >
                            </div>

                            <div class="flex items-center justify-between rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm">
                                <span class="font-medium text-blue-700">Kembalian</span>
                                <span class="text-lg font-semibold tracking-tight text-blue-900" id="change-display">Rp 0</span>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Metode Bayar</label>
                                <select name="payment_method" class="dashboard-input">
                                    <option value="CASH">CASH</option>
                                    <option value="QRIS">QRIS</option>
                                    <option value="DEBIT">DEBIT</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                <button type="submit" class="w-full rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                    Bayar
                                </button>

                                <button
                                    type="submit"
                                    formnovalidate
                                    formaction="{{ route('cashier.pos.hold') }}"
                                    class="w-full rounded-2xl bg-amber-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-600"
                                >
                                    Simpan & Tahan (Bayar Belakangan)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>

<div id="pos-low-margin-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/45 px-4">
    <div class="w-full max-w-md rounded-[28px] border border-rose-200 bg-white p-6 shadow-[0_24px_60px_rgba(15,23,42,0.35)]">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-lg font-bold text-rose-600">
                !
            </div>
            <div class="min-w-0">
                <div class="text-lg font-semibold text-rose-700">Peringatan Harga Modal</div>
                <div class="mt-2 text-sm leading-6 text-slate-700" id="pos-low-margin-modal-message"></div>
            </div>
        </div>
        <div class="mt-5 flex justify-end">
            <button
                type="button"
                id="pos-low-margin-modal-ok"
                class="rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700"
            >
                OK, lanjutkan
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('product-search');
        const cards = document.querySelectorAll('.product-card');
        const forms = document.querySelectorAll('.product-card');
        const discountInput = document.querySelector('input[name="discount_amount"]');
        const summaryEl = document.querySelector('[data-summary]');
        const lowMarginModal = document.getElementById('pos-low-margin-modal');
        const lowMarginModalMessage = document.getElementById('pos-low-margin-modal-message');
        const lowMarginModalOk = document.getElementById('pos-low-margin-modal-ok');
        let pendingLowMarginForm = null;

        const formatRp = (n) => new Intl.NumberFormat('id-ID').format(n);

        const showLowMarginModal = (form, message) => {
            if (!lowMarginModal || !lowMarginModalMessage) return;

            pendingLowMarginForm = form;
            lowMarginModalMessage.textContent = message;
            lowMarginModal.classList.remove('hidden');
            lowMarginModal.classList.add('flex');
        };

        const closeLowMarginModal = () => {
            if (!lowMarginModal) return;

            lowMarginModal.classList.add('hidden');
            lowMarginModal.classList.remove('flex');
        };

        if (lowMarginModalOk) {
            lowMarginModalOk.addEventListener('click', function () {
                if (!pendingLowMarginForm) {
                    closeLowMarginModal();
                    return;
                }

                const form = pendingLowMarginForm;
                pendingLowMarginForm = null;
                form.dataset.lowMarginWarningShown = '1';
                closeLowMarginModal();
                window.setTimeout(() => form.requestSubmit(), 60);
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const q = this.value.toLowerCase();

                cards.forEach(card => {
                    const name = card.dataset.name || '';
                    card.style.display = name.includes(q) ? '' : 'none';
                });
            });
        }

        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                const marginWarning = (this.dataset.marginWarning || '').trim();
                if (marginWarning && this.dataset.lowMarginWarningShown !== '1') {
                    e.preventDefault();

                    const price = parseFloat(this.dataset.price || '0');
                    const estimatedCost = parseFloat(this.dataset.estimatedCost || '0');
                    const message = marginWarning + ' Harga jual Rp ' + formatRp(price) + ' • estimasi modal Rp ' + formatRp(estimatedCost) + '. Item tetap akan ditambahkan ke keranjang.';

                    showLowMarginModal(this, message);
                    return;
                }

                this.dataset.lowMarginWarningShown = '0';

                const warning = (this.dataset.warning || '').trim();
                if (!warning) return;

                const ok = window.confirm('Perhatian: ' + warning + '.\nTetap tambahkan ke keranjang?');
                if (!ok) {
                    e.preventDefault();
                }
            });
        });

        forms.forEach(form => {
            form.addEventListener('submit', function () {
                const tableInput = document.querySelector('input[name="table_no"][form="pos-payment-form"]');
                const nameInput = document.querySelector('input[name="customer_name"][form="pos-payment-form"]');
                const tableHidden = form.querySelector('input[name="table_no"]');
                const nameHidden = form.querySelector('input[name="customer_name"]');

                if (tableHidden && tableInput) {
                    tableHidden.value = tableInput.value || '';
                }
                if (nameHidden && nameInput) {
                    nameHidden.value = nameInput.value || '';
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
