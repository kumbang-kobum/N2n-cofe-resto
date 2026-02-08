@extends('layouts.dashboard')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <div>
            <h1 class="text-xl font-semibold">Laporan Penjualan</h1>
            <p class="text-sm text-gray-600">
                Periode:
                <span class="font-medium">{{ $from }}</span>
                s/d
                <span class="font-medium">{{ $to }}</span>
            </p>
        </div>
    </div>

    {{-- Filter Periode --}}
    <div class="bg-white border rounded-lg p-4 mb-4">
        <form
            method="GET"
            action="{{ route(request()->routeIs('cashier.*') ? 'cashier.reports.sales' : 'admin.reports.sales') }}"
            class="flex flex-wrap items-end gap-3"
        >
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input
                    type="date"
                    name="from"
                    value="{{ $from }}"
                    class="border rounded px-3 py-2 text-sm"
                >
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input
                    type="date"
                    name="to"
                    value="{{ $to }}"
                    class="border rounded px-3 py-2 text-sm"
                >
            </div>

            <div>
                <button
                    type="submit"
                    class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium"
                >
                    Tampilkan
                </button>
            </div>
        </form>
    </div>

    {{-- Ringkasan Utama --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Omzet</div>
            <div class="text-lg font-semibold">
                Rp {{ number_format($summary['omzet'] ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">COGS (HPP)</div>
            <div class="text-lg font-semibold">
                Rp {{ number_format($summary['cogs'] ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Laba Kotor</div>
            <div class="text-lg font-semibold">
                Rp {{ number_format($summary['profit'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
    </div>

    @php
        $perPayment = $summary['per_payment'] ?? [];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Total CASH</div>
            <div class="text-lg font-semibold">
                Rp {{ number_format($perPayment['CASH'] ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Total QRIS</div>
            <div class="text-lg font-semibold">
                Rp {{ number_format($perPayment['QRIS'] ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Total DEBIT</div>
            <div class="text-lg font-semibold">
                Rp {{ number_format($perPayment['DEBIT'] ?? 0, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Tabel Detail Transaksi --}}
    <div class="bg-white border rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="font-semibold">Detail Transaksi</div>
            <div class="text-xs text-gray-500">
                {{ count($sales) }} transaksi
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-2 border-b">Tanggal</th>
                        <th class="text-left p-2 border-b">ID</th>
                        <th class="text-left p-2 border-b">No. Nota</th>
                        <th class="text-left p-2 border-b">Kasir</th>
                        <th class="text-left p-2 border-b">Metode</th>
                        <th class="text-right p-2 border-b">Total</th>
                        <th class="text-right p-2 border-b">COGS</th>
                        <th class="text-right p-2 border-b">Laba</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $s)
                        <tr class="border-b">
                            <td class="p-2 align-top">
                                {{ \Illuminate\Support\Carbon::parse($s->paid_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-2 align-top">
                                #{{ $s->id }}
                            </td>
                            <td class="p-2 align-top">
                                {{ $s->receipt_no ?? '-' }}
                            </td>
                            <td class="p-2 align-top">
                                {{ optional($s->cashier)->name ?? '-' }}
                            </td>
                            <td class="p-2 align-top">
                                {{ strtoupper($s->payment_method ?? '-') }}
                            </td>
                            <td class="p-2 text-right align-top">
                                Rp {{ number_format($s->total, 0, ',', '.') }}
                            </td>
                            <td class="p-2 text-right align-top">
                                Rp {{ number_format($s->cogs_total ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="p-2 text-right align-top">
                                Rp {{ number_format($s->profit_gross ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-4 text-center text-gray-500">
                                Belum ada transaksi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection