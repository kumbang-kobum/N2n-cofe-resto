@extends('layouts.dashboard')

@section('content')
@php
    $missingExpired = $opname->lines
        ->where('diff_qty_base', '>', 0)
        ->whereNull('expired_at')
        ->count();

    $canPost = $opname->status === 'DRAFT' && $missingExpired === 0;
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 class="text-xl font-semibold">Detail Stock Opname</h1>
        <div class="text-sm text-gray-700">
            <span class="font-medium">{{ $opname->code }}</span>
            • {{ \Carbon\Carbon::parse($opname->counted_at)->format('d M Y') }}
            • Status:
            <span class="font-semibold">
                {{ $opname->status }}
            </span>
        </div>

        <div class="text-xs text-gray-600 mt-1 space-y-0.5">
            <div>
                Dibuat oleh:
                {{ optional($opname->creator)->name ?? 'User #'.$opname->created_by }}
            </div>

            @if($opname->status === 'POSTED' && $opname->posted_at)
                <div>
                    Diposting:
                    {{ \Carbon\Carbon::parse($opname->posted_at)->format('d M Y H:i') }}
                    oleh {{ optional($opname->poster)->name ?? 'User #'.$opname->posted_by }}
                </div>
            @endif

            @if($opname->status === 'CANCELLED' && $opname->cancelled_at)
                <div class="text-red-700">
                    Dibatalkan:
                    {{ \Carbon\Carbon::parse($opname->cancelled_at)->format('d M Y H:i') }}
                    @if($opname->cancelled_by)
                        oleh User #{{ $opname->cancelled_by }}
                    @endif
                    @if($opname->cancel_reason)
                        • Alasan: {{ $opname->cancel_reason }}
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.stock_opname.index') }}"
           class="px-3 py-2 text-sm border rounded hover:bg-gray-50">
            Kembali
        </a>

        <a href="{{ route('admin.stock_opname.pdf', $opname->id) }}"
           class="px-3 py-2 text-sm border rounded hover:bg-gray-50"
           target="_blank">
            Print PDF
        </a>

        @if($opname->status === 'DRAFT')
            <a href="{{ route('admin.stock_opname.edit', $opname->id) }}"
               class="px-3 py-2 text-sm border rounded hover:bg-gray-50">
                Edit
            </a>

            <form method="POST"
                  action="{{ route('admin.stock_opname.post', $opname->id) }}"
                  onsubmit="return {{ $canPost ? "confirm('POST opname? Ini akan mengubah stok dan tidak bisa diulang.')" : "false" }}"
            >
                @csrf
                <button
                    type="submit"
                    @disabled(!$canPost)
                    class="px-3 py-2 text-sm rounded text-white
                        {{ $canPost ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }}">
                    POST Opname
                </button>
            </form>

            <form method="POST"
                  action="{{ route('admin.stock_opname.cancel', $opname->id) }}"
                  class="flex items-center gap-1"
                  onsubmit="return confirm('Batalkan opname?')"
            >
                @csrf
                <input type="text"
                       name="reason"
                       class="border rounded px-2 py-1 text-xs"
                       placeholder="Alasan cancel (opsional)">
                <button type="submit"
                        class="px-3 py-2 text-sm rounded text-white bg-red-600 hover:bg-red-700">
                    Cancel
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('status'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-sm">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm">
        <ul class="list-disc pl-4 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($opname->status === 'DRAFT' && $missingExpired > 0)
    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
        Ada <b>{{ $missingExpired }}</b> item selisih <b>plus</b> yang belum diisi expired.
        Silakan klik <b>Edit</b> dulu sebelum POST.
    </div>
@endif

@if($opname->note)
    <div class="mb-4 p-3 bg-white border rounded text-sm">
        <div class="font-semibold mb-1">Catatan</div>
        <div>{{ $opname->note }}</div>
    </div>
@endif

<div class="bg-white border rounded-lg overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-2">Bahan</th>
                    <th class="text-left p-2">Satuan Input</th>
                    <th class="text-right p-2">Sistem (base)</th>
                    <th class="text-right p-2">Fisik (base)</th>
                    <th class="text-right p-2">Selisih (base)</th>
                    <th class="text-left p-2">Expired (jika +)</th>
                    <th class="text-right p-2">Cost Base</th>
                </tr>
            </thead>
            <tbody>
                @forelse($opname->lines as $l)
                    <tr class="border-t">
                        <td class="p-2">
                            <div class="font-medium">{{ $l->item->name }}</div>
                            <div class="text-xs text-gray-500">
                                Base: {{ $l->item->baseUnit->symbol ?? '-' }}
                            </div>
                        </td>
                        <td class="p-2">
                            <span class="text-xs text-gray-600">
                                {{ optional($l->inputUnit)->symbol ?? '-' }}
                            </span>
                        </td>
                        <td class="p-2 text-right">
                            {{ number_format($l->system_qty_base, 3, ',', '.') }}
                        </td>
                        <td class="p-2 text-right">
                            {{ number_format($l->physical_qty_base, 3, ',', '.') }}
                        </td>
                        <td class="p-2 text-right
                            {{ $l->diff_qty_base < 0 ? 'text-red-600' : ($l->diff_qty_base > 0 ? 'text-green-600' : '') }}">
                            {{ number_format($l->diff_qty_base, 3, ',', '.') }}
                        </td>
                        <td class="p-2">
                            @if($l->diff_qty_base > 0 && $l->expired_at)
                                {{ \Carbon\Carbon::parse($l->expired_at)->format('d M Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-2 text-right">
                            {{ number_format((float) $l->unit_cost_base, 3, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr class="border-t">
                        <td colspan="7" class="p-3 text-center text-gray-500">
                            Tidak ada line.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 bg-white border rounded-lg p-4">
    <h2 class="font-semibold mb-2 text-sm">Audit Log</h2>
    <div class="text-xs">
        @forelse($opname->audits as $a)
            <div class="border-t py-2">
                <div class="font-medium">{{ $a->action }}</div>
                <div class="text-gray-600">
                    {{ $a->created_at->format('d M Y H:i') }}
                    • Actor: {{ $a->actor_id ?? '-' }}
                </div>
                @if($a->meta)
                    <pre class="text-[11px] bg-gray-50 p-2 rounded mt-1 overflow-x-auto">
{{ json_encode($a->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}
                    </pre>
                @endif
            </div>
        @empty
            <div class="text-gray-500">Belum ada audit.</div>
        @endforelse
    </div>
</div>
@endsection