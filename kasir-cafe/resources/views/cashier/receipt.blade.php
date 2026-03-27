@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Nota</h1>
    <div class="flex items-center gap-2">
        <a href="{{ route('cashier.pos') }}"
           class="px-3 py-2 rounded-md border text-sm hover:bg-gray-50">
            Kembali
        </a>
        <button onclick="window.print()"
                class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
            Cetak 3 Rangkap
        </button>
    </div>
</div>

@php
    $settings = \App\Models\Setting::first();
    $drinkLines = $sale->lines->filter(fn ($line) => $line->product?->menu_type === \App\Models\Product::TYPE_DRINK)->values();
    $foodLines = $sale->lines->filter(fn ($line) => $line->product?->menu_type !== \App\Models\Product::TYPE_DRINK)->values();
@endphp

<div class="space-y-4">
    <div class="rounded-lg border bg-blue-50/70 p-3 text-sm text-slate-700">
        Nota sekarang dicetak dalam 3 lembar:
        <span class="font-semibold">nota utama</span>,
        <span class="font-semibold">slip minuman</span>,
        dan
        <span class="font-semibold">slip makanan</span>.
    </div>

    <div class="bg-white border rounded-lg p-4">
        <div id="receipt-stack">
            <div class="receipt-copy">
                <div class="receipt-80mm">
                    @include('cashier.partials.receipt-full', ['sale' => $sale, 'settings' => $settings])
                </div>
            </div>

            <div class="receipt-copy">
                <div class="receipt-80mm">
                    @include('cashier.partials.receipt-category', [
                        'sale' => $sale,
                        'settings' => $settings,
                        'title' => 'Slip Minuman',
                        'lines' => $drinkLines,
                        'emptyMessage' => 'Tidak ada item minuman pada transaksi ini.',
                    ])
                </div>
            </div>

            <div class="receipt-copy">
                <div class="receipt-80mm">
                    @include('cashier.partials.receipt-category', [
                        'sale' => $sale,
                        'settings' => $settings,
                        'title' => 'Slip Makanan',
                        'lines' => $foodLines,
                        'emptyMessage' => 'Tidak ada item makanan pada transaksi ini.',
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @page {
        size: 80mm auto;
        margin: 0;
    }

    @media print {
        html, body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body * { visibility: hidden; }
        #receipt-stack, #receipt-stack * { visibility: visible; }
        #receipt-stack {
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm;
        }
        .receipt-copy {
            page-break-after: always;
            break-after: page;
        }
        .receipt-copy:last-child {
            page-break-after: auto;
            break-after: auto;
        }
    }

    #receipt-stack {
        display: grid;
        gap: 16px;
        justify-content: start;
    }
    .receipt-copy {
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        background: #fff;
    }
    .receipt-80mm {
        width: 80mm;
        max-width: 80mm;
        box-sizing: border-box;
        padding: 2mm 2.5mm 1.5mm 2.5mm;
        font-family: "Courier New", Consolas, monospace;
        font-size: 11px;
        line-height: 1.2;
        letter-spacing: 0;
        font-weight: 700;
    }
    .receipt-80mm table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .receipt-80mm th,
    .receipt-80mm td {
        padding: 1px 0;
        vertical-align: top;
        word-wrap: break-word;
        overflow-wrap: anywhere;
    }
    .receipt-mini-table th:nth-child(2),
    .receipt-mini-table td:nth-child(2),
    .receipt-mini-table td:nth-child(2) {
        text-align: right;
    }
    .receipt-table th:nth-child(2),
    .receipt-table th:nth-child(3),
    .receipt-table th:nth-child(4),
    .receipt-table td:nth-child(2),
    .receipt-table td:nth-child(3),
    .receipt-table td:nth-child(4) {
        text-align: right;
    }
</style>
@endpush
@endsection
