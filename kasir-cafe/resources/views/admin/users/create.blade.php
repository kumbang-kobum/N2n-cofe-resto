@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Tambah Pengguna</h1>

<div class="bg-white border rounded-lg p-4 max-w-xl">
  <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
      <input type="text" name="name" value="{{ old('name') }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      @error('name')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
      <input type="email" name="email" value="{{ old('email') }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      @error('email')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
      <select name="role" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
        @foreach($roles as $r)
          <option value="{{ $r }}" @selected(old('role') === $r)>{{ strtoupper($r) }}</option>
        @endforeach
      </select>
      @error('role')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
      <input type="password" name="password"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      @error('password')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
      <input type="password" name="password_confirmation"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.users.index') }}"
         class="px-3 py-2 rounded border text-sm">Batal</a>
      <button type="submit"
              class="px-3 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
        Simpan
      </button>
    </div>
  </form>
</div>
@endsection
