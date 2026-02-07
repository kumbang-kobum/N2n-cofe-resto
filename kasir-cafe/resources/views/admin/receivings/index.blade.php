@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">Penerimaan Stok</h1>
  <a class="px-3 py-2 rounded bg-gray-900 text-white" href="{{ route('admin.receivings.create') }}">+ Terima Stok</a>
</div>

<div class="space-y-3">
  @foreach($purchases as $p)
    <div class="bg-white border rounded-lg p-4">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
          <div class="font-semibold">#{{ $p->id }} — {{ \Carbon\Carbon::parse($p->received_at)->format('d M Y H:i') }}</div>
          <div class="text-sm text-gray-600">{{ $p->supplier_name ?? '-' }}</div>
        </div>
        <div class="text-sm text-gray-600">{{ $p->lines->count() }} item</div>
      </div>

      <div class="mt-3 overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-2">Bahan</th>
              <th class="text-right p-2">Qty</th>
              <th class="text-right p-2">Cost</th>
              <th class="text-left p-2">Expired</th>
            </tr>
          </thead>
          <tbody>
            @foreach($p->lines as $l)
              <tr class="border-t">
                <td class="p-2">{{ $l->item->name }}</td>
                <td class="p-2 text-right">{{ $l->qty }}</td>
                <td class="p-2 text-right">{{ number_format($l->unit_cost,2,',','.') }}</td>
                <td class="p-2">{{ \Carbon\Carbon::parse($l->expired_at)->format('d M Y') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endforeach
</div>
@endsection