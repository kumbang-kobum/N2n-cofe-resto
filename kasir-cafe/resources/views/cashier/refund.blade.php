@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Refund Transaksi #{{ $sale->id }}</h1>

<div class="bg-white border rounded-lg p-4 mb-4">
  <div class="text-sm text-gray-600">
    Total: <b>Rp {{ number_format($sale->grand_total ?? $sale->total, 0, ',', '.') }}</b>
    | Refund saat ini: <b>Rp {{ number_format($sale->refund_total ?? 0, 0, ',', '.') }}</b>
  </div>
</div>

<div class="bg-white border rounded-lg p-4">
  <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.refunds.store' : (request()->routeIs('cashier.*') ? 'cashier.refunds.store' : 'admin.refunds.store'), $sale) }}">
    @csrf

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Menu</th>
            <th class="text-right p-2">Qty</th>
            <th class="text-right p-2">Sudah Refund</th>
            <th class="text-right p-2">Refund Qty</th>
          </tr>
        </thead>
        <tbody>
          @foreach($sale->lines as $line)
            @php
              $refunded = (float) $line->refundLines->sum('qty');
              $maxRefund = max(0, (float) $line->qty - $refunded);
            @endphp
            <tr class="border-t">
              <td class="p-2">{{ $line->product->name }}</td>
              <td class="p-2 text-right">{{ $line->qty }}</td>
              <td class="p-2 text-right">{{ $refunded }}</td>
              <td class="p-2 text-right">
                <input type="number" name="lines[{{ $line->id }}][qty]" min="0" max="{{ $maxRefund }}" step="0.001"
                       class="w-24 rounded border border-gray-300 px-2 py-1 text-xs text-right">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
      <input type="text" name="note" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
    </div>

    <div class="mt-4 flex items-center gap-2">
      <a href="{{ route(request()->routeIs('manager.*') ? 'manager.reports.sales' : (request()->routeIs('cashier.*') ? 'cashier.reports.sales' : 'admin.reports.sales')) }}"
         class="px-3 py-2 rounded border text-sm">Batal</a>
      <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
        Proses Refund
      </button>
    </div>
  </form>
</div>
@endsection
