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
            Cetak
        </button>
    </div>
</div>

<div class="bg-white border rounded-lg p-4">
    <div id="receipt" class="receipt-80mm">
        @php
            $settings = \App\Models\Setting::first();
        @endphp
        <div class="text-center">
            @if (!empty($settings?->logo_path))
                <div class="flex justify-center mb-1">
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-12 object-contain">
                </div>
            @endif
            <div class="font-semibold">
                {{ $settings->restaurant_name ?? 'N2N Cafe' }}
            </div>
            @if (!empty($settings?->restaurant_address))
                <div class="text-[10px] text-gray-600">{{ $settings->restaurant_address }}</div>
            @endif
            @if (!empty($settings?->restaurant_phone))
                <div class="text-[10px] text-gray-600">Telp: {{ $settings->restaurant_phone }}</div>
            @endif
            <div class="text-[11px] text-gray-600 mt-1">Struk Pembayaran</div>
        </div>

        <div class="mt-2 text-[11px]">
            <div>No. Nota: {{ $sale->receipt_no ?? ('#' . $sale->id) }}</div>
            <div>Tanggal: {{ optional($sale->paid_at)->format('d/m/Y H:i') }}</div>
            <div>Kasir: {{ optional($sale->cashier)->name ?? '-' }}</div>
            <div>Metode: {{ strtoupper($sale->payment_method ?? '-') }}</div>
        </div>

        <div class="my-2 border-t border-dashed"></div>

        <table class="w-full text-[11px]">
            <thead>
                <tr>
                    <th class="text-left">Item</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Sub</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->lines as $l)
                    <tr>
                        <td class="pr-1">{{ $l->product->name }}</td>
                        <td class="text-right">{{ $l->qty }}</td>
                        <td class="text-right">{{ number_format($l->price, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($l->qty * $l->price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="my-2 border-t border-dashed"></div>

@php
            $taxRate = (float) ($sale->tax_rate ?? config('pos.tax_rate', 0.10));
            $discount = (float) ($sale->discount_amount ?? 0);
            $taxBase = max(0, (float) $sale->total - $discount);
            $taxAmount = (float) ($sale->tax_amount ?? 0);
            $grand = (float) ($sale->grand_total ?? ($taxBase + $taxAmount));
        @endphp

        <div class="text-[11px] space-y-1">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>{{ number_format($sale->total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Diskon</span>
                <span>{{ number_format($discount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pajak ({{ (int) ($taxRate * 100) }}%)</span>
                <span>{{ number_format($taxAmount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-semibold">
                <span>Total</span>
                <span>{{ number_format($grand, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="my-2 border-t border-dashed"></div>
        <div class="text-center text-[11px] text-gray-600">
            Terima kasih
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body * { visibility: hidden; }
        #receipt, #receipt * { visibility: visible; }
        #receipt { position: absolute; left: 0; top: 0; }
    }

    .receipt-80mm {
        width: 80mm;
        font-family: "Courier New", Courier, monospace;
    }
    .receipt-80mm table {
        width: 100%;
        border-collapse: collapse;
    }
    .receipt-80mm th,
    .receipt-80mm td {
        padding: 2px 0;
    }
</style>
@endpush
@endsection
