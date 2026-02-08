@extends('layouts.dashboard')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
    <div>
        <h1 class="text-xl font-semibold">Laporan Penjualan</h1>
        <p class="text-xs text-gray-500">
            Ringkasan omzet & metode pembayaran (CASH / QRIS / DEBIT).
        </p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('admin.reports.sales') }}"
      class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
        <input type="date" name="from"
               value="{{ $from->format('Y-m-d') }}"
               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
        <input type="date" name="to"
               value="{{ $to->format('Y-m-d') }}"
               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Group By</label>
        <select name="group"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="daily"   {{ $group === 'daily' ? 'selected' : '' }}>Harian</option>
            <option value="monthly" {{ $group === 'monthly' ? 'selected' : '' }}>Bulanan</option>
        </select>
    </div>
    <div>
        <button type="submit"
                class="w-full md:w-auto px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
            Tampilkan
        </button>
    </div>
</form>

{{-- Ringkasan Atas --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs font-medium text-gray-500 mb-1">Periode</div>
        <div class="text-sm text-gray-700">
            {{ $from->format('d/m/Y') }} s/d {{ $to->format('d/m/Y') }}
        </div>
        <div class="mt-2 text-[11px] text-gray-400">
            Group: <span class="uppercase font-semibold">{{ $group }}</span>
        </div>
    </div>

    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs font-medium text-gray-500 mb-1">Total Omzet</div>
        <div class="text-lg font-semibold text-blue-700">
            Rp {{ number_format($overall['total'], 0, ',', '.') }}
        </div>
        <div class="mt-1 text-[11px] text-gray-400">
            {{ $overall['transactions'] }} transaksi
        </div>
    </div>

    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs font-medium text-gray-500 mb-1">CASH (fisik di laci)</div>
        <div class="text-lg font-semibold text-gray-800">
            Rp {{ number_format($overall['cash'], 0, ',', '.') }}
        </div>
    </div>

    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs font-medium text-gray-500 mb-1">QRIS + DEBIT</div>
        <div class="text-sm text-gray-700">
            QRIS: <span class="font-semibold">
                Rp {{ number_format($overall['qris'], 0, ',', '.') }}
            </span>
        </div>
        <div class="text-sm text-gray-700">
            DEBIT: <span class="font-semibold">
                Rp {{ number_format($overall['debit'], 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>

{{-- Tabel detail per hari / bulan --}}
<div class="bg-white border rounded-lg p-4">
    <div class="flex items-center justify-between mb-3">
        <div class="font-semibold text-sm">Detail {{ $group === 'monthly' ? 'Bulanan' : 'Harian' }}</div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs md:text-sm">
            <thead class="bg-blue-50 border-b border-blue-100">
                <tr>
                    <th class="p-2 text-left">Tanggal / Bulan</th>
                    <th class="p-2 text-right">Transaksi</th>
                    <th class="p-2 text-right">Total</th>
                    <th class="p-2 text-right">CASH</th>
                    <th class="p-2 text-right">QRIS</th>
                    <th class="p-2 text-right">DEBIT</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b last:border-0">
                        <td class="p-2">{{ $row['label'] }}</td>
                        <td class="p-2 text-right">{{ $row['transactions'] }}</td>
                        <td class="p-2 text-right">
                            Rp {{ number_format($row['total'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-right">
                            Rp {{ number_format($row['cash'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-right">
                            Rp {{ number_format($row['qris'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-right">
                            Rp {{ number_format($row['debit'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-3 text-center text-xs text-gray-500">
                            Tidak ada transaksi pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection