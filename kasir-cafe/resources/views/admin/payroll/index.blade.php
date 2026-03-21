@extends('layouts.dashboard')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold">Penggajian Petugas (MVP)</h1>
            <p class="text-sm text-gray-600">Input, approval, dan tanda bayar payroll bulanan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <div class="bg-white border rounded-lg p-3">
            <div class="text-xs text-gray-500">Draft</div>
            <div class="font-semibold">Rp {{ number_format($summary['draft'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white border rounded-lg p-3">
            <div class="text-xs text-gray-500">Approved</div>
            <div class="font-semibold">Rp {{ number_format($summary['approved'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white border rounded-lg p-3">
            <div class="text-xs text-gray-500">Paid</div>
            <div class="font-semibold text-emerald-700">Rp {{ number_format($summary['paid'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="bg-white border rounded-lg p-4">
            <h2 class="font-semibold mb-3">Input Payroll</h2>
            <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.payroll.store' : 'admin.payroll.store') }}" class="space-y-3" id="payroll-form">
                @csrf
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Periode (Bulan)</label>
                    <input type="month" name="period_month" id="payroll-period-month" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('period_month', $period) }}" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Petugas</label>
                    <select name="employee_master_id" id="payroll-employee-master-id" class="w-full border rounded px-3 py-2 text-sm" required>
                        <option value="">Pilih petugas</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_master_id') == $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Gaji Pokok</label>
                    <input type="number" name="base_salary" min="0" step="1" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('base_salary', 0) }}" required>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Lembur</label>
                        <input type="number" name="overtime_amount" min="0" step="1" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('overtime_amount', 0) }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Bonus</label>
                        <input type="number" name="bonus_amount" min="0" step="1" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('bonus_amount', 0) }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Potongan</label>
                    <input type="number" name="deduction_amount" min="0" step="1" class="w-full border rounded px-3 py-2 text-sm" value="{{ old('deduction_amount', 0) }}">
                    <div class="mt-1 text-[11px] text-gray-500">Potongan makan karyawan dihitung otomatis dari selisih nominal di atas jatah bulanan.</div>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Preview Potongan Makan</label>
                    <input type="text" id="meal-deduction-preview" class="w-full border rounded px-3 py-2 text-sm bg-gray-50" value="Rp 0" readonly>
                    <div id="meal-deduction-preview-note" class="mt-1 text-[11px] text-gray-500">Pilih periode dan petugas untuk melihat potongan makan otomatis.</div>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Catatan</label>
                    <textarea name="note" rows="2" class="w-full border rounded px-3 py-2 text-sm">{{ old('note') }}</textarea>
                </div>
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded px-3 py-2 text-sm font-medium">Simpan Payroll</button>
            </form>
        </div>

        <div class="xl:col-span-2 bg-white border rounded-lg p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Periode</label>
                    <input type="month" name="period" value="{{ $period }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Petugas</label>
                    <select name="employee_master_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">Semua petugas</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected($employeeMasterId === $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="DRAFT" @selected($status === 'DRAFT')>DRAFT</option>
                        <option value="APPROVED" @selected($status === 'APPROVED')>APPROVED</option>
                        <option value="PAID" @selected($status === 'PAID')>PAID</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full bg-slate-700 hover:bg-slate-800 text-white rounded px-3 py-2 text-sm">Filter</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-2 border-b">Periode</th>
                            <th class="text-left p-2 border-b">Petugas</th>
                            <th class="text-right p-2 border-b">Pokok</th>
                            <th class="text-right p-2 border-b">Lembur+Bonus</th>
                            <th class="text-right p-2 border-b">Potongan</th>
                            <th class="text-right p-2 border-b">Pot. Makan</th>
                            <th class="text-right p-2 border-b">Net</th>
                            <th class="text-left p-2 border-b">Status</th>
                            <th class="text-center p-2 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                            <tr class="border-b">
                                <td class="p-2">{{ optional($payroll->period_month)->format('m/Y') }}</td>
                                <td class="p-2">{{ $payroll->employee_display_name }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($payroll->overtime_amount + $payroll->bonus_amount, 0, ',', '.') }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($payroll->deduction_amount, 0, ',', '.') }}</td>
                                <td class="p-2 text-right">Rp {{ number_format($payroll->meal_deduction_amount, 0, ',', '.') }}</td>
                                <td class="p-2 text-right font-semibold">Rp {{ number_format($payroll->net_amount, 0, ',', '.') }}</td>
                                <td class="p-2">
                                    <span class="text-xs px-2 py-1 rounded {{ $payroll->status === 'PAID' ? 'bg-emerald-100 text-emerald-700' : ($payroll->status === 'APPROVED' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $payroll->status }}
                                    </span>
                                </td>
                                <td class="p-2 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route(request()->routeIs('manager.*') ? 'manager.payroll.slip' : 'admin.payroll.slip', $payroll) }}"
                                           class="text-xs text-indigo-600 hover:underline">
                                            Slip
                                        </a>
                                        @if($payroll->status !== 'PAID')
                                            <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.payroll.approve' : 'admin.payroll.approve', $payroll) }}">
                                                @csrf
                                                <button class="text-xs text-blue-600 hover:underline">Approve</button>
                                            </form>
                                        @endif
                                        @if($payroll->status !== 'PAID')
                                            <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.payroll.mark_paid' : 'admin.payroll.mark_paid', $payroll) }}">
                                                @csrf
                                                <button class="text-xs text-emerald-600 hover:underline">Paid</button>
                                            </form>
                                        @endif
                                        @if($payroll->status !== 'PAID')
                                            <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.payroll.destroy' : 'admin.payroll.destroy', $payroll) }}" onsubmit="return confirm('Hapus payroll ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-xs text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-4 text-center text-gray-500">Belum ada data payroll.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $payrolls->links() }}
            </div>
        </div>
    </div>

    <script>
        (() => {
            const employeeInput = document.getElementById('payroll-employee-master-id');
            const periodInput = document.getElementById('payroll-period-month');
            const previewInput = document.getElementById('meal-deduction-preview');
            const previewNote = document.getElementById('meal-deduction-preview-note');
            const previewUrl = @json(route(request()->routeIs('manager.*') ? 'manager.payroll.meal_deduction_preview' : 'admin.payroll.meal_deduction_preview'));

            const formatRupiah = (amount) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(amount || 0))}`;

            const loadPreview = async () => {
                const employeeId = employeeInput?.value;
                const periodMonth = periodInput?.value;

                if (!employeeId || !periodMonth) {
                    previewInput.value = 'Rp 0';
                    previewNote.textContent = 'Pilih periode dan petugas untuk melihat potongan makan otomatis.';
                    return;
                }

                previewInput.value = 'Memuat...';
                previewNote.textContent = 'Menghitung transaksi makan lebih jatah...';

                try {
                    const url = new URL(previewUrl, window.location.origin);
                    url.searchParams.set('employee_master_id', employeeId);
                    url.searchParams.set('period_month', periodMonth);

                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Gagal memuat preview');
                    }

                    const data = await response.json();
                    const amount = Number(data.meal_deduction_amount || 0);

                    previewInput.value = formatRupiah(amount);
                    previewNote.textContent = amount > 0
                        ? 'Nominal ini akan otomatis masuk ke Pot. Makan saat payroll disimpan.'
                        : 'Belum ada kelebihan makan pada periode ini.';
                } catch (error) {
                    previewInput.value = 'Rp 0';
                    previewNote.textContent = 'Preview potongan makan gagal dimuat.';
                }
            };

            employeeInput?.addEventListener('change', loadPreview);
            periodInput?.addEventListener('change', loadPreview);
            loadPreview();
        })();
    </script>
@endsection
