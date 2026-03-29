@extends('layouts.dashboard')

@section('content')
    @php
        $attendanceExportRoute = request()->routeIs('manager.*') ? 'manager.payroll.attendance_export' : 'admin.payroll.attendance_export';
        $attendanceExportParams = array_filter([
            'period' => $period,
            'employee_master_id' => $employeeMasterId,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold">Penggajian Petugas (MVP)</h1>
            <p class="text-sm text-gray-600">Input, approval, dan tanda bayar payroll bulanan.</p>
        </div>
        <a href="{{ route($attendanceExportRoute, $attendanceExportParams) }}"
           class="inline-flex items-center rounded bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">
            Export Excel Rekap Absensi
        </a>
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
                    <label class="block text-xs text-gray-600 mb-1">Preview Potongan Telat</label>
                    <input type="text" id="late-deduction-preview" class="w-full border rounded px-3 py-2 text-sm bg-gray-50" value="Rp 0" readonly>
                    <div id="late-deduction-preview-note" class="mt-1 text-[11px] text-gray-500">Akan diambil dari absensi telat yang belum masuk payroll.</div>
                </div>
                <div class="rounded-lg border bg-slate-50 p-3">
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Rekap Absensi Bulan Ini</div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                        <div class="rounded border bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Hadir</div>
                            <div id="attendance-total-present" class="font-semibold">0</div>
                        </div>
                        <div class="rounded border bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Telat</div>
                            <div id="attendance-total-late" class="font-semibold text-amber-700">0</div>
                        </div>
                        <div class="rounded border bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Belum Lengkap</div>
                            <div id="attendance-total-incomplete" class="font-semibold text-slate-700">0</div>
                        </div>
                        <div class="rounded border bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Lembur</div>
                            <div id="attendance-overtime-minutes" class="font-semibold">0 m</div>
                        </div>
                        <div class="rounded border bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Face Verified</div>
                            <div id="attendance-total-face-verified" class="font-semibold text-emerald-700">0</div>
                        </div>
                        <div class="rounded border bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Perlu Review</div>
                            <div id="attendance-total-review-required" class="font-semibold text-amber-700">0</div>
                        </div>
                        <div class="rounded border bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Cuti</div>
                            <div id="attendance-total-leave-days" class="font-semibold">0 hari</div>
                        </div>
                        <div class="rounded border bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Sakit</div>
                            <div id="attendance-total-sick-days" class="font-semibold">0 hari</div>
                        </div>
                        <div class="rounded border bg-white px-3 py-2">
                            <div class="text-xs text-gray-500">Izin</div>
                            <div id="attendance-total-permission-days" class="font-semibold">0 hari</div>
                        </div>
                    </div>
                    <div id="attendance-summary-note" class="mt-2 text-[11px] text-gray-500">Pilih periode dan petugas untuk melihat rekap absensi bulanan.</div>
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
                            <th class="text-right p-2 border-b">Pot. Telat</th>
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
                                <td class="p-2 text-right">Rp {{ number_format($payroll->late_deduction_amount, 0, ',', '.') }}</td>
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
                                        @can('action.payroll.approve')
                                        @if($payroll->status !== 'PAID')
                                            <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.payroll.approve' : 'admin.payroll.approve', $payroll) }}">
                                                @csrf
                                                <button class="text-xs text-blue-600 hover:underline">Approve</button>
                                            </form>
                                        @endif
                                        @endcan
                                        @can('action.payroll.mark_paid')
                                        @if($payroll->status !== 'PAID')
                                            <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.payroll.mark_paid' : 'admin.payroll.mark_paid', $payroll) }}">
                                                @csrf
                                                <button class="text-xs text-emerald-600 hover:underline">Paid</button>
                                            </form>
                                        @endif
                                        @endcan
                                        @can('action.payroll.delete')
                                        @if($payroll->status !== 'PAID')
                                            <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.payroll.destroy' : 'admin.payroll.destroy', $payroll) }}" onsubmit="return confirm('Hapus payroll ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-xs text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-4 text-center text-gray-500">Belum ada data payroll.</td>
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

    <div class="mt-4 bg-white border rounded-lg p-4">
        <div class="mb-3 flex items-start justify-between gap-3">
            <h2 class="font-semibold">Rekap Absensi Bulanan per Karyawan</h2>
            <div class="flex items-center gap-3">
                <p class="text-sm text-gray-600">Ringkasan ini membantu memastikan data hadir, telat, lembur, dan review wajah sudah masuk akal sebelum payroll dibuat.</p>
                <a href="{{ route($attendanceExportRoute, $attendanceExportParams) }}"
                   class="inline-flex shrink-0 items-center rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 hover:bg-emerald-100">
                    Export Excel
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-2 border-b">Karyawan</th>
                        <th class="text-right p-2 border-b">Record</th>
                        <th class="text-right p-2 border-b">Hadir</th>
                        <th class="text-right p-2 border-b">Telat</th>
                        <th class="text-right p-2 border-b">Belum Lengkap</th>
                        <th class="text-right p-2 border-b">Lembur (m)</th>
                        <th class="text-right p-2 border-b">Face Verified</th>
                        <th class="text-right p-2 border-b">Perlu Review</th>
                        <th class="text-right p-2 border-b">Cuti</th>
                        <th class="text-right p-2 border-b">Sakit</th>
                        <th class="text-right p-2 border-b">Izin</th>
                        <th class="text-right p-2 border-b">Pot. Telat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendanceRecaps as $recap)
                        <tr class="border-b">
                            <td class="p-2">{{ $recap['employee_name'] }}</td>
                            <td class="p-2 text-right">{{ $recap['total_records'] }}</td>
                            <td class="p-2 text-right">{{ $recap['total_present'] }}</td>
                            <td class="p-2 text-right text-amber-700">{{ $recap['total_late'] }}</td>
                            <td class="p-2 text-right text-slate-700">{{ $recap['total_incomplete'] }}</td>
                            <td class="p-2 text-right">{{ $recap['overtime_minutes'] }}</td>
                            <td class="p-2 text-right text-emerald-700">{{ $recap['total_face_verified'] }}</td>
                            <td class="p-2 text-right text-amber-700">{{ $recap['total_review_required'] }}</td>
                            <td class="p-2 text-right">{{ $recap['leave_days'] }}</td>
                            <td class="p-2 text-right">{{ $recap['sick_days'] }}</td>
                            <td class="p-2 text-right">{{ $recap['permission_days'] }}</td>
                            <td class="p-2 text-right font-medium">Rp {{ number_format($recap['late_deduction_amount'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="p-4 text-center text-gray-500">Belum ada data rekap absensi untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (() => {
            const employeeInput = document.getElementById('payroll-employee-master-id');
            const periodInput = document.getElementById('payroll-period-month');
            const previewInput = document.getElementById('meal-deduction-preview');
            const previewNote = document.getElementById('meal-deduction-preview-note');
            const latePreviewInput = document.getElementById('late-deduction-preview');
            const latePreviewNote = document.getElementById('late-deduction-preview-note');
            const attendanceTotalPresent = document.getElementById('attendance-total-present');
            const attendanceTotalLate = document.getElementById('attendance-total-late');
            const attendanceTotalIncomplete = document.getElementById('attendance-total-incomplete');
            const attendanceOvertimeMinutes = document.getElementById('attendance-overtime-minutes');
            const attendanceTotalFaceVerified = document.getElementById('attendance-total-face-verified');
            const attendanceTotalReviewRequired = document.getElementById('attendance-total-review-required');
            const attendanceTotalLeaveDays = document.getElementById('attendance-total-leave-days');
            const attendanceTotalSickDays = document.getElementById('attendance-total-sick-days');
            const attendanceTotalPermissionDays = document.getElementById('attendance-total-permission-days');
            const attendanceSummaryNote = document.getElementById('attendance-summary-note');
            const previewUrl = @json(route(request()->routeIs('manager.*') ? 'manager.payroll.meal_deduction_preview' : 'admin.payroll.meal_deduction_preview'));

            const formatRupiah = (amount) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(amount || 0))}`;
            const resetAttendanceSummary = () => {
                attendanceTotalPresent.textContent = '0';
                attendanceTotalLate.textContent = '0';
                attendanceTotalIncomplete.textContent = '0';
                attendanceOvertimeMinutes.textContent = '0 m';
                attendanceTotalFaceVerified.textContent = '0';
                attendanceTotalReviewRequired.textContent = '0';
                attendanceTotalLeaveDays.textContent = '0 hari';
                attendanceTotalSickDays.textContent = '0 hari';
                attendanceTotalPermissionDays.textContent = '0 hari';
            };

            const loadPreview = async () => {
                const employeeId = employeeInput?.value;
                const periodMonth = periodInput?.value;

                if (!employeeId || !periodMonth) {
                    previewInput.value = 'Rp 0';
                    previewNote.textContent = 'Pilih periode dan petugas untuk melihat potongan makan otomatis.';
                    latePreviewInput.value = 'Rp 0';
                    latePreviewNote.textContent = 'Pilih periode dan petugas untuk melihat potongan telat otomatis.';
                    resetAttendanceSummary();
                    attendanceSummaryNote.textContent = 'Pilih periode dan petugas untuk melihat rekap absensi bulanan.';
                    return;
                }

                previewInput.value = 'Memuat...';
                previewNote.textContent = 'Menghitung transaksi makan lebih jatah...';
                latePreviewInput.value = 'Memuat...';
                latePreviewNote.textContent = 'Menghitung absensi telat yang belum dipotong...';
                attendanceSummaryNote.textContent = 'Mengambil rekap absensi bulanan...';

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
                    const lateAmount = Number(data.late_deduction_amount || 0);
                    const attendanceSummary = data.attendance_summary || {};

                    previewInput.value = formatRupiah(amount);
                    previewNote.textContent = amount > 0
                        ? 'Nominal ini akan otomatis masuk ke Pot. Makan saat payroll disimpan.'
                        : 'Belum ada kelebihan makan pada periode ini.';
                    latePreviewInput.value = formatRupiah(lateAmount);
                    latePreviewNote.textContent = lateAmount > 0
                        ? 'Nominal ini akan otomatis masuk ke Pot. Telat saat payroll disimpan.'
                        : 'Belum ada potongan telat pada periode ini.';
                    attendanceTotalPresent.textContent = attendanceSummary.total_present ?? 0;
                    attendanceTotalLate.textContent = attendanceSummary.total_late ?? 0;
                    attendanceTotalIncomplete.textContent = attendanceSummary.total_incomplete ?? 0;
                    attendanceOvertimeMinutes.textContent = `${attendanceSummary.overtime_minutes ?? 0} m`;
                    attendanceTotalFaceVerified.textContent = attendanceSummary.total_face_verified ?? 0;
                    attendanceTotalReviewRequired.textContent = attendanceSummary.total_review_required ?? 0;
                    attendanceTotalLeaveDays.textContent = `${attendanceSummary.leave_days ?? 0} hari`;
                    attendanceTotalSickDays.textContent = `${attendanceSummary.sick_days ?? 0} hari`;
                    attendanceTotalPermissionDays.textContent = `${attendanceSummary.permission_days ?? 0} hari`;
                    attendanceSummaryNote.textContent = 'Ringkasan ini diambil dari absensi periode yang dipilih dan membantu verifikasi sebelum payroll disimpan.';
                } catch (error) {
                    previewInput.value = 'Rp 0';
                    previewNote.textContent = 'Preview potongan makan gagal dimuat.';
                    latePreviewInput.value = 'Rp 0';
                    latePreviewNote.textContent = 'Preview potongan telat gagal dimuat.';
                    resetAttendanceSummary();
                    attendanceSummaryNote.textContent = 'Rekap absensi gagal dimuat.';
                }
            };

            employeeInput?.addEventListener('change', loadPreview);
            periodInput?.addEventListener('change', loadPreview);
            loadPreview();
        })();
    </script>
@endsection
