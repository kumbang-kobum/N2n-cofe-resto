@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h1 class="text-xl font-semibold">Top 10 Penjualan Terbanyak</h1>
        <p class="text-sm text-gray-600">Berdasarkan kuantitas menu terjual.</p>
    </div>

    <div class="bg-white border rounded-lg p-4 mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="border rounded px-3 py-2 text-sm">
            </div>
            <button class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium">Tampilkan</button>
        </form>
    </div>

    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="p-4 border-b font-semibold">Periode {{ $from }} s/d {{ $to }}</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-2 border-b">Ranking</th>
                        <th class="text-left p-2 border-b">Menu</th>
                        <th class="text-right p-2 border-b">Qty Terjual</th>
                        <th class="text-right p-2 border-b">Omzet</th>
                        <th class="text-right p-2 border-b">Jumlah Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $row)
                        <tr class="border-b">
                            <td class="p-2">{{ $i + 1 }}</td>
                            <td class="p-2">{{ $row->product_name }}</td>
                            <td class="p-2 text-right">{{ number_format((float) $row->qty_total, 0, ',', '.') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format((float) $row->omzet_total, 0, ',', '.') }}</td>
                            <td class="p-2 text-right">{{ number_format((float) $row->trx_total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada data penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
