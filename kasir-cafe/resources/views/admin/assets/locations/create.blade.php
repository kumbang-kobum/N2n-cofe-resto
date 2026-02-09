@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Tambah Lokasi Inventaris</h1>

<div class="bg-white border rounded-lg p-4 max-w-xl">
  <form method="POST" action="{{ route('admin.asset_locations.store') }}" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
      <input type="text" name="name" value="{{ old('name') }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      @error('name')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.asset_locations.index') }}" class="px-3 py-2 rounded border text-sm">Batal</a>
      <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Simpan</button>
    </div>
  </form>
</div>
@endsection
