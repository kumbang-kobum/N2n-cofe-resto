@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h1 class="text-xl font-semibold">Semua Menu Terjual</h1>
        <p class="text-sm text-gray-600">Seluruh menu yang terjual berdasarkan kuantitas pada periode yang dipilih.</p>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Periode</div><div class="text-sm font-semibold">{{ $from }} s/d {{ $to }}</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Total Jenis Menu Terjual</div><div class="text-sm font-semibold">{{ $rows->count() }} menu</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Total Qty Terjual</div><div class="text-sm font-semibold">{{ number_format((float) $rows->sum('qty_total'), 0, ',', '.') }}</div></div>
    </div>

    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <div>
                <div class="font-semibold">Semua Produk Terjual</div>
                <div class="text-xs text-gray-500">Ringkasan seluruh menu terjual, omzet, dan jumlah transaksi.</div>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left p-2 border-b sticky top-0 z-10 bg-gray-50">No.</th>
                        <th class="text-left p-2 border-b sticky top-0 z-10 bg-gray-50">Menu</th>
                        <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Qty Terjual</th>
                        <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Omzet</th>
                        <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Jumlah Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $row)
                        <tr class="border-b">
                            <td class="p-2 font-semibold text-gray-500">{{ $i + 1 }}</td>
                            <td class="p-2 font-medium text-slate-800">{{ $row->product_name }}</td>
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
