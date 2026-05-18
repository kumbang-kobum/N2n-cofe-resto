@extends('layouts.dashboard')

@section('content')
@php
    $storeRoute = request()->routeIs('manager.*')
        ? 'manager.refunds.store'
        : (request()->routeIs('cashier.*') ? 'cashier.refunds.store' : 'admin.refunds.store');
    $backRoute = request()->routeIs('manager.*')
        ? 'manager.reports.sales'
        : (request()->routeIs('cashier.*') ? 'cashier.reports.sales' : 'admin.reports.sales');
@endphp

<div class="mb-4">
    <h1 class="text-xl font-semibold">Ajukan Permintaan Refund</h1>
    <p class="text-sm text-gray-600">Permintaan akan diteruskan ke manager/admin untuk disetujui sebelum diproses.</p>
</div>

<div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-4 text-sm text-amber-800">
    <strong>Perhatian:</strong> Refund tidak langsung diproses. Manager atau admin perlu menyetujui permintaan ini terlebih dahulu.
</div>

<div class="bg-white border rounded-lg p-4 mb-4 text-sm text-gray-600 space-y-1">
    <div>Nota: <strong>#{{ $sale->receipt_no ?? $sale->id }}</strong></div>
    <div>Total Transaksi: <strong>Rp {{ number_format($sale->grand_total ?? $sale->total, 0, ',', '.') }}</strong>
         &nbsp;|&nbsp; Metode: <strong>{{ strtoupper($sale->payment_method ?? '-') }}</strong></div>
    @if($sale->refund_total > 0)
        <div>Sudah direfund: <strong>Rp {{ number_format($sale->refund_total, 0, ',', '.') }}</strong></div>
    @endif
</div>

<div class="bg-white border rounded-lg p-4">
    <form method="POST" action="{{ route($storeRoute, $sale) }}">
        @csrf

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-2 border-b">Menu</th>
                        <th class="text-right p-2 border-b">Qty Beli</th>
                        <th class="text-right p-2 border-b">Sudah Direfund</th>
                        <th class="text-right p-2 border-b">Maks. Refund</th>
                        <th class="text-right p-2 border-b">Qty Refund</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->lines as $line)
                        @php
                            $refunded  = (float) $line->refundLines->sum('qty');
                            $maxRefund = max(0, (float) $line->qty - $refunded);
                        @endphp
                        <tr class="border-b {{ $maxRefund <= 0 ? 'opacity-40' : '' }}">
                            <td class="p-2 font-medium">{{ $line->product->name }}</td>
                            <td class="p-2 text-right">{{ $line->qty }}</td>
                            <td class="p-2 text-right">{{ $refunded > 0 ? $refunded : '—' }}</td>
                            <td class="p-2 text-right font-semibold {{ $maxRefund <= 0 ? 'text-gray-400' : 'text-slate-700' }}">{{ $maxRefund }}</td>
                            <td class="p-2 text-right">
                                @if($maxRefund > 0)
                                    <input type="number"
                                           name="lines[{{ $line->id }}][qty]"
                                           min="0"
                                           max="{{ $maxRefund }}"
                                           step="0.001"
                                           placeholder="0"
                                           class="w-24 rounded border border-gray-300 px-2 py-1 text-xs text-right">
                                @else
                                    <span class="text-xs text-gray-400">Lunas</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan / Catatan <span class="text-gray-400 text-xs">(opsional)</span></label>
            <input type="text" name="note" value="{{ old('note') }}"
                   placeholder="Contoh: Pelanggan menerima pesanan salah..."
                   class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route($backRoute) }}" class="px-3 py-2 rounded border text-sm">Batal</a>
            <button type="submit"
                    class="px-4 py-2 rounded bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600">
                Ajukan Permintaan Refund
            </button>
        </div>
    </form>
</div>
@endsection
