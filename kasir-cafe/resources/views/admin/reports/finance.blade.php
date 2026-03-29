@extends('layouts.dashboard')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-semibold">Laporan Keuangan</h1>
            <p class="text-sm text-gray-600">Disusun mengikuti modul aplikasi: penjualan, pengeluaran operasional, kas kecil, makan karyawan, dan penggajian petugas.</p>
        </div>
        <a href="{{ route(request()->routeIs('cashier.*') ? 'cashier.reports.finance.export' : (request()->routeIs('manager.*') ? 'manager.reports.finance.export' : 'admin.reports.finance.export'), request()->query()) }}"
           class="px-3 py-2 rounded bg-green-600 text-white text-sm font-medium hover:bg-green-700">
            Export Excel
        </a>
    </div>

    <div class="bg-white border rounded-lg p-4 mb-5">
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

    <div class="space-y-5">
        <section class="space-y-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Penjualan & Margin</h2>
                <p class="text-xs text-gray-500">Ringkasan dari menu penjualan, refund, dan performa margin.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-3">
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Jumlah Transaksi</div><div class="font-semibold">{{ number_format($summary['trx_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Subtotal</div><div class="font-semibold">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Diskon</div><div class="font-semibold">Rp {{ number_format($summary['discount'], 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Pajak</div><div class="font-semibold">Rp {{ number_format($summary['tax'], 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Omzet</div><div class="font-semibold">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Refund</div><div class="font-semibold">Rp {{ number_format($summary['refund'], 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">COGS (HPP)</div><div class="font-semibold">Rp {{ number_format($summary['cogs'], 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Laba Kotor</div><div class="font-semibold">Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}</div></div>
            </div>
        </section>

        <section class="space-y-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Pengeluaran Operasional & Kas Kecil</h2>
                <p class="text-xs text-gray-500">Ringkasan dari menu Pengeluaran Operasional dan Kas Kecil.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-3">
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Beban Operasional Approved</div><div class="font-semibold">Rp {{ number_format($summary['expense_total'], 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Pending Approval</div><div class="font-semibold text-amber-700">Rp {{ number_format($summary['expense_pending'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Rejected</div><div class="font-semibold text-rose-700">Rp {{ number_format($summary['expense_rejected'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Kas Penjualan Dipakai</div><div class="font-semibold">Rp {{ number_format($summary['direct_expense_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Kas Kecil Dipakai</div><div class="font-semibold">Rp {{ number_format($summary['petty_cash_expense_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Dana Kas Kecil</div><div class="font-semibold">Rp {{ number_format($summary['petty_cash_opening_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Pengembalian Kas Kecil</div><div class="font-semibold">Rp {{ number_format($summary['petty_cash_returned_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Saldo Kas Kecil</div><div class="font-semibold">Rp {{ number_format($summary['petty_cash_balance_total'] ?? 0, 0, ',', '.') }}</div></div>
            </div>
        </section>

        <section class="space-y-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Makan Karyawan & Payroll</h2>
                <p class="text-xs text-gray-500">Ringkasan dari modul Makan Karyawan dan Penggajian Petugas.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-3">
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Transaksi Makan</div><div class="font-semibold">{{ number_format($summary['employee_meal_count'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Nilai Makan Karyawan</div><div class="font-semibold">Rp {{ number_format($summary['employee_meal_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Beban Makan Karyawan</div><div class="font-semibold">Rp {{ number_format($summary['employee_meal_expense_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Lebih Jatah</div><div class="font-semibold text-rose-700">Rp {{ number_format($summary['employee_meal_excess_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Pending Potong Payroll</div><div class="font-semibold text-amber-700">Rp {{ number_format($summary['employee_meal_pending_deduction'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Payroll (Beban)</div><div class="font-semibold">Rp {{ number_format($summary['payroll_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Payroll (Kas Keluar)</div><div class="font-semibold">Rp {{ number_format($summary['payroll_cash_total'] ?? 0, 0, ',', '.') }}</div></div>
                <div class="bg-white border rounded-lg p-3"><div class="text-xs text-gray-500">Penjualan CASH</div><div class="font-semibold">Rp {{ number_format($summary['cash_sales_total'] ?? 0, 0, ',', '.') }}</div></div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Laba Bersih Sebelum Payroll</div>
                <div class="text-2xl font-bold {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    Rp {{ number_format($summary['net_profit'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-500 mt-1">Sudah dikurangi beban operasional dan beban makan karyawan.</div>
            </div>
            <div class="bg-white border rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Laba Bersih Setelah Payroll</div>
                <div class="text-2xl font-bold {{ ($summary['net_profit_after_payroll'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    Rp {{ number_format($summary['net_profit_after_payroll'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-500 mt-1">Ini angka akhir setelah modul payroll dibebankan.</div>
            </div>
            <div class="bg-white border rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Saldo Kas Penjualan Setelah Payroll</div>
                <div class="text-2xl font-bold {{ ($summary['cash_balance_after_payroll'] ?? 0) >= 0 ? 'text-blue-700' : 'text-red-600' }}">
                    Rp {{ number_format($summary['cash_balance_after_payroll'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-500 mt-1">Dari penjualan CASH, dikurangi pengeluaran langsung dan kas keluar payroll.</div>
            </div>
        </section>

        <div class="rounded-lg border bg-white">
            <div class="border-b px-4 py-3">
                <div class="font-semibold">Ringkasan Harian</div>
                <div class="text-xs text-gray-500">Disusun mengikuti pengaruh modul penjualan, pengeluaran, makan karyawan, dan payroll per hari.</div>
            </div>
            <div class="overflow-x-auto overflow-y-auto max-h-[55vh]">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="text-left p-2 border-b sticky top-0 z-10 bg-gray-50">Tanggal</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Omzet</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Refund</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">COGS</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Laba Kotor</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Beban Op.</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Kas Penjualan</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Kas Kecil</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Beban Makan</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Payroll</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Laba Bersih</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Laba Setelah Payroll</th>
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
                                <td class="p-2 text-right">Rp {{ number_format($row['direct_expense_total'] ?? 0, 0, ',', '.') }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($row['petty_cash_expense_total'] ?? 0, 0, ',', '.') }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($row['employee_meal_expense_total'] ?? 0, 0, ',', '.') }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($row['payroll_total'] ?? 0, 0, ',', '.') }}</td>
                                <td class="p-2 text-right font-medium {{ $row['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($row['net_profit'], 0, ',', '.') }}</td>
                                <td class="p-2 text-right font-medium {{ ($row['net_after_payroll'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($row['net_after_payroll'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center p-4 text-gray-500">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border bg-white">
            <div class="border-b px-4 py-3">
                <div class="font-semibold">Ringkasan Bulanan</div>
                <div class="text-xs text-gray-500">Rekap bulanan dari seluruh modul yang mempengaruhi laporan keuangan.</div>
            </div>
            <div class="overflow-x-auto overflow-y-auto max-h-[55vh]">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="text-left p-2 border-b sticky top-0 z-10 bg-gray-50">Bulan</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Omzet</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Refund</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">COGS</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Laba Kotor</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Beban Op.</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Kas Penjualan</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Kas Kecil</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Beban Makan</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Payroll</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Laba Bersih</th>
                            <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Laba Setelah Payroll</th>
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
                                <td class="p-2 text-right">Rp {{ number_format($row['direct_expense_total'] ?? 0, 0, ',', '.') }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($row['petty_cash_expense_total'] ?? 0, 0, ',', '.') }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($row['employee_meal_expense_total'] ?? 0, 0, ',', '.') }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($row['payroll_total'] ?? 0, 0, ',', '.') }}</td>
                                <td class="p-2 text-right font-medium {{ $row['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($row['net_profit'], 0, ',', '.') }}</td>
                                <td class="p-2 text-right font-medium {{ ($row['net_after_payroll'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($row['net_after_payroll'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-center p-4 text-gray-500">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(($pettyCashFunds ?? collect())->isNotEmpty())
            <div class="rounded-lg border bg-white">
                <div class="border-b px-4 py-3">
                    <div class="font-semibold">Ringkasan Kas Kecil</div>
                    <div class="text-xs text-gray-500">Mengikuti data dari menu Kas Kecil.</div>
                </div>
                <div class="overflow-x-auto overflow-y-auto max-h-[45vh]">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="text-left p-2 border-b sticky top-0 z-10 bg-gray-50">Periode</th>
                                <th class="text-left p-2 border-b sticky top-0 z-10 bg-gray-50">Nama Dana</th>
                                <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Dana Awal</th>
                                <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Terpakai Approved</th>
                                <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Dikembalikan</th>
                                <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Saldo</th>
                                <th class="text-left p-2 border-b sticky top-0 z-10 bg-gray-50">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pettyCashFunds as $fund)
                                @php
                                    $used = (float) ($fund->approved_used_total ?? 0);
                                    $remaining = (float) $fund->opening_balance - $used - (float) $fund->returned_amount;
                                @endphp
                                <tr class="border-b">
                                    <td class="p-2">{{ optional($fund->period_start)->format('d/m/Y') }} - {{ optional($fund->period_end)->format('d/m/Y') }}</td>
                                    <td class="p-2">{{ $fund->name }}</td>
                                    <td class="p-2 text-right">Rp {{ number_format($fund->opening_balance, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right">Rp {{ number_format($used, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right">Rp {{ number_format($fund->returned_amount, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right font-medium {{ $remaining >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                                    <td class="p-2">{{ $fund->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(($expenseCategoryRows ?? collect())->isNotEmpty())
            <div class="rounded-lg border bg-white">
                <div class="border-b px-4 py-3">
                    <div class="font-semibold">Ringkasan Pengeluaran per Kategori</div>
                    <div class="text-xs text-gray-500">Mengikuti kategori dari menu Pengeluaran Operasional.</div>
                </div>
                <div class="overflow-x-auto overflow-y-auto max-h-[45vh]">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="text-left p-2 border-b sticky top-0 z-10 bg-gray-50">Kategori</th>
                                <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Jumlah Transaksi</th>
                                <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Kas Penjualan</th>
                                <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Kas Kecil</th>
                                <th class="text-right p-2 border-b sticky top-0 z-10 bg-gray-50">Total Approved</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenseCategoryRows as $row)
                                <tr class="border-b">
                                    <td class="p-2 font-medium">{{ $row['category'] }}</td>
                                    <td class="p-2 text-right">{{ number_format($row['count'], 0, ',', '.') }}</td>
                                    <td class="p-2 text-right">Rp {{ number_format($row['direct_total'], 0, ',', '.') }}</td>
                                    <td class="p-2 text-right">Rp {{ number_format($row['petty_cash_total'], 0, ',', '.') }}</td>
                                    <td class="p-2 text-right font-semibold">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
