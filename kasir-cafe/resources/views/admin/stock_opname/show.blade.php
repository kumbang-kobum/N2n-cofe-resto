@extends('layouts.dashboard')

@section('content')
@php
  $missingExpired = $opname->lines
    ->where('diff_qty_base', '>', 0)
    ->whereNull('expired_at')
    ->count();

  $canPost = $opname->status === 'DRAFT' && $missingExpired === 0;
@endphp

{{-- Flash messages --}}
@if(session('status'))
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded">
    {{ session('status') }}
  </div>
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
    Ada <b>{{ $missingExpired }}</b> item selisih <b>plus</b> yang belum diisi expired.
    Silakan klik <b>Edit</b> terlebih dahulu.
  </div>
@endif

{{-- Header --}}
<div class="flex flex-wrap items-start justify-between gap-3 mb-4">
  <div>
    <h1 class="text-xl font-semibold">Detail Stock Opname</h1>
    <div class="text-sm text-gray-600 mt-1">
      <span class="font-medium">{{ $opname->code }}</span> •
      {{ \Carbon\Carbon::parse($opname->counted_at)->format('d M Y') }} •
      Status: <span class="font-medium">{{ $opname->status }}</span>
    </div>

    @if($opname->note)
      <div class="mt-2 text-sm bg-white border rounded p-3">
        {{ $opname->note }}
      </div>
    @endif

    @if($opname->status === 'CANCELLED')
      <div class="mt-2 text-sm bg-red-50 border border-red-200 rounded p-3">
        <div class="font-medium">Opname dibatalkan</div>
        @if($opname->cancel_reason)
          <div class="text-gray-700 mt-1">Alasan: {{ $opname->cancel_reason }}</div>
        @endif
        @if($opname->cancelled_at)
          <div class="text-gray-600 mt-1">Waktu: {{ \Carbon\Carbon::parse($opname->cancelled_at)->format('d M Y H:i') }}</div>
        @endif
      </div>
    @endif
  </div>

  {{-- Actions --}}
  <div class="flex flex-wrap gap-2 items-center">
    <a href="{{ route('admin.stock_opname.index') }}" class="px-3 py-2 rounded border text-sm">
      Kembali
    </a>

    <a href="{{ route('admin.stock_opname.pdf', $opname->id) }}" class="px-3 py-2 rounded border text-sm">
      Print PDF
    </a>

    @if($opname->status === 'DRAFT')
      <a href="{{ route('admin.stock_opname.edit', $opname->id) }}" class="px-3 py-2 rounded border text-sm">
        Edit Expired/Cost
      </a>

      <form method="POST" action="{{ route('admin.stock_opname.post', $opname->id) }}">
        @csrf
        <button
          type="submit"
          @disabled(!$canPost)
          class="px-3 py-2 rounded text-white text-sm {{ $canPost ? 'bg-green-600' : 'bg-gray-400 cursor-not-allowed' }}"
          @if($canPost) onclick="return confirm('POST opname? Ini akan mengubah stok dan tidak bisa diulang.')" @endif
        >
          POST Opname
        </button>
      </form>

      <form method="POST" action="{{ route('admin.stock_opname.cancel', $opname->id) }}" class="flex gap-2 items-center">
        @csrf
        <input
          name="reason"
          class="border rounded p-2 text-sm"
          placeholder="Alasan cancel (opsional)"
        >
        <button
          type="submit"
          class="px-3 py-2 rounded bg-red-600 text-white text-sm"
          onclick="return confirm('Batalkan opname? Dokumen tidak bisa diposting setelah dibatalkan.')"
        >
          Cancel
        </button>
      </form>
    @endif
  </div>
</div>

{{-- Table --}}
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

{{-- Audit log --}}
<div class="mt-6 bg-white border rounded-lg p-4">
  <h2 class="font-semibold mb-2">Audit Log</h2>
  <div class="text-sm">
    @forelse($opname->audits as $a)
      <div class="border-t py-2">
        <div class="font-medium">{{ $a->action }}</div>
        <div class="text-gray-600">
          {{ $a->created_at->format('d M Y H:i') }} • Actor: {{ $a->actor_id ?? '-' }}
        </div>
        @if($a->meta)
          <pre class="text-xs bg-gray-50 p-2 rounded mt-1 overflow-x-auto">{{ json_encode($a->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
        @endif
      </div>
    @empty
      <div class="text-gray-600">Belum ada audit.</div>
    @endforelse
  </div>
</div>
@endsection