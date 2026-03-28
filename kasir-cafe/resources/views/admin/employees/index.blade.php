@extends('layouts.dashboard')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold">Master Karyawan</h1>
            <p class="text-sm text-gray-600">Karyawan aplikasi & non-aplikasi (dapur, keamanan, dll).</p>
        </div>
        <a href="{{ route(request()->routeIs('manager.*') ? 'manager.employees.create' : 'admin.employees.create') }}"
           class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
            + Tambah Karyawan
        </a>
    </div>

    <form method="GET" class="mb-3">
        <input type="text" name="q" value="{{ $q }}" class="w-full md:w-80 border rounded px-3 py-2 text-sm" placeholder="Cari nama/kode/jabatan/departemen">
    </form>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-3 py-2">Kode</th>
                    <th class="px-3 py-2">Nama</th>
                    <th class="px-3 py-2">Jabatan</th>
                    <th class="px-3 py-2">Departemen</th>
                    <th class="px-3 py-2">Shift Default</th>
                    <th class="px-3 py-2">Jatah Makan</th>
                    <th class="px-3 py-2">Akun App</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $employee->employee_code ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $employee->name }}</td>
                        <td class="px-3 py-2">{{ $employee->position ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $employee->department ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $employee->defaultShift?->name ?: '-' }}</td>
                        <td class="px-3 py-2">
                            {{ is_null($employee->meal_allowance_monthly) ? 'Tidak dibatasi' : 'Rp ' . number_format($employee->meal_allowance_monthly, 0, ',', '.') . ' / bulan' }}
                        </td>
                        <td class="px-3 py-2">
                            @if($employee->uses_app)
                                <span class="text-xs rounded px-2 py-0.5 bg-blue-100 text-blue-700">
                                    {{ optional($employee->user)->name ?? 'Belum di-link' }}
                                </span>
                            @else
                                <span class="text-xs rounded px-2 py-0.5 bg-gray-100 text-gray-600">Non-app</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <span class="text-xs rounded px-2 py-0.5 {{ $employee->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route(request()->routeIs('manager.*') ? 'manager.employees.edit' : 'admin.employees.edit', $employee) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route(request()->routeIs('manager.*') ? 'manager.employees.destroy' : 'admin.employees.destroy', $employee) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Hapus karyawan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 text-xs text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t">
                        <td colspan="9" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada data karyawan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $employees->links() }}
    </div>
@endsection
