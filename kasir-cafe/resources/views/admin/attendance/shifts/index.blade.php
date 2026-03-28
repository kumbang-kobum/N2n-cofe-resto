@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-semibold">Master Shift</h1>
        <p class="text-sm text-gray-600">Jam kerja, toleransi telat, dan dasar hitung lembur.</p>
    </div>
    <a href="{{ route(request()->routeIs('manager.*') ? 'manager.attendance_shifts.create' : 'admin.attendance_shifts.create') }}" class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Tambah Shift</a>
</div>

@if(session('status'))
<div class="mb-3 rounded bg-green-100 px-3 py-2 text-sm text-green-700">{{ session('status') }}</div>
@endif

<form method="GET" class="mb-3">
    <input type="text" name="q" value="{{ $q }}" class="w-full md:w-80 border rounded px-3 py-2 text-sm" placeholder="Cari nama shift">
</form>

<div class="overflow-x-auto rounded-lg border bg-white">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
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
                    <td class="px-3 py-2 font-medium">{{ $shift->name }}</td>
                    <td class="px-3 py-2">{{ $shift->start_time }} - {{ $shift->end_time }}</td>
                    <td class="px-3 py-2 text-right">{{ $shift->late_tolerance_minutes }} menit</td>
                    <td class="px-3 py-2 text-right">{{ $shift->overtime_after_minutes }} menit</td>
                    <td class="px-3 py-2 text-center">
                        <span class="rounded px-2 py-1 text-xs {{ $shift->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $shift->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route(request()->routeIs('manager.*') ? 'manager.attendance_shifts.edit' : 'admin.attendance_shifts.edit', $shift) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.attendance_shifts.destroy' : 'admin.attendance_shifts.destroy', $shift) }}" class="inline" onsubmit="return confirm('Hapus shift ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-2 text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada shift.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $shifts->links() }}</div>
@endsection
