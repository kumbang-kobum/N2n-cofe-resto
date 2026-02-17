@extends('layouts.dashboard')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-semibold">Laporan Keuangan</h1>
            <p class="text-sm text-gray-600">Rekap harian dan bulanan: omzet, HPP, pengeluaran kasir, dan laba.</p>
        </div>
        <a href="{{ route(request()->routeIs('cashier.*') ? 'cashier.reports.finance.export' : (request()->routeIs('manager.*') ? 'manager.reports.finance.export' : 'admin.reports.finance.export'), request()->query()) }}"
           class="px-3 py-2 rounded bg-green-600 text-white text-sm font-medium hover:bg-green-700">
            Export Excel
        </a>
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
            @if(!auth()->user()->hasRole('cashier'))
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Kasir</label>
                    <select name="cashier_id" class="border rounded px-3 py-2 text-sm">
                        <option value="">Semua Kasir</option>
                        @foreach($cashiers as $cashier)
                            <option value="{{ $cashier->id }}" @selected($cashierId === $cashier->id)>{{ $cashier->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium">Tampilkan</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 xl:grid-cols-8 gap-3 mb-5">
        <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Subtotal</div><div class="font-semibold">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Diskon</div><div class="font-semibold">Rp {{ number_format($summary['discount'], 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Pajak</div><div class="font-semibold">Rp {{ number_format($summary['tax'], 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Omzet</div><div class="font-semibold">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Refund</div><div class="font-semibold">Rp {{ number_format($summary['refund'], 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">COGS (HPP)</div><div class="font-semibold">Rp {{ number_format($summary['cogs'], 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Laba Kotor</div><div class="font-semibold">Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Pengeluaran Kas</div><div class="font-semibold">Rp {{ number_format($summary['expense_total'], 0, ',', '.') }}</div></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
        <div class="bg-white border rounded-lg p-4">
            <div class="text-sm text-gray-600 mb-1">Laba Bersih (Laba Kotor - Pengeluaran Kas)</div>
            <div class="text-2xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                Rp {{ number_format($summary['net_profit'], 0, ',', '.') }}
            </div>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <div class="text-sm text-gray-600 mb-1">Saldo Kas dari Penjualan CASH - Pengeluaran</div>
            <div class="text-2xl font-bold {{ $summary['cash_balance'] >= 0 ? 'text-blue-700' : 'text-red-600' }}">
                Rp {{ number_format($summary['cash_balance'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Total CASH: Rp {{ number_format($summary['cash_sales_total'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-white border rounded-lg p-4 mb-5">
        <div class="font-semibold mb-3">Ringkasan Harian</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-2 border-b">Tanggal</th>
                        <th class="text-right p-2 border-b">Omzet</th>
                        <th class="text-right p-2 border-b">Refund</th>
                        <th class="text-right p-2 border-b">COGS</th>
                        <th class="text-right p-2 border-b">Laba Kotor</th>
                        <th class="text-right p-2 border-b">Pengeluaran</th>
                        <th class="text-right p-2 border-b">Laba Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyRows as $row)
                        <tr class="border-b">
                            <td class="p-2">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['refund'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['cogs'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['gross_profit'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['expense_total'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right font-medium {{ $row['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                Rp {{ number_format($row['net_profit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center p-4 text-gray-500">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white border rounded-lg p-4">
        <div class="font-semibold mb-3">Ringkasan Bulanan</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-2 border-b">Bulan</th>
                        <th class="text-right p-2 border-b">Omzet</th>
                        <th class="text-right p-2 border-b">Refund</th>
                        <th class="text-right p-2 border-b">COGS</th>
                        <th class="text-right p-2 border-b">Laba Kotor</th>
                        <th class="text-right p-2 border-b">Pengeluaran</th>
                        <th class="text-right p-2 border-b">Laba Bersih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyRows as $row)
                        <tr class="border-b">
                            <td class="p-2">{{ $row['month'] }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['refund'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['cogs'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['gross_profit'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right">Rp {{ number_format($row['expense_total'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right font-medium {{ $row['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                Rp {{ number_format($row['net_profit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center p-4 text-gray-500">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
