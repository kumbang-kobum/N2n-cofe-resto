@extends('layouts.dashboard')

@section('content')
@php
    $isManager = request()->routeIs('manager.*');
    $storeRoute = $isManager ? 'manager.leave_requests.store' : 'admin.leave_requests.store';
    $approveRoute = $isManager ? 'manager.leave_requests.approve' : 'admin.leave_requests.approve';
    $rejectRoute = $isManager ? 'manager.leave_requests.reject' : 'admin.leave_requests.reject';
@endphp

<div class="mb-4 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-semibold text-slate-900">Izin / Cuti / Sakit</h1>
        <p class="text-sm text-slate-600">Pengajuan dan approval status kehadiran non-hadir untuk payroll dan rekap absensi.</p>
    </div>
</div>

@if(session('status'))
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
@endif

<div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
    <div class="panel-section">
        <h2 class="mb-3 font-semibold text-slate-900">Ajukan</h2>
        <form method="POST" action="{{ route($storeRoute) }}" class="space-y-3">
            @csrf
            <div>
                <label class="mb-1 block text-xs text-slate-600">Karyawan</label>
                <select name="employee_id" class="dashboard-input" required>
                    <option value="">Pilih</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-600">Jenis</label>
                <select name="type" class="dashboard-input">
                    <option value="PERMISSION">Izin</option>
                    <option value="SICK">Sakit</option>
                    <option value="LEAVE">Cuti</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Mulai</label>
                    <input type="date" name="start_date" class="dashboard-input" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Selesai</label>
                    <input type="date" name="end_date" class="dashboard-input" required>
                </div>
            </div>
            <div>
                <textarea name="reason" rows="3" class="dashboard-input" placeholder="Alasan / catatan"></textarea>
            </div>
            <button class="btn-primary w-full">Simpan Pengajuan</button>
        </form>
    </div>

    <div class="panel-section xl:col-span-2">
        <form method="GET" class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs text-slate-600">Dari</label>
                <input type="date" name="from" value="{{ $from }}" class="dashboard-input">
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-600">Sampai</label>
                <input type="date" name="to" value="{{ $to }}" class="dashboard-input">
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-600">Status</label>
                <select name="status" class="dashboard-input">
                    <option value="">Semua</option>
                    <option value="PENDING" @selected($status === 'PENDING')>Pending</option>
                    <option value="APPROVED" @selected($status === 'APPROVED')>Approved</option>
                    <option value="REJECTED" @selected($status === 'REJECTED')>Rejected</option>
                </select>
            </div>
            <div class="flex items-end">
                <button class="btn-primary w-full">Filter</button>
            </div>
        </form>

        <div class="table-shell shadow-none">
        <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
            <table class="w-full text-sm">
                <thead class="table-head">
                    <tr>
                        <th class="px-3 py-2 text-left">Karyawan</th>
                        <th class="px-3 py-2 text-left">Jenis</th>
                        <th class="px-3 py-2 text-left">Periode</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ $leave->employee?->name }}</td>
                            <td class="px-3 py-2">{{ $leave->type }}</td>
                            <td class="px-3 py-2">{{ $leave->start_date?->format('d/m/Y') }} - {{ $leave->end_date?->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded px-2 py-1 text-xs {{ $leave->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : ($leave->status === 'REJECTED' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $leave->status }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                @if($leave->status === 'PENDING')
                                    @can('action.leave_requests.approve')
                                        <form method="POST" action="{{ route($approveRoute, $leave) }}" class="inline">
                                            @csrf
                                            <button class="text-xs text-blue-600 hover:underline">Approve</button>
                                        </form>
                                    @endcan
                                    @can('action.leave_requests.reject')
                                        <form method="POST" action="{{ route($rejectRoute, $leave) }}" class="ml-2 inline">
                                            @csrf
                                            <button class="text-xs text-red-600 hover:underline">Reject</button>
                                        </form>
                                    @endcan
                                    @cannot('action.leave_requests.approve')
                                        @cannot('action.leave_requests.reject')
                                            <span class="text-xs text-gray-400">Menunggu reviewer</span>
                                        @endcannot
                                    @endcannot
                                @else
                                    <span class="text-xs text-gray-400">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada pengajuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>

        <div class="mt-4">
            {{ $leaves->links() }}
        </div>
    </div>
</div>
@endsection
