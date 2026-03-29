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
        <h1 class="text-xl font-semibold">Izin / Cuti / Sakit</h1>
        <p class="text-sm text-gray-600">Pengajuan dan approval status kehadiran non-hadir.</p>
    </div>
</div>

@if(session('status'))
    <div class="mb-3 rounded bg-green-100 px-3 py-2 text-sm text-green-700">{{ session('status') }}</div>
@endif

<div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
    <div class="rounded-lg border bg-white p-4">
        <h2 class="mb-3 font-semibold">Ajukan</h2>
        <form method="POST" action="{{ route($storeRoute) }}" class="space-y-3">
            @csrf
            <div>
                <label class="mb-1 block text-xs text-gray-600">Karyawan</label>
                <select name="employee_id" class="w-full rounded border px-3 py-2 text-sm" required>
                    <option value="">Pilih</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-600">Jenis</label>
                <select name="type" class="w-full rounded border px-3 py-2 text-sm">
                    <option value="PERMISSION">Izin</option>
                    <option value="SICK">Sakit</option>
                    <option value="LEAVE">Cuti</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="mb-1 block text-xs text-gray-600">Mulai</label>
                    <input type="date" name="start_date" class="w-full rounded border px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-gray-600">Selesai</label>
                    <input type="date" name="end_date" class="w-full rounded border px-3 py-2 text-sm" required>
                </div>
            </div>
            <div>
                <textarea name="reason" rows="3" class="w-full rounded border px-3 py-2 text-sm" placeholder="Alasan / catatan"></textarea>
            </div>
            <button class="w-full rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white">Simpan Pengajuan</button>
        </form>
    </div>

    <div class="rounded-lg border bg-white p-4 xl:col-span-2">
        <form method="GET" class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs text-gray-600">Dari</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full rounded border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-600">Sampai</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full rounded border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-600">Status</label>
                <select name="status" class="w-full rounded border px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="PENDING" @selected($status === 'PENDING')>Pending</option>
                    <option value="APPROVED" @selected($status === 'APPROVED')>Approved</option>
                    <option value="REJECTED" @selected($status === 'REJECTED')>Rejected</option>
                </select>
            </div>
            <div class="flex items-end">
                <button class="w-full rounded bg-slate-700 px-3 py-2 text-sm text-white">Filter</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
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

        <div class="mt-3">
            {{ $leaves->links() }}
        </div>
    </div>
</div>
@endsection
