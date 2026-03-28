@extends('layouts.dashboard')
@section('content')
<h1 class="mb-4 text-xl font-semibold">Tambah Rule Keterlambatan</h1>
<form method="POST" action="{{ route($routePrefix . '.attendance_late_rules.store') }}" class="space-y-4 rounded-lg border bg-white p-4 max-w-3xl">@csrf @include('admin.attendance.late_rules._form')<div class="flex gap-2"><a href="{{ route($routePrefix . '.attendance_late_rules.index') }}" class="rounded border px-3 py-2 text-sm">Batal</a><button type="submit" class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white">Simpan</button></div></form>
@endsection
