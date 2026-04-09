@extends('layouts.dashboard')

@section('content')
@php
    $routePrefix = request()->routeIs('manager.*') ? 'manager' : 'admin';
@endphp

<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold text-slate-900">Master Shift</h1>
        <p class="text-sm text-slate-600">Jam kerja, toleransi telat, dan dasar hitung lembur untuk jadwal absensi.</p>
    </div>
    <a href="{{ route($routePrefix . '.attendance_shifts.create') }}" class="btn-primary">+ Tambah Shift</a>
</div>

@if(session('status'))
<div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
@endif

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
    <div class="stat-card">
        <div class="stat-label">Total Shift</div>
        <div class="stat-value">{{ number_format($shifts->total(), 0, ',', '.') }}</div>
        <div class="stat-meta">Master shift aktif dan nonaktif.</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pencarian</div>
        <div class="stat-value text-2xl">{{ $q ?: 'Semua' }}</div>
        <div class="stat-meta">Memudahkan audit shift per nama.</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Halaman</div>
        <div class="stat-value">{{ $shifts->currentPage() }}</div>
        <div class="stat-meta">Tetap ringan untuk data yang terus bertambah.</div>
    </div>
</div>

<div class="panel-section mb-4">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ $q }}" class="dashboard-input max-w-sm" placeholder="Cari nama shift">
        <button type="submit" class="btn-primary">Cari</button>
        @if($q)
            <a href="{{ route($routePrefix . '.attendance_shifts.index') }}" class="btn-secondary">Reset</a>
        @endif
    </form>
</div>

<div class="table-shell">
    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
    <table class="w-full text-sm">
        <thead class="table-head">
            <tr>
                <th class="px-3 py-2 text-left">Shift</th>
                <th class="px-3 py-2 text-left">Jam</th>
                <th class="px-3 py-2 text-right">Toleransi</th>
                <th class="px-3 py-2 text-right">Lembur</th>
                <th class="px-3 py-2 text-center">Status</th>
                <th class="px-3 py-2 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shifts as $shift)
                <tr class="border-t">
                    <td class="px-3 py-2 font-medium text-slate-900">{{ $shift->name }}</td>
                    <td class="px-3 py-2">{{ $shift->start_time }} - {{ $shift->end_time }}</td>
                    <td class="px-3 py-2 text-right">{{ $shift->late_tolerance_minutes }} menit</td>
                    <td class="px-3 py-2 text-right">{{ $shift->overtime_after_minutes }} menit</td>
                    <td class="px-3 py-2 text-center">
                        <span class="rounded px-2 py-1 text-xs {{ $shift->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $shift->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route($routePrefix . '.attendance_shifts.edit', $shift) }}" class="text-xs font-medium text-blue-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route($routePrefix . '.attendance_shifts.destroy', $shift) }}" class="inline" onsubmit="return confirm('Hapus shift ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-2 text-xs font-medium text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada shift.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $shifts->links() }}</div>
@endsection
