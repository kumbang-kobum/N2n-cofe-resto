@extends('layouts.dashboard')
@section('content')
<div class="mb-5">
    <h1 class="text-xl font-semibold text-slate-900">Edit Rule Keterlambatan</h1>
    <p class="mt-1 text-sm text-slate-600">Perbarui rule agar perhitungan telat tetap konsisten tanpa mengubah histori absensi lama.</p>
</div>

<form method="POST" action="{{ route($routePrefix . '.attendance_late_rules.update', $rule) }}" class="panel-section max-w-3xl space-y-4">
    @csrf
    @method('PUT')
    @include('admin.attendance.late_rules._form')
    <div class="flex gap-2">
        <a href="{{ route($routePrefix . '.attendance_late_rules.index') }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary">Update</button>
    </div>
</form>
@endsection
