@extends('layouts.dashboard')
@section('content')
<div class="mb-5">
    <h1 class="text-xl font-semibold text-slate-900">Tambah Shift</h1>
    <p class="mt-1 text-sm text-slate-600">Buat template jam kerja baru untuk roster, absensi, dan payroll.</p>
</div>

<form method="POST" action="{{ route($routePrefix . '.attendance_shifts.store') }}" class="panel-section max-w-3xl space-y-4">
    @csrf
    @include('admin.attendance.shifts._form')
    <div class="flex gap-2">
        <a href="{{ route($routePrefix . '.attendance_shifts.index') }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary">Simpan</button>
    </div>
</form>
@endsection
