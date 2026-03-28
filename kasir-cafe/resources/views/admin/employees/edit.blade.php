@extends('layouts.dashboard')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Edit Karyawan</h1>

    <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.employees.update' : 'admin.employees.update', $employee) }}" enctype="multipart/form-data" class="bg-white border rounded-lg p-4 space-y-3 max-w-2xl">
        @csrf
        @method('PUT')
        @include('admin.employees.partials.form', ['employee' => $employee])
        <div class="pt-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Update</button>
            <a href="{{ route(request()->routeIs('manager.*') ? 'manager.employees.index' : 'admin.employees.index') }}" class="ml-2 text-sm text-gray-600 hover:underline">Batal</a>
        </div>
    </form>
@endsection
