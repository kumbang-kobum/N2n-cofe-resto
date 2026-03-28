@extends('layouts.dashboard')
@section('content')
<h1 class="mb-4 text-xl font-semibold">Edit Shift</h1>
<form method="POST" action="{{ route($routePrefix . '.attendance_shifts.update', $shift) }}" class="space-y-4 rounded-lg border bg-white p-4 max-w-3xl">
    @csrf
    @method('PUT')
    @include('admin.attendance.shifts._form')
    <div class="flex gap-2">
        <a href="{{ route($routePrefix . '.attendance_shifts.index') }}" class="rounded border px-3 py-2 text-sm">Batal</a>
        <button type="submit" class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white">Update</button>
    </div>
</form>
@endsection
