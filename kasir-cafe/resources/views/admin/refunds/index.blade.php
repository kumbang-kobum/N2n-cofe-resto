@extends('layouts.dashboard')

@section('content')
@php $routePrefix = request()->routeIs('manager.*') ? 'manager' : 'admin'; @endphp

<div class="mb-4">
    <h1 class="text-xl font-semibold">Antrian Permintaan Refund</h1>
    <p class="text-sm text-gray-600">Permintaan refund yang menunggu persetujuan Anda.</p>
</div>

@if (session('status'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
        {{ session('status') }}
    </div>
@endif

@if ($refunds->isEmpty())
    <div class="rounded-lg border bg-white p-10 text-center text-sm text-gray-500">
        Tidak ada permintaan refund yang menunggu persetujuan.
    </div>
@else
    <div class="space-y-4">
        @foreach ($refunds as $refund)
            @php $sale = $refund->sale; @endphp
            <div class="rounded-lg border bg-white overflow-hidden">
                {{-- Header --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b bg-slate-50 px-4 py-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">MENUNGGU</span>
                        <span class="text-sm font-semibold text-slate-800">
                            Nota #{{ $sale->receipt_no ?? $sale->id }}
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ optional($sale->paid_at)->translatedFormat('d M Y, H:i') }}
                        </span>
                        @if($sale->cashier)
                            <span class="text-xs text-gray-500">· Kasir: <strong>{{ $sale->cashier->name }}</strong></span>
                        @endif
                        @if($sale->table_no)
                            <span class="text-xs text-gray-500">· Meja {{ $sale->table_no }}</span>
                        @endif
                        @if($sale->customer_name)
                            <span class="text-xs text-gray-500">· {{ $sale->customer_name }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        Diajukan oleh <strong class="text-slate-700">{{ $refund->requestedBy?->name ?? '-' }}</strong>
                        · {{ $refund->created_at->translatedFormat('d M Y, H:i') }}
                    </div>
                </div>

                <div class="p-4 grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4">
                    {{-- Detail item yang di-refund --}}
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Item yang diminta refund</div>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500">
                                <tr>
                                    <th class="text-left px-2 py-1.5 border-b">Menu</th>
                                    <th class="text-right px-2 py-1.5 border-b">Qty Refund</th>
                                    <th class="text-right px-2 py-1.5 border-b">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($refund->lines as $line)
                                    <tr class="border-b">
                                        <td class="px-2 py-1.5">{{ $line->saleLine?->product?->name ?? '—' }}</td>
                                        <td class="px-2 py-1.5 text-right">{{ number_format((float) $line->qty, 2, ',', '.') }}</td>
                                        <td class="px-2 py-1.5 text-right">Rp {{ number_format((float) $line->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-semibold text-slate-800">
                                    <td class="px-2 py-2" colspan="2">Total Refund Diminta</td>
                                    <td class="px-2 py-2 text-right">Rp {{ number_format((float) $refund->total_refund, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        @if ($refund->note)
                            <div class="mt-2 rounded bg-blue-50 border border-blue-100 px-3 py-2 text-xs text-blue-800">
                                <span class="font-semibold">Catatan:</span> {{ $refund->note }}
                            </div>
                        @endif
                    </div>

                    {{-- Aksi --}}
                    @php $refundFormatted = number_format((float) $refund->total_refund, 0, ',', '.'); @endphp
                    <div class="flex flex-col gap-3 min-w-[220px]">
                        {{-- Setujui --}}
                        <form action="{{ route($routePrefix . '.refunds.approve', $refund) }}" method="POST"
                              onsubmit="return confirm('Setujui refund Rp {{ $refundFormatted }}? Stok akan dikembalikan.');">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                                Setujui Refund
                            </button>
                        </form>

                        {{-- Tolak --}}
                        <div x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                    class="w-full rounded border border-red-300 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                                Tolak
                            </button>
                            <div x-show="open" x-cloak class="mt-2">
                                <form action="{{ route($routePrefix . '.refunds.reject', $refund) }}" method="POST">
                                    @csrf
                                    <textarea name="rejection_note" rows="2" required
                                              placeholder="Alasan penolakan (wajib diisi)..."
                                              class="w-full rounded border border-gray-300 px-3 py-2 text-sm mb-2"></textarea>
                                    <button type="submit"
                                            class="w-full rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                        Konfirmasi Tolak
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="text-center text-xs text-gray-400">
                            Total transaksi: Rp {{ number_format((float) $sale->grand_total, 0, ',', '.') }}
                            ({{ strtoupper($sale->payment_method ?? '-') }})
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $refunds->links() }}</div>
@endif
@endsection
