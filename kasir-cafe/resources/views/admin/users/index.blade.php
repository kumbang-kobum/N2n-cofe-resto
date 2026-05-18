@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Pengguna & Hak Akses</h1>

@if (session('status'))
  <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
    {{ session('status') }}
  </div>
@endif

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
  <div class="flex flex-wrap items-center gap-2">
    <span class="text-xs text-gray-500 font-medium">Filter:</span>
    <a href="{{ route('admin.users.index') }}"
       class="rounded px-3 py-1.5 text-xs font-medium border transition {{ $roleFilter === '' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400' }}">
      Semua
    </a>
    @foreach($roles as $r)
      <a href="{{ route('admin.users.index', ['role' => $r]) }}"
         class="rounded px-3 py-1.5 text-xs font-medium border transition {{ $roleFilter === $r ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400' }}">
        {{ strtoupper($r) }}
      </a>
    @endforeach
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
        <th class="px-3 py-2">Hak Akses</th>
        <th class="px-3 py-2 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($users as $u)
        @php $role = $u->getRoleNames()->first() ?? '-'; @endphp
        <tr class="border-t hover:bg-slate-50/60">
          <td class="px-3 py-2 font-medium text-slate-800">{{ $u->name }}</td>
          <td class="px-3 py-2 text-gray-600">{{ $u->email }}</td>
          <td class="px-3 py-2">
            <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
              {{ strtoupper($role) }}
            </span>
          </td>
          <td class="px-3 py-2">
            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-600"
                  title="{{ $u->permissions->pluck('name')->implode(', ') }}">
              {{ $u->permissions->count() }} akses
            </span>
          </td>
          <td class="px-3 py-2 text-right whitespace-nowrap">
            <a href="{{ route('admin.users.edit', $u) }}"
               class="text-xs text-blue-600 hover:underline">Edit</a>
            @if($u->id !== auth()->id())
              <form action="{{ route('admin.users.destroy', $u) }}"
                    method="POST"
                    class="inline"
                    onsubmit="return confirm('Hapus pengguna {{ addslashes($u->name) }}?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="ml-2 text-xs text-red-600 hover:underline">Hapus</button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr class="border-t">
          <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">
            @if($roleFilter)
              Tidak ada pengguna dengan role <strong>{{ strtoupper($roleFilter) }}</strong>.
            @else
              Belum ada pengguna.
            @endif
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
