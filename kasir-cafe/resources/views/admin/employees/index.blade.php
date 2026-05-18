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

    @php $routePrefix = request()->routeIs('manager.*') ? 'manager' : 'admin'; @endphp

    <div class="mb-3 flex flex-wrap items-center gap-3">
        <form method="GET" class="flex-1 min-w-[200px]">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <input type="text" name="q" value="{{ $q }}" class="w-full md:w-80 border rounded px-3 py-2 text-sm" placeholder="Cari nama / kode / jabatan / departemen">
        </form>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500 font-medium">Status:</span>
            @foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', '' => 'Semua'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val, 'page' => 1]) }}"
                   class="rounded px-3 py-1.5 text-xs font-medium border transition
                     {{ $statusFilter === $val ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

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
                    <tr class="border-t hover:bg-slate-50/60">
                        <td class="px-3 py-2 text-gray-500">{{ $employee->employee_code ?: '-' }}</td>
                        <td class="px-3 py-2 font-medium text-slate-800">{{ $employee->name }}</td>
                        <td class="px-3 py-2">{{ $employee->position ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $employee->department ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $employee->defaultShift?->name ?: '-' }}</td>
                        <td class="px-3 py-2">
                            {{ is_null($employee->meal_allowance_monthly) ? 'Tidak dibatasi' : 'Rp ' . number_format($employee->meal_allowance_monthly, 0, ',', '.') . ' / bulan' }}
                        </td>
                        <td class="px-3 py-2">
                            @if($employee->uses_app)
                                @if($employee->user)
                                    @can('access.users')
                                        <a href="{{ route('admin.users.edit', $employee->user) }}"
                                           class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 hover:bg-blue-100 hover:underline">
                                            {{ $employee->user->name }}
                                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    @else
                                        <span class="rounded bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ $employee->user->name }}</span>
                                    @endcan
                                @else
                                    <span class="rounded bg-amber-50 px-2 py-0.5 text-xs text-amber-700">Belum di-link</span>
                                @endif
                            @else
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">Non-app</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <span class="rounded px-2 py-0.5 text-xs font-medium {{ $employee->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <a href="{{ route($routePrefix . '.employees.edit', $employee) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route($routePrefix . '.employees.destroy', $employee) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('Hapus karyawan {{ addslashes($employee->name) }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 text-xs text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t">
                        <td colspan="9" class="px-3 py-4 text-center text-sm text-gray-500">
                            @if($q)
                                Tidak ada karyawan yang cocok dengan pencarian "<strong>{{ $q }}</strong>".
                            @elseif($statusFilter === 'active')
                                Belum ada karyawan aktif.
                            @elseif($statusFilter === 'inactive')
                                Tidak ada karyawan nonaktif.
                            @else
                                Belum ada data karyawan.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $employees->links() }}
    </div>
@endsection
