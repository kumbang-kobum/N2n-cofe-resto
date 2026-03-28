@extends('layouts.dashboard')

@section('content')
    <h1 class="text-xl font-semibold mb-4">Tambah Karyawan</h1>

    <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.employees.store' : 'admin.employees.store') }}" enctype="multipart/form-data" class="bg-white border rounded-lg p-4 space-y-3 max-w-2xl">
        @csrf
        @include('admin.employees.partials.form', ['employee' => null])
        <div class="pt-2">
            <button class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route(request()->routeIs('manager.*') ? 'manager.employees.index' : 'admin.employees.index') }}" class="ml-2 text-sm text-gray-600 hover:underline">Batal</a>
        </div>
    </form>
@endsection
