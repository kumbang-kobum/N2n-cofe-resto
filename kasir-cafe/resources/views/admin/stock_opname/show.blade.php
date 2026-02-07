@extends('layouts.dashboard')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
  <div>
    <h1 class="text-xl font-semibold">Detail Stock Opname</h1>
    <div class="text-sm text-gray-600">
      <span class="font-medium">{{ $opname->code }}</span> •
      {{ \Carbon\Carbon::parse($opname->counted_at)->format('d M Y') }} •
      Status: <span class="font-medium">{{ $opname->status }}</span>
    </div>
  </div>

  <div class="flex gap-2">
    <a href="{{ route('admin.stock_opname.index') }}" class="px-3 py-2 rounded border text-sm">Kembali</a>

    @if($opname->status === 'DRAFT')
      <form method="POST" action="{{ route('admin.stock_opname.post', $opname->id) }}">
        @csrf
        <button onclick="return confirm('POST opname? Ini akan mengubah stok dan tidak bisa diulang.')" class="px-3 py-2 rounded bg-green-600 text-white text-sm">
          POST Opname
        </button>
      </form>
    @endif
  </div>
</div>

@if(session('status'))
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded">{{ session('status') }}</div>
@endif

@if($opname->note)
  <div class="mb-4 p-3 bg-white border rounded">{{ $opname->note }}</div>
@endif

<div class="bg-white border rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-2">Bahan</th>
          <th class="text-right p-2">Sistem (base)</th>
          <th class="text-right p-2">Fisik (base)</th>
          <th class="text-right p-2">Selisih</th>
          <th class="text-left p-2">Expired (jika +)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($opname->lines as $l)
          <tr class="border-t">
            <td class="p-2">{{ $l->item->name }}</td>
            <td class="p-2 text-right">{{ number_format($l->system_qty_base, 3, ',', '.') }}</td>
            <td class="p-2 text-right">{{ number_format($l->physical_qty_base, 3, ',', '.') }}</td>
            <td class="p-2 text-right {{ $l->diff_qty_base < 0 ? 'text-red-600' : ($l->diff_qty_base > 0 ? 'text-green-600' : '') }}">
              {{ number_format($l->diff_qty_base, 3, ',', '.') }}
            </td>
            <td class="p-2">
              {{ $l->expired_at ? \Carbon\Carbon::parse($l->expired_at)->format('d M Y') : '-' }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection