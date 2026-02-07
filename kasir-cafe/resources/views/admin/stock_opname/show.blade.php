@extends('layouts.dashboard')

@section('content')
@php
  $missingExpired = $opname->lines
    ->where('diff_qty_base', '>', 0)
    ->whereNull('expired_at')
    ->count();

  $canPost = $opname->status === 'DRAFT' && $missingExpired === 0;
@endphp

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
      <a href="{{ route('admin.stock_opname.edit', $opname->id) }}" class="px-3 py-2 rounded border text-sm">Edit</a>

      <form method="POST" action="{{ route('admin.stock_opname.post', $opname->id) }}">
        @csrf
        <button
          @disabled(!$canPost)
          class="px-3 py-2 rounded text-white text-sm {{ $canPost ? 'bg-green-600' : 'bg-gray-400 cursor-not-allowed' }}"
          onclick="return {{ $canPost ? "confirm('POST opname? Ini akan mengubah stok dan tidak bisa diulang.')" : "false" }}"
        >
          POST Opname
        </button>
      </form>
    @endif
  </div>
</div>

@if(session('status'))
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded">{{ session('status') }}</div>
@endif

@if ($errors->any())
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded">
    <ul class="list-disc pl-5 text-sm">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if($opname->status === 'DRAFT' && $missingExpired > 0)
  <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
    Ada <b>{{ $missingExpired }}</b> item selisih <b>plus</b> yang belum diisi expired. Silakan klik <b>Edit</b> dulu.
  </div>
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
          <th class="text-right p-2">Cost Base</th>
        </tr>
      </thead>
      <tbody>
        @forelse($opname->lines as $l)
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
            <td class="p-2 text-right">{{ number_format((float)$l->unit_cost_base, 3, ',', '.') }}</td>
          </tr>
        @empty
          <tr class="border-t">
            <td colspan="6" class="p-3 text-gray-600">Tidak ada line.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection