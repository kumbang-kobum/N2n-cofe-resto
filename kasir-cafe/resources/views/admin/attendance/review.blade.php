@extends('layouts.dashboard')

@section('content')
@php
    $isManager = request()->routeIs('manager.*');
    $indexRoute = $isManager ? 'manager.attendances.index' : 'admin.attendances.index';
    $reviewUpdateRoute = $isManager ? 'manager.attendances.review_update' : 'admin.attendances.review_update';
@endphp

<div class="space-y-4">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Review Wajah Absensi</h1>
            <p class="text-sm text-slate-600">Antrian ini membantu admin dan manager meninjau selfie absensi yang perlu dicek manual sebelum benar-benar dianggap valid.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route($indexRoute) }}" class="inline-flex rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Kembali ke Absensi
            </a>
            <a href="{{ route('attendance.kiosk') }}" class="inline-flex rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                Buka Kiosk
            </a>
        </div>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Filter Status</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm md:w-64">
                    @foreach (['REVIEW_REQUIRED' => 'Perlu Review', 'FACE_VERIFIED' => 'Face Verified', 'PHOTO_ONLY' => 'Photo Only', 'ALL' => 'Semua'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="inline-flex rounded-2xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-900">
                Terapkan Filter
            </button>
        </form>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        @forelse($attendances as $attendance)
            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            {{ $attendance->attendance_date?->format('d/m/Y') }} · {{ $attendance->shift?->name ?? $attendance->employee?->defaultShift?->name ?? 'Tanpa Shift' }}
                        </div>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">{{ $attendance->employee?->name }}</h2>
                        <div class="mt-1 text-sm text-slate-500">
                            Masuk {{ optional($attendance->clock_in_at)->format('H:i') ?? '-' }} ·
                            Pulang {{ optional($attendance->clock_out_at)->format('H:i') ?? '-' }}
                        </div>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]
                        {{ $attendance->verification_status === 'FACE_VERIFIED' ? 'bg-emerald-50 text-emerald-700' : ($attendance->verification_status === 'REVIEW_REQUIRED' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                        {{ str_replace('_', ' ', $attendance->verification_status ?? 'PHOTO_ONLY') }}
                    </span>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div>
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Foto Referensi</div>
                        @if($attendance->employee?->face_reference_path)
                            <img src="{{ asset('storage/' . $attendance->employee->face_reference_path) }}" alt="Foto referensi" class="h-44 w-full rounded-2xl border border-slate-200 object-cover">
                        @else
                            <div class="flex h-44 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm text-slate-500">
                                Karyawan ini belum memiliki foto referensi.
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Selfie Masuk</div>
                        @if($attendance->clock_in_photo_path)
                            <a href="{{ asset('storage/' . $attendance->clock_in_photo_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $attendance->clock_in_photo_path) }}" alt="Selfie masuk" class="h-44 w-full rounded-2xl border border-slate-200 object-cover transition hover:scale-[1.01]">
                            </a>
                        @else
                            <div class="flex h-44 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm text-slate-500">
                                Belum ada selfie masuk.
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Selfie Pulang</div>
                        @if($attendance->clock_out_photo_path)
                            <a href="{{ asset('storage/' . $attendance->clock_out_photo_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $attendance->clock_out_photo_path) }}" alt="Selfie pulang" class="h-44 w-full rounded-2xl border border-slate-200 object-cover transition hover:scale-[1.01]">
                            </a>
                        @else
                            <div class="flex h-44 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm text-slate-500">
                                Belum ada selfie pulang.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Telat</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">{{ $attendance->late_minutes }} menit</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Lembur</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">{{ $attendance->overtime_minutes }} menit</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Potongan Telat</div>
                        <div class="mt-1 text-lg font-semibold text-rose-600">Rp {{ number_format($attendance->late_deduction_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 md:col-span-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Skor Verifikasi Ringan</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">
                            {{ $attendance->verification_score !== null ? number_format($attendance->verification_score, 1, ',', '.') . '%' : 'Belum ada skor' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 md:col-span-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Audit Review</div>
                        <div class="mt-1 text-sm text-slate-700">
                            Reviewer:
                            <span class="font-semibold text-slate-900">{{ $attendance->reviewer?->name ?? '-' }}</span>
                        </div>
                        <div class="mt-1 text-sm text-slate-700">
                            Waktu Review:
                            <span class="font-semibold text-slate-900">{{ $attendance->reviewed_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="mt-1 text-sm text-slate-700">
                            Catatan Review:
                            <span class="font-semibold text-slate-900">{{ $attendance->review_note ?: '-' }}</span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route($reviewUpdateRoute, $attendance) }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="verification_score" value="{{ $attendance->verification_score }}">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Keputusan Review</label>
                        <select name="verification_status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                            <option value="FACE_VERIFIED" @selected($attendance->verification_status === 'FACE_VERIFIED')>Valid - Face Verified</option>
                            <option value="REVIEW_REQUIRED" @selected($attendance->verification_status === 'REVIEW_REQUIRED')>Masih Perlu Review</option>
                            <option value="PHOTO_ONLY" @selected($attendance->verification_status === 'PHOTO_ONLY')>Simpan sebagai Photo Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Catatan Review</label>
                        <textarea name="review_note" rows="3" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm" placeholder="Misalnya: wajah cocok, cahaya terlalu redup, atau perlu cek manual lagi."></textarea>
                    </div>
                    @if($attendance->note)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <div class="mb-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Catatan Tersimpan</div>
                            {{ $attendance->note }}
                        </div>
                    @endif
                    <div class="flex flex-wrap gap-2">
                        <button class="rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                            Simpan Review
                        </button>
                    </div>
                </form>
            </section>
        @empty
            <div class="xl:col-span-2 rounded-[28px] border border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-500 shadow-sm">
                Tidak ada data review wajah untuk filter saat ini.
            </div>
        @endforelse
    </div>

    <div>
        {{ $attendances->links() }}
    </div>
</div>
@endsection
