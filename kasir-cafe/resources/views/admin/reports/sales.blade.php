@extends('layouts.dashboard')

@section('content')
    @php
        $salesRoute = request()->routeIs('cashier.*')
            ? 'cashier.reports.sales'
            : (request()->routeIs('manager.*') ? 'manager.reports.sales' : 'admin.reports.sales');

        $salesExportRoute = request()->routeIs('cashier.*')
            ? 'cashier.reports.sales.export'
            : (request()->routeIs('manager.*') ? 'manager.reports.sales.export' : 'admin.reports.sales.export');

        $receiptRoute = request()->routeIs('cashier.*')
            ? 'cashier.pos.receipt'
            : (request()->routeIs('manager.*') ? 'manager.sales.receipt' : 'admin.sales.receipt');

        $refundCreateRoute = request()->routeIs('cashier.*')
            ? 'cashier.refunds.create'
            : (request()->routeIs('manager.*') ? 'manager.refunds.create' : 'admin.refunds.create');

        $perPayment = $summary['per_payment'] ?? [];
    @endphp

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
        <div>
            <a href="{{ route($salesExportRoute, request()->query()) }}"
               class="px-3 py-2 rounded bg-green-600 text-white text-sm font-medium hover:bg-green-700">
                Export Excel
            </a>
        </div>
    </div>

    <div class="bg-white border rounded-lg p-4 mb-4">
        <form method="GET" action="{{ route($salesRoute) }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="border rounded px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Nota</label>
                <input type="text"
                       name="receipt_no"
                       value="{{ $receiptNo ?? '' }}"
                       placeholder="Contoh: INV-2026-001"
                       class="border rounded px-3 py-2 text-sm min-w-[220px]">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Limit Data</label>
                <select name="limit" class="border rounded px-3 py-2 text-sm">
                    @foreach([25, 50, 100, 200] as $size)
                        <option value="{{ $size }}" @selected((int) ($limit ?? 50) === $size)>{{ $size }} baris</option>
                    @endforeach
                </select>
            </div>

            @if (!request()->routeIs('cashier.*'))
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kasir</label>
                    <select name="cashier_id" class="border rounded px-3 py-2 text-sm">
                        <option value="">Semua Kasir</option>
                        @foreach(($cashiers ?? []) as $c)
                            <option value="{{ $c->id }}" @selected(($selectedCashier ?? null) === $c->id)>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium">
                    Tampilkan
                </button>
            </div>

            @if(($receiptNo ?? '') !== '' || request()->filled('cashier_id') || request()->filled('limit'))
                <div>
                    <a href="{{ route($salesRoute, ['from' => $from, 'to' => $to]) }}" class="px-4 py-2 rounded border text-sm font-medium hover:bg-gray-50">
                        Reset Filter Tambahan
                    </a>
                </div>
            @endif
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-7 gap-4 mb-4">
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Subtotal</div><div class="text-lg font-semibold">Rp {{ number_format($summary['subtotal'] ?? 0, 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Diskon</div><div class="text-lg font-semibold">Rp {{ number_format($summary['discount'] ?? 0, 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Pajak</div><div class="text-lg font-semibold">Rp {{ number_format($summary['tax'] ?? 0, 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Refund</div><div class="text-lg font-semibold">Rp {{ number_format($summary['refund'] ?? 0, 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Omzet</div><div class="text-lg font-semibold">Rp {{ number_format($summary['omzet'] ?? 0, 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">COGS (HPP)</div><div class="text-lg font-semibold">Rp {{ number_format($summary['cogs'] ?? 0, 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Laba Kotor</div><div class="text-lg font-semibold">Rp {{ number_format($summary['profit'] ?? 0, 0, ',', '.') }}</div></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Total CASH</div><div class="text-lg font-semibold">Rp {{ number_format($perPayment['CASH'] ?? 0, 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Total QRIS</div><div class="text-lg font-semibold">Rp {{ number_format($perPayment['QRIS'] ?? 0, 0, ',', '.') }}</div></div>
        <div class="bg-white border rounded-lg p-4"><div class="text-xs text-gray-500 mb-1">Total DEBIT</div><div class="text-lg font-semibold">Rp {{ number_format($perPayment['DEBIT'] ?? 0, 0, ',', '.') }}</div></div>
    </div>

    <div class="bg-white border rounded-lg">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3">
            <div>
                <div class="font-semibold">Detail Transaksi</div>
                <div class="text-xs text-gray-500">Tabel dibatasi default agar tetap cepat. Gunakan pencarian nota untuk audit yang lebih fokus.</div>
            </div>
            <div class="text-xs text-gray-500">
                {{ method_exists($sales, 'total') ? $sales->total() : count($sales) }} transaksi
            </div>
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Tanggal</th>
                        <th class="text-left px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">ID</th>
                        <th class="text-left px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">No. Nota</th>
                        <th class="text-left px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Kasir</th>
                        <th class="text-left px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Metode</th>
                        <th class="text-right px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Subtotal</th>
                        <th class="text-right px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Diskon</th>
                        <th class="text-right px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Pajak</th>
                        <th class="text-right px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Total</th>
                        <th class="text-right px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Refund</th>
                        <th class="text-right px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">COGS</th>
                        <th class="text-right px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Laba</th>
                        <th class="text-center px-3 py-2 border-b sticky top-0 z-10 bg-gray-50">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $s)
                        <tr class="border-b">
                            <td class="px-3 py-2 align-top whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($s->paid_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2 align-top whitespace-nowrap">#{{ $s->id }}</td>
                            <td class="px-3 py-2 align-top whitespace-nowrap">
                                <a href="{{ route($receiptRoute, $s->id) }}" class="font-medium text-blue-600 hover:underline">
                                    {{ $s->receipt_no ?? '-' }}
                                </a>
                            </td>
                            <td class="px-3 py-2 align-top">{{ optional($s->cashier)->name ?? '-' }}</td>
                            <td class="px-3 py-2 align-top">{{ strtoupper($s->payment_method ?? '-') }}</td>
                            <td class="px-3 py-2 text-right align-top whitespace-nowrap">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right align-top whitespace-nowrap">Rp {{ number_format($s->discount_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right align-top whitespace-nowrap">Rp {{ number_format($s->tax_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right align-top whitespace-nowrap">Rp {{ number_format($s->grand_total ?? ($s->total - ($s->discount_amount ?? 0) + ($s->tax_amount ?? 0)), 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right align-top whitespace-nowrap">Rp {{ number_format($s->effective_refund_total ?? ($s->refund_total ?? 0), 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right align-top whitespace-nowrap">Rp {{ number_format($s->effective_cogs_total ?? ($s->cogs_total ?? 0), 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right align-top whitespace-nowrap">Rp {{ number_format($s->effective_profit_gross ?? ($s->profit_gross ?? 0), 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-center align-top">
                                <a href="{{ route($refundCreateRoute, $s) }}"
                                   class="text-xs font-medium text-blue-600 hover:underline">Refund</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-3 py-4 text-center text-gray-500">
                                Belum ada transaksi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($sales, 'links'))
            <div class="border-t px-4 py-3">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
@endsection
