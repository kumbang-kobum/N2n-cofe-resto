<div class="text-center">
    @if (!empty($settings?->logo_path))
        <div class="flex justify-center mb-1">
            <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-10 object-contain">
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

<div class="mt-1 text-[11px] leading-tight">
    <div>No. Nota: {{ $sale->receipt_no ?? ('#' . $sale->id) }}</div>
    <div>Tanggal: {{ optional($sale->paid_at)->format('d/m/Y H:i') }}</div>
    @if($sale->table_no || $sale->customer_name)
        <div>Meja: {{ $sale->table_no ?? '-' }} | Tamu: {{ $sale->customer_name ?? '-' }}</div>
    @endif
    <div>Kasir: {{ optional($sale->cashier)->name ?? '-' }}</div>
    <div>Metode: {{ strtoupper($sale->payment_method ?? '-') }}</div>
</div>

<div class="my-1 border-t border-dashed"></div>

<table class="w-full text-[11px] leading-tight receipt-table">
    <colgroup>
        <col style="width: 44%">
        <col style="width: 14%">
        <col style="width: 20%">
        <col style="width: 22%">
    </colgroup>
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
                <td class="pr-1 break-words">{{ $l->product->name }}</td>
                <td class="text-right">{{ rtrim(rtrim(number_format((float) $l->qty, 3, '.', ''), '0'), '.') }}</td>
                <td class="text-right">{{ number_format($l->price, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($l->qty * $l->price, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="my-1 border-t border-dashed"></div>

@php
    $taxRate = (float) ($sale->tax_rate ?? config('pos.tax_rate', 0.10));
    $discount = (float) ($sale->discount_amount ?? 0);
    $taxBase = max(0, (float) $sale->total - $discount);
    $taxAmount = (float) ($sale->tax_amount ?? 0);
    $grand = (float) ($sale->grand_total ?? ($taxBase + $taxAmount));
@endphp

<div class="text-[11px] space-y-0.5 leading-tight">
    <div class="flex justify-between">
        <span>Subtotal</span>
        <span>{{ number_format($sale->total, 0, ',', '.') }}</span>
    </div>
    <div class="flex justify-between">
        <span>Diskon</span>
        <span>{{ number_format($discount, 0, ',', '.') }}</span>
    </div>
    @if ($taxRate > 0)
        <div class="flex justify-between">
            <span>Pajak ({{ (int) ($taxRate * 100) }}%)</span>
            <span>{{ number_format($taxAmount, 0, ',', '.') }}</span>
        </div>
    @endif
    <div class="flex justify-between font-semibold">
        <span>Total</span>
        <span>{{ number_format($grand, 0, ',', '.') }}</span>
    </div>
    <div class="flex justify-between">
        <span>Dibayar</span>
        <span>{{ number_format($sale->paid_amount ?? 0, 0, ',', '.') }}</span>
    </div>
    <div class="flex justify-between">
        <span>Kembalian</span>
        <span>{{ number_format($sale->change_amount ?? 0, 0, ',', '.') }}</span>
    </div>
</div>

<div class="my-1 border-t border-dashed"></div>
<div class="text-center text-[11px] text-gray-600">
    Terima kasih
</div>
