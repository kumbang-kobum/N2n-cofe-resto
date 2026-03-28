@extends('layouts.dashboard')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Absensi Karyawan</h1>
            <p class="text-sm text-slate-600">Pantau jam masuk, jam pulang, selfie bukti, dan status verifikasi agar payroll lebih akurat dan mudah diaudit.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route(request()->routeIs('manager.*') ? 'manager.attendances.review' : 'admin.attendances.review') }}" class="inline-flex rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                Review Wajah
            </a>
            <a href="{{ route('attendance.kiosk') }}" class="inline-flex rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                Buka Kiosk Absensi
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Hadir Tepat</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['present'] }}</div>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-500">Telat</div>
            <div class="mt-2 text-2xl font-semibold text-amber-700">{{ $summary['late'] }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Belum Lengkap</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['incomplete'] }}</div>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Potongan Telat</div>
            <div class="mt-2 text-2xl font-semibold text-rose-700">Rp {{ number_format($summary['late_deduction_amount'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Input / Koreksi Absensi</h2>
            <p class="mt-1 text-sm text-slate-500">Gunakan form ini untuk koreksi manual atau input absensi yang belum sempat masuk dari kiosk.</p>

            <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.attendances.store' : 'admin.attendances.store') }}" class="mt-5 space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Karyawan</label>
                    <select name="employee_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" required>
                        <option value="">Pilih</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Tanggal</label>
                    <input type="date" name="attendance_date" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" value="{{ now()->toDateString() }}" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Clock In</label>
                    <input type="datetime-local" name="clock_in_at" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Clock Out</label>
                    <input type="datetime-local" name="clock_out_at" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Status Verifikasi</label>
                    <select name="verification_status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                        <option value="MANUAL">Manual</option>
                        <option value="PHOTO_ONLY">Selfie Foto</option>
                        <option value="FACE_VERIFIED">Face Verified</option>
                        <option value="REVIEW_REQUIRED">Perlu Review</option>
                    </select>
                </div>
                <div>
                    <textarea name="note" rows="3" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" placeholder="Catatan absensi"></textarea>
                </div>
                <button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Simpan Absensi
                </button>
            </form>
        </section>

        <section class="xl:col-span-2 rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Dari</label>
                    <input type="date" name="from" value="{{ $from }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Karyawan</label>
                    <select name="employee_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                        <option value="">Semua</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected($employeeId === $employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-2xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-900">
                        Filter
                    </button>
                </div>
            </form>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[980px] text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-3 py-3 text-left">Tanggal</th>
                            <th class="px-3 py-3 text-left">Karyawan</th>
                            <th class="px-3 py-3 text-left">Shift</th>
                            <th class="px-3 py-3 text-left">Masuk / Pulang</th>
                            <th class="px-3 py-3 text-left">Riwayat Selfie</th>
                            <th class="px-3 py-3 text-right">Telat</th>
                            <th class="px-3 py-3 text-right">Lembur</th>
                            <th class="px-3 py-3 text-right">Potongan</th>
                            <th class="px-3 py-3 text-left">Verifikasi</th>
                            <th class="px-3 py-3 text-right">Skor</th>
                            <th class="px-3 py-3 text-left">Review</th>
                            <th class="px-3 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr class="border-t border-slate-100 align-top">
                                <td class="px-3 py-3 text-slate-700">{{ $attendance->attendance_date?->format('d/m/Y') }}</td>
                                <td class="px-3 py-3">
                                    <div class="font-medium text-slate-900">{{ $attendance->employee?->name }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $attendance->employee?->employee_code ?? 'Tanpa kode' }}</div>
                                </td>
                                <td class="px-3 py-3 text-slate-700">{{ $attendance->shift?->name ?? $attendance->employee?->defaultShift?->name ?? '-' }}</td>
                                <td class="px-3 py-3 text-xs text-slate-600">
                                    {{ optional($attendance->clock_in_at)->format('H:i') ?? '-' }} / {{ optional($attendance->clock_out_at)->format('H:i') ?? '-' }}
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex gap-2">
                                        @foreach ([
                                            'Masuk' => $attendance->clock_in_photo_path,
                                            'Pulang' => $attendance->clock_out_photo_path,
                                        ] as $label => $photoPath)
                                            @if($photoPath)
                                                <a href="{{ asset('storage/' . $photoPath) }}" target="_blank" class="group block">
                                                    <img src="{{ asset('storage/' . $photoPath) }}" alt="Selfie {{ strtolower($label) }}" class="h-14 w-14 rounded-2xl border border-slate-200 object-cover shadow-sm transition group-hover:scale-[1.03]">
                                                    <div class="mt-1 text-center text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $label }}</div>
                                                </a>
                                            @else
                                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                                                    {{ $label }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-right text-slate-700">{{ $attendance->late_minutes }} m</td>
                                <td class="px-3 py-3 text-right text-slate-700">{{ $attendance->overtime_minutes }} m</td>
                                <td class="px-3 py-3 text-right font-medium text-rose-600">Rp {{ number_format($attendance->late_deduction_amount, 0, ',', '.') }}</td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em]
                                        {{ $attendance->verification_status === 'FACE_VERIFIED' ? 'bg-emerald-50 text-emerald-700' : ($attendance->verification_status === 'REVIEW_REQUIRED' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                        {{ str_replace('_', ' ', $attendance->verification_status ?? 'MANUAL') }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right text-slate-700">
                                    {{ $attendance->verification_score !== null ? number_format($attendance->verification_score, 1, ',', '.') . '%' : '-' }}
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-600">
                                    @if($attendance->reviewed_at)
                                        <div class="font-medium text-slate-900">{{ $attendance->reviewer?->name ?? '-' }}</div>
                                        <div class="mt-1">{{ $attendance->reviewed_at?->format('d/m H:i') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-700">
                                        {{ $attendance->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-3 py-8 text-center text-sm text-slate-500">
                                    Belum ada data absensi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $attendances->links() }}
            </div>
        </section>
    </div>
</div>
@endsection
