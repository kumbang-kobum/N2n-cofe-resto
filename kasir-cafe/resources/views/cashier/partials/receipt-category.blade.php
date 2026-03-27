<div class="text-center">
    <div class="font-semibold">
        {{ $settings->restaurant_name ?? 'N2N Cafe' }}
    </div>
    <div class="text-[11px] mt-1">{{ $title }}</div>
</div>

<div class="mt-1 text-[11px] leading-tight">
    <div>No. Nota: {{ $sale->receipt_no ?? ('#' . $sale->id) }}</div>
    <div>Tanggal: {{ optional($sale->paid_at)->format('d/m/Y H:i') }}</div>
    @if($sale->table_no || $sale->customer_name)
        <div>Meja: {{ $sale->table_no ?? '-' }} | Tamu: {{ $sale->customer_name ?? '-' }}</div>
    @endif
</div>

<div class="my-1 border-t border-dashed"></div>

@if($lines->isEmpty())
    <div class="py-3 text-center text-[11px] text-gray-500">
        {{ $emptyMessage }}
    </div>
@else
    <table class="w-full text-[11px] leading-tight receipt-mini-table">
        <colgroup>
            <col style="width: 76%">
            <col style="width: 24%">
        </colgroup>
        <thead>
            <tr>
                <th class="text-left">Item</th>
                <th class="text-right">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr>
                    <td class="pr-1 break-words">{{ $line->product->name }}</td>
                    <td class="text-right">{{ rtrim(rtrim(number_format((float) $line->qty, 3, '.', ''), '0'), '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="my-1 border-t border-dashed"></div>
<div class="text-center text-[11px] text-gray-600">
    Bagian {{ strtolower($title) }}
</div>
