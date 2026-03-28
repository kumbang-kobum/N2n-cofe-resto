@extends('layouts.dashboard')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Rekap Absensi Bulanan</h1>
            <p class="text-sm text-slate-600">Laporan khusus absensi bulanan untuk memantau kehadiran, telat, lembur, serta izin/cuti/sakit dalam satu periode.</p>
        </div>
        <a href="{{ route(request()->routeIs('manager.*') ? 'manager.reports.attendance.export' : 'admin.reports.attendance.export', ['month' => $month, 'employee_id' => $employeeId]) }}"
           class="inline-flex items-center rounded bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">
            Export Excel
        </a>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Bulan</label>
                <input type="month" name="month" value="{{ $month }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Karyawan</label>
                <select name="employee_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Semua karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected($employeeId === $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button class="w-full rounded-2xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-900">
                    Tampilkan Rekap
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3">
            <h2 class="text-lg font-semibold text-slate-900">Rekap per Karyawan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="p-2 text-left border-b">Karyawan</th>
                        <th class="p-2 text-right border-b">Record</th>
                        <th class="p-2 text-right border-b">Hadir</th>
                        <th class="p-2 text-right border-b">Telat</th>
                        <th class="p-2 text-right border-b">Belum Lengkap</th>
                        <th class="p-2 text-right border-b">Lembur (m)</th>
                        <th class="p-2 text-right border-b">Face Verified</th>
                        <th class="p-2 text-right border-b">Perlu Review</th>
                        <th class="p-2 text-right border-b">Cuti</th>
                        <th class="p-2 text-right border-b">Sakit</th>
                        <th class="p-2 text-right border-b">Izin</th>
                        <th class="p-2 text-right border-b">Pot. Telat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeeRecaps as $recap)
                        <tr class="border-b">
                            <td class="p-2">{{ $recap['employee_name'] }}</td>
                            <td class="p-2 text-right">{{ $recap['total_records'] }}</td>
                            <td class="p-2 text-right">{{ $recap['total_present'] }}</td>
                            <td class="p-2 text-right text-amber-700">{{ $recap['total_late'] }}</td>
                            <td class="p-2 text-right">{{ $recap['total_incomplete'] }}</td>
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
                            <td colspan="12" class="p-4 text-center text-slate-500">Belum ada data rekap untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3">
            <h2 class="text-lg font-semibold text-slate-900">Rekap Harian</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="p-2 text-left border-b">Tanggal</th>
                        <th class="p-2 text-right border-b">Record</th>
                        <th class="p-2 text-right border-b">Hadir</th>
                        <th class="p-2 text-right border-b">Telat</th>
                        <th class="p-2 text-right border-b">Belum Lengkap</th>
                        <th class="p-2 text-right border-b">Lembur (m)</th>
                        <th class="p-2 text-right border-b">Pot. Telat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyRecaps as $daily)
                        <tr class="border-b">
                            <td class="p-2">{{ \Carbon\Carbon::parse($daily->attendance_day)->format('d/m/Y') }}</td>
                            <td class="p-2 text-right">{{ $daily->total_records }}</td>
                            <td class="p-2 text-right">{{ $daily->total_present }}</td>
                            <td class="p-2 text-right text-amber-700">{{ $daily->total_late }}</td>
                            <td class="p-2 text-right">{{ $daily->total_incomplete }}</td>
                            <td class="p-2 text-right">{{ $daily->overtime_minutes }}</td>
                            <td class="p-2 text-right font-medium">Rp {{ number_format($daily->late_deduction_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4 text-center text-slate-500">Belum ada data harian untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
