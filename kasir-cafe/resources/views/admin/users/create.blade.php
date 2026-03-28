@extends('layouts.dashboard')

@section('content')
@php
    $initialRole = old('role', $roles[0] ?? 'cashier');
    $checkedPermissions = old('permissions', $roleDefaultPermissions[$initialRole] ?? []);
@endphp

<h1 class="mb-4 text-xl font-semibold">Tambah Pengguna</h1>

<div class="grid gap-6 xl:grid-cols-[minmax(0,38rem)_minmax(0,1fr)]">
  <div class="rounded-lg border bg-white p-4">
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="permissions_present" value="1">

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Nama</label>
        <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
        @error('name')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
        @error('email')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Role</label>
        <select id="role" name="role" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
          @foreach($roles as $r)
            <option value="{{ $r }}" @selected($initialRole === $r)>{{ strtoupper($r) }}</option>
          @endforeach
        </select>
        <div class="mt-1 text-xs text-gray-500">Role tetap dipakai sebagai identitas utama. Hak akses menu bisa kamu sesuaikan di bawah.</div>
        @error('role')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
        @error('password')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      </div>

      <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
          <div>
            <h2 class="text-sm font-semibold text-slate-900">Hak Akses Menu</h2>
            <p class="text-xs text-slate-500">Checklist menu yang boleh diakses pengguna ini.</p>
          </div>
          <button
            type="button"
            class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-white"
            onclick="applyRoleDefaults()"
          >
            Gunakan Default Role
          </button>
        </div>

        @foreach($permissionGroups as $group => $permissions)
          <div class="mb-4 rounded-lg border border-slate-200 bg-white p-3">
            <div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $group }}</div>
            <div class="grid gap-3 md:grid-cols-2">
              @foreach($permissions as $permission)
                <label class="flex gap-3 rounded-lg border border-slate-200 px-3 py-2 hover:border-blue-300 hover:bg-blue-50/40">
                  <input
                    class="menu-permission mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    type="checkbox"
                    name="permissions[]"
                    value="{{ $permission['name'] }}"
                    @checked(in_array($permission['name'], $checkedPermissions, true))
                  >
                  <span>
                    <span class="block text-sm font-medium text-slate-800">{{ $permission['label'] }}</span>
                    <span class="block text-xs text-slate-500">{{ $permission['description'] }}</span>
                  </span>
                </label>
              @endforeach
            </div>
          </div>
        @endforeach
        @error('permissions')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
        @error('permissions.*')<div class="mt-1 text-xs text-red-600">{{ $message }}</div>@enderror
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.users.index') }}" class="rounded border px-3 py-2 text-sm">Batal</a>
        <button type="submit" class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
          Simpan
        </button>
      </div>
    </form>
  </div>

  <div class="rounded-lg border bg-white p-4">
    <h2 class="text-sm font-semibold text-slate-900">Catatan Audit</h2>
    <ul class="mt-3 space-y-2 text-sm text-slate-600">
      <li>Gunakan <span class="font-medium">default role</span> kalau ingin akses standar sesuai jabatan.</li>
      <li>Hilangkan checklist tertentu jika menu itu tidak perlu terlihat atau diakses via URL.</li>
      <li>Untuk menu sensitif seperti <span class="font-medium">Kas Kecil</span>, <span class="font-medium">Payroll</span>, dan <span class="font-medium">Audit Log</span>, sebaiknya hanya diberikan ke pihak yang benar-benar perlu.</li>
    </ul>
  </div>
</div>

<script>
  const roleDefaults = @json($roleDefaultPermissions);

  function applyRoleDefaults() {
    const role = document.getElementById('role').value;
    const defaults = roleDefaults[role] || [];

    document.querySelectorAll('.menu-permission').forEach((checkbox) => {
      checkbox.checked = defaults.includes(checkbox.value);
    });
  }
</script>
@endsection
