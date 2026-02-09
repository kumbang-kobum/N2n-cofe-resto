@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Pengguna</h1>

@if (session('status'))
  <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
    {{ session('status') }}
  </div>
@endif

<div class="mb-4 flex items-center justify-between gap-2">
  <div class="text-sm text-gray-600">
    Kelola admin, manager, dan kasir.
  </div>
  <a href="{{ route('admin.users.create') }}"
     class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
    + Tambah Pengguna
  </a>
</div>

<div class="overflow-x-auto rounded-lg border bg-white">
  <table class="w-full text-left text-sm">
    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
      <tr>
        <th class="px-3 py-2">Nama</th>
        <th class="px-3 py-2">Email</th>
        <th class="px-3 py-2">Role</th>
        <th class="px-3 py-2 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($users as $u)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $u->name }}</td>
          <td class="px-3 py-2">{{ $u->email }}</td>
          <td class="px-3 py-2">
            @php $role = $u->getRoleNames()->first() ?? '-'; @endphp
            <span class="rounded bg-blue-50 px-2 py-0.5 text-xs text-blue-700">
              {{ strtoupper($role) }}
            </span>
          </td>
          <td class="px-3 py-2 text-right">
            <a href="{{ route('admin.users.edit', $u) }}"
               class="text-xs text-blue-600 hover:underline">Edit</a>
            <form action="{{ route('admin.users.destroy', $u) }}"
                  method="POST"
                  class="inline"
                  onsubmit="return confirm('Hapus pengguna ini?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="ml-2 text-xs text-red-600 hover:underline">Hapus</button>
            </form>
          </td>
        </tr>
      @empty
        <tr class="border-t">
          <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">
            Belum ada pengguna.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">
  {{ $users->links() }}
</div>
@endsection
