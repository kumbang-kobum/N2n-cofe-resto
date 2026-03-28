@extends('layouts.dashboard')

@php
    $routePrefix = request()->routeIs('manager.*') ? 'manager' : 'admin';
    $monthLabel = $monthStart->translatedFormat('F Y');
@endphp

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Roster Bulanan Shift</h1>
            <p class="text-sm text-slate-600">Atur shift dan libur langsung per tanggal. Klik sel pada kalender roster untuk memilih shift, libur, atau mengikuti shift default karyawan.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
            Periode aktif:
            <span class="font-semibold text-slate-900">{{ $monthLabel }}</span>
        </div>
    </div>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-4">
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
            <div class="md:col-span-2 flex items-end gap-2">
                <button class="inline-flex rounded-2xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-900">
                    Tampilkan Roster
                </button>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500">
                    Warna: <span class="font-semibold text-blue-700">shift</span>, <span class="font-semibold text-slate-700">default</span>, <span class="font-semibold text-rose-700">libur</span>, <span class="font-semibold text-emerald-700">cuti/sakit/izin</span>
                </div>
            </div>
        </form>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Bulk Action Roster</h2>
            <p class="text-sm text-slate-500">Gunakan ini untuk mengisi shift/libur banyak tanggal sekaligus. Cocok untuk roster 1 minggu atau 1 bulan penuh.</p>
        </div>

        <form method="POST" action="{{ route($routePrefix . '.attendance_schedules.bulk_store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Karyawan</label>
                <select name="employee_ids[]" multiple size="6" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
                <div class="mt-1 text-[11px] text-slate-500">Pilih satu atau beberapa karyawan sekaligus.</div>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $monthStart->toDateString() }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $monthEnd->toDateString() }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Shift</label>
                    <select name="shift_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                        <option value="">Ikuti shift default</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    <input type="checkbox" name="is_day_off" value="1" class="rounded border-slate-300">
                    Jadikan semua tanggal sebagai hari libur
                </label>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Terapkan pada Hari</label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
                    @foreach([
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                    ] as $dayValue => $dayLabel)
                        <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                            <input type="checkbox" name="weekdays[]" value="{{ $dayValue }}" class="rounded border-slate-300" checked>
                            {{ $dayLabel }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_auto]">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Catatan</label>
                    <textarea name="note" rows="2" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" placeholder="Catatan bulk roster, misalnya shift Ramadan atau pola minggu pertama."></textarea>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <input type="checkbox" name="overwrite_existing" value="1" class="rounded border-slate-300">
                        Timpa jadwal yang sudah ada
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Terapkan Bulk Action
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Copy Roster Otomatis</h2>
            <p class="text-sm text-slate-500">Salin pola jadwal dari minggu lalu atau bulan lalu ke bulan aktif. Cocok kalau pola kerja relatif berulang.</p>
        </div>

        <form method="POST" action="{{ route($routePrefix . '.attendance_schedules.copy') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">

            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Sumber Copy</label>
                    <select name="source_type" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                        <option value="previous_week">Pola Minggu Lalu</option>
                        <option value="previous_month">Pola Bulan Lalu</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Batasi Karyawan</label>
                    <select name="employee_ids[]" multiple size="5" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-1 text-[11px] text-slate-500">Kosongkan jika ingin menyalin untuk semua karyawan yang punya jadwal sumber.</div>
                </div>
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <input type="checkbox" name="overwrite_existing" value="1" class="rounded border-slate-300">
                Timpa jadwal yang sudah ada di bulan aktif
            </label>

            <div class="flex justify-end">
                <button type="submit" class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Salin Roster
                </button>
            </div>
        </form>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Kalender Roster</h2>
                <p class="text-sm text-slate-500">Klik salah satu sel untuk mengubah jadwal. Data izin, cuti, dan sakit yang sudah disetujui tetap terlihat sebagai referensi.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] border-separate border-spacing-0 text-sm">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-20 min-w-[220px] border-b border-r border-slate-200 bg-white px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            Karyawan
                        </th>
                        @foreach($days as $day)
                            <th class="border-b border-slate-200 bg-slate-50 px-2 py-3 text-center">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">{{ $day->translatedFormat('D') }}</div>
                                <div class="mt-1 text-base font-semibold text-slate-900">{{ $day->format('d') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td class="sticky left-0 z-10 border-b border-r border-slate-200 bg-white px-4 py-3 align-top">
                                <div class="font-semibold text-slate-900">{{ $employee->name }}</div>
                                <div class="mt-1 text-xs text-slate-500">Shift default: {{ $employee->defaultShift?->name ?? 'Belum diatur' }}</div>
                            </td>
                            @foreach($days as $day)
                                @php
                                    $dateKey = $day->format('Y-m-d');
                                    $schedule = $scheduleMap->get($employee->id . '|' . $dateKey);
                                    $leave = $leaveMap[$employee->id . '|' . $dateKey] ?? null;
                                    $label = $schedule?->is_day_off
                                        ? 'Libur'
                                        : ($schedule?->shift?->name ?? ($employee->defaultShift?->name ? 'Default' : '—'));
                                    $tone = $leave
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : ($schedule?->is_day_off
                                            ? 'border-rose-200 bg-rose-50 text-rose-700'
                                            : ($schedule?->shift
                                                ? 'border-blue-200 bg-blue-50 text-blue-700'
                                                : 'border-slate-200 bg-slate-50 text-slate-700'));
                                    $leaveLabel = match($leave['type'] ?? null) {
                                        'LEAVE', 'CUTI' => 'Cuti',
                                        'SICK', 'SAKIT' => 'Sakit',
                                        'PERMISSION', 'IZIN' => 'Izin',
                                        default => null,
                                    };
                                @endphp
                                <td class="border-b border-slate-200 px-1 py-2 text-center">
                                    <button
                                        type="button"
                                        class="roster-cell flex w-full min-w-[88px] flex-col items-center rounded-2xl border px-2 py-2 text-xs font-semibold transition {{ $leave ? 'cursor-not-allowed opacity-80' : 'hover:scale-[1.02]' }} {{ $tone }}"
                                        data-employee-id="{{ $employee->id }}"
                                        data-employee-name="{{ $employee->name }}"
                                        data-date="{{ $dateKey }}"
                                        data-shift-id="{{ $schedule?->shift_id }}"
                                        data-is-day-off="{{ $schedule?->is_day_off ? '1' : '0' }}"
                                        data-note="{{ $schedule?->note }}"
                                        data-leave-label="{{ $leaveLabel }}"
                                        data-locked="{{ $leave ? '1' : '0' }}"
                                        @disabled($leave)
                                    >
                                        <span>{{ $label }}</span>
                                        @if($leaveLabel)
                                            <span class="mt-1 rounded-full bg-white/70 px-2 py-0.5 text-[10px] uppercase tracking-[0.12em]">{{ $leaveLabel }}</span>
                                        @elseif($schedule?->shift)
                                            <span class="mt-1 text-[10px] text-current/80">{{ \Carbon\Carbon::parse($schedule->shift->start_time)->format('H:i') }}</span>
                                        @endif
                                    </button>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 1 + $days->count() }}" class="px-4 py-10 text-center text-sm text-slate-500">
                                Belum ada karyawan aktif untuk ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="roster-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 px-4">
    <div class="w-full max-w-lg rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_28px_80px_rgba(15,23,42,0.22)]">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Set Jadwal</div>
                <h3 class="mt-1 text-xl font-semibold text-slate-900" id="roster-modal-title">Jadwal Karyawan</h3>
                <p class="mt-1 text-sm text-slate-500" id="roster-modal-subtitle">Pilih shift atau tentukan hari libur.</p>
            </div>
            <button type="button" id="roster-modal-close" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Tutup</button>
        </div>

        <form method="POST" action="{{ route($routePrefix . '.attendance_schedules.store') }}" class="mt-5 space-y-4">
            @csrf
            <input type="hidden" name="employee_id" id="roster-employee-id">
            <input type="hidden" name="schedule_date" id="roster-schedule-date">

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Shift</label>
                <select name="shift_id" id="roster-shift-id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Ikuti shift default</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})</option>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <input type="checkbox" name="is_day_off" value="1" id="roster-is-day-off" class="rounded border-slate-300">
                Jadikan hari libur
            </label>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Catatan</label>
                <textarea name="note" id="roster-note" rows="3" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" placeholder="Contoh: tukar shift, request karyawan, atau catatan operasional."></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Simpan Jadwal
                </button>
                <button type="button" id="roster-modal-cancel" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const modal = document.getElementById('roster-modal');
        const closeButton = document.getElementById('roster-modal-close');
        const cancelButton = document.getElementById('roster-modal-cancel');
        const title = document.getElementById('roster-modal-title');
        const subtitle = document.getElementById('roster-modal-subtitle');
        const employeeIdInput = document.getElementById('roster-employee-id');
        const scheduleDateInput = document.getElementById('roster-schedule-date');
        const shiftIdInput = document.getElementById('roster-shift-id');
        const isDayOffInput = document.getElementById('roster-is-day-off');
        const noteInput = document.getElementById('roster-note');
        const modalForm = modal?.querySelector('form');

        const openModal = (button) => {
            if (button.dataset.locked === '1') {
                return;
            }

            title.textContent = `Jadwal ${button.dataset.employeeName}`;
            subtitle.textContent = `Tanggal ${button.dataset.date}`;
            employeeIdInput.value = button.dataset.employeeId || '';
            scheduleDateInput.value = button.dataset.date || '';
            shiftIdInput.value = button.dataset.shiftId || '';
            isDayOffInput.checked = button.dataset.isDayOff === '1';
            noteInput.value = button.dataset.note || '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        document.querySelectorAll('.roster-cell').forEach((button) => {
            button.addEventListener('click', () => openModal(button));
        });

        closeButton?.addEventListener('click', closeModal);
        cancelButton?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    })();
</script>
@endpush
