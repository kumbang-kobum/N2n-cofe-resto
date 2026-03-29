@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Penerimaan Stok</h1>

<div class="mb-4 flex items-center justify-between gap-2">
  <div class="text-sm text-gray-600">
    Riwayat penerimaan bahan, supplier, dan detail batch yang masuk.
  </div>
  <a href="{{ route('admin.receivings.create') }}"
     class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
    + Terima Stok
  </a>
</div>

<form method="GET" class="mb-4">
  <div class="flex items-center gap-2">
    <input type="text"
           name="q"
           value="{{ $q ?? '' }}"
           placeholder="Cari supplier, bahan, atau ID penerimaan..."
           class="w-full max-w-md rounded border px-3 py-2 text-sm">
    <button type="submit" class="rounded bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Cari</button>
    @if(!empty($q))
      <a href="{{ route('admin.receivings.index') }}" class="rounded border px-3 py-2 text-sm hover:bg-gray-50">Reset</a>
    @endif
  </div>
</form>

<div class="space-y-4">
  @forelse($purchases as $purchase)
    <div class="rounded-lg border bg-white">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-3">
        <div>
          <div class="font-semibold text-slate-800">#{{ $purchase->id }} - {{ \Carbon\Carbon::parse($purchase->received_at)->format('d M Y H:i') }}</div>
          <div class="text-sm text-gray-600">Supplier: {{ $purchase->supplier_name ?: '-' }}</div>
        </div>
        <div class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
          {{ $purchase->lines->count() }} item
        </div>
      </div>

      <div class="overflow-x-auto overflow-y-auto max-h-[50vh]">
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
              <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Bahan</th>
              <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Qty</th>
              <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Harga / Unit</th>
              <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Total</th>
              <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Expired</th>
            </tr>
          </thead>
          <tbody>
            @foreach($purchase->lines as $line)
              <tr class="border-t">
                <td class="px-3 py-2 font-medium text-slate-800">{{ $line->item->name }}</td>
                <td class="px-3 py-2">{{ rtrim(rtrim(number_format($line->qty, 6, ',', '.'), '0'), ',') }} {{ $line->unit?->symbol }}</td>
                <td class="px-3 py-2 text-right">Rp {{ number_format((float) $line->unit_cost, 2, ',', '.') }}</td>
                <td class="px-3 py-2 text-right">Rp {{ number_format((float) ($line->qty * $line->unit_cost), 2, ',', '.') }}</td>
                <td class="px-3 py-2">{{ \Carbon\Carbon::parse($line->expired_at)->format('d M Y') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @empty
    <div class="rounded-lg border bg-white px-4 py-8 text-center text-sm text-gray-500">
      Belum ada data penerimaan stok yang cocok dengan pencarian.
    </div>
  @endforelse
</div>
@endsection
