@extends('layouts.dashboard')

@section('content')
    @php
        $routePrefix = request()->routeIs('manager.*') ? 'manager' : (request()->routeIs('cashier.*') ? 'cashier' : 'admin');
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Makan Karyawan</h1>
                <p class="text-sm text-slate-500">Catat konsumsi internal, kurangi stok, dan hitung otomatis kelebihan di atas plafon nominal bulanan.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3 xl:grid-cols-6">
            <div class="bg-white border rounded-lg p-3"><div class="text-xs text-slate-500">Transaksi</div><div class="font-semibold text-slate-900">{{ number_format($summary['trx_count']) }}</div></div>
            <div class="bg-white border rounded-lg p-3"><div class="text-xs text-slate-500">Nilai Menu</div><div class="font-semibold text-slate-900">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</div></div>
            <div class="bg-white border rounded-lg p-3"><div class="text-xs text-slate-500">COGS Aktual</div><div class="font-semibold text-slate-900">Rp {{ number_format($summary['cogs_total'], 0, ',', '.') }}</div></div>
            <div class="bg-white border rounded-lg p-3"><div class="text-xs text-slate-500">Beban Konsumsi</div><div class="font-semibold text-amber-700">Rp {{ number_format($summary['expense_cogs_total'], 0, ',', '.') }}</div></div>
            <div class="bg-white border rounded-lg p-3"><div class="text-xs text-slate-500">Ditanggung Perusahaan</div><div class="font-semibold text-emerald-700">Rp {{ number_format($summary['company_covered_amount'], 0, ',', '.') }}</div></div>
            <div class="bg-white border rounded-lg p-3"><div class="text-xs text-slate-500">Pending Potong Payroll</div><div class="font-semibold text-rose-700">Rp {{ number_format($summary['pending_payroll_deduction'], 0, ',', '.') }}</div></div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <div class="bg-white border rounded-lg p-4">
                <h2 class="font-semibold text-slate-900 mb-3">Input Makan Karyawan</h2>
                <form method="POST" action="{{ route($routePrefix . '.employee_meals.store') }}" class="space-y-3" id="employee-meal-form">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Tanggal / Jam</label>
                        <input type="datetime-local" name="consumed_at" value="{{ old('consumed_at', now()->format('Y-m-d\\TH:i')) }}" class="w-full border rounded px-3 py-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Karyawan</label>
                        <select name="employee_id" class="w-full border rounded px-3 py-2 text-sm" required>
                            <option value="">Pilih karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>
                                    {{ $employee->name }}@if(!is_null($employee->meal_allowance_monthly)) - Jatah Rp {{ number_format($employee->meal_allowance_monthly, 0, ',', '.') }}/bulan @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-3">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <div class="text-sm font-medium text-slate-800">Menu yang dikonsumsi</div>
                            <button type="button" id="add-meal-line" class="rounded bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700">
                                + Tambah Menu
                            </button>
                        </div>

                        <div id="meal-lines" class="space-y-2">
                            @php $oldLines = old('lines', [['product_id' => '', 'qty' => 1]]); @endphp
                            @foreach($oldLines as $idx => $oldLine)
                                <div class="meal-line grid grid-cols-12 gap-2">
                                    <div class="col-span-8">
                                        <select name="lines[{{ $idx }}][product_id]" class="w-full border rounded px-3 py-2 text-sm" required>
                                            <option value="">Pilih menu</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" @selected((string) ($oldLine['product_id'] ?? '') === (string) $product->id)>
                                                    {{ $product->name }} - Rp {{ number_format($product->price_default, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" name="lines[{{ $idx }}][qty]" min="1" step="1" value="{{ $oldLine['qty'] ?? 1 }}" class="w-full border rounded px-3 py-2 text-sm" required>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-center">
                                        <button type="button" class="remove-meal-line text-rose-600 text-sm">×</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Catatan</label>
                        <textarea name="note" rows="2" class="w-full border rounded px-3 py-2 text-sm">{{ old('note') }}</textarea>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                        Jatah makan dicek per bulan berdasarkan nominal harga menu. Jika akumulasi bulan berjalan melebihi plafon karyawan, selisihnya masuk ke potongan payroll.
                    </div>

                    <button class="w-full rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">Simpan Transaksi</button>
                </form>
            </div>

            <div class="xl:col-span-2 bg-white border rounded-lg p-4">
                <form method="GET" action="{{ route($routePrefix . '.employee_meals.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Dari</label>
                        <input type="date" name="from" value="{{ $from }}" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Sampai</label>
                        <input type="date" name="to" value="{{ $to }}" class="w-full border rounded px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Karyawan</label>
                        <select name="employee_id" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">Semua karyawan</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected($employeeId === $employee->id)>{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="w-full rounded bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900">Filter</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="text-left p-2 border-b">Waktu</th>
                                <th class="text-left p-2 border-b">Karyawan</th>
                                <th class="text-left p-2 border-b">Menu</th>
                                <th class="text-right p-2 border-b">Nilai</th>
                                <th class="text-right p-2 border-b">Beban</th>
                                <th class="text-right p-2 border-b">Lebih Jatah</th>
                                <th class="text-left p-2 border-b">Payroll</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($meals as $meal)
                                <tr class="border-b align-top">
                                    <td class="p-2 whitespace-nowrap">{{ optional($meal->consumed_at)->format('d/m/Y H:i') }}</td>
                                    <td class="p-2">
                                        <div class="font-medium text-slate-800">{{ $meal->employee->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $meal->cashier->name ?? '-' }}</div>
                                    </td>
                                    <td class="p-2">
                                        <div class="space-y-1">
                                            @foreach($meal->lines as $line)
                                                <div class="text-xs text-slate-700">{{ $line->product->name ?? '-' }} × {{ rtrim(rtrim(number_format($line->qty, 2, ',', '.'), '0'), ',') }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="p-2 text-right whitespace-nowrap">Rp {{ number_format($meal->total_amount, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right whitespace-nowrap">Rp {{ number_format($meal->expense_cogs_total, 0, ',', '.') }}</td>
                                    <td class="p-2 text-right whitespace-nowrap">
                                        <div class="{{ $meal->excess_amount > 0 ? 'text-rose-700 font-medium' : 'text-slate-500' }}">
                                            Rp {{ number_format($meal->excess_amount, 0, ',', '.') }}
                                        </div>
                                        @if($meal->is_over_allowance)
                                            <div class="text-[11px] text-rose-600">Potong payroll</div>
                                        @endif
                                    </td>
                                    <td class="p-2 whitespace-nowrap">
                                        @if($meal->payroll)
                                            <div class="text-xs text-emerald-700 font-medium">PAY {{ optional($meal->payroll->period_month)->format('m/Y') }}</div>
                                        @elseif($meal->excess_amount > 0)
                                            <div class="text-xs text-amber-700">Menunggu payroll</div>
                                        @else
                                            <div class="text-xs text-slate-500">Tidak ada</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-4 text-center text-slate-500">Belum ada transaksi makan karyawan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $meals->links() }}
                </div>
            </div>
        </div>
    </div>

    <template id="meal-line-template">
        <div class="meal-line grid grid-cols-12 gap-2">
            <div class="col-span-8">
                <select class="w-full border rounded px-3 py-2 text-sm" data-name="product_id" required>
                    <option value="">Pilih menu</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} - Rp {{ number_format($product->price_default, 0, ',', '.') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" min="1" step="1" value="1" class="w-full border rounded px-3 py-2 text-sm" data-name="qty" required>
            </div>
            <div class="col-span-1 flex items-center justify-center">
                <button type="button" class="remove-meal-line text-rose-600 text-sm">×</button>
            </div>
        </div>
    </template>

    <script>
        (() => {
            const linesWrap = document.getElementById('meal-lines');
            const template = document.getElementById('meal-line-template');
            const addButton = document.getElementById('add-meal-line');

            const reindex = () => {
                linesWrap.querySelectorAll('.meal-line').forEach((line, index) => {
                    line.querySelectorAll('[data-name]').forEach((field) => {
                        field.name = `lines[${index}][${field.dataset.name}]`;
                    });
                });
            };

            addButton?.addEventListener('click', () => {
                const fragment = template.content.cloneNode(true);
                linesWrap.appendChild(fragment);
                reindex();
            });

            linesWrap?.addEventListener('click', (event) => {
                const button = event.target.closest('.remove-meal-line');
                if (!button) {
                    return;
                }

                if (linesWrap.querySelectorAll('.meal-line').length <= 1) {
                    return;
                }

                button.closest('.meal-line')?.remove();
                reindex();
            });

            reindex();
        })();
    </script>
@endsection
