@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">Satuan</h1>

  <a href="{{ route('admin.units.create') }}"
     class="px-3 py-2 rounded bg-blue-600 text-white text-sm">
    + Satuan Baru
  </a>
</div>

@if(session('status'))
  <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2">
    {{ session('status') }}
  </div>
@endif

<div class="bg-white border rounded-lg overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2 text-left w-16">No</th>
        <th class="px-3 py-2 text-left">Nama</th>
        <th class="px-3 py-2 text-left">Simbol</th>
        <th class="px-3 py-2 text-right w-32">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($units as $unit)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $loop->iteration }}</td>
          <td class="px-3 py-2">{{ $unit->name }}</td>
          <td class="px-3 py-2">{{ $unit->symbol }}</td>
          <td class="px-3 py-2 text-right space-x-1">
            <a href="{{ route('admin.units.edit', $unit) }}"
               class="text-blue-600 hover:underline text-xs">
              Edit
            </a>

            <form action="{{ route('admin.units.destroy', $unit) }}"
                  method="POST"
                  class="inline"
                  onsubmit="return confirm('Hapus satuan ini?')">
              @csrf
              @method('DELETE')
              <button type="submit"
                      class="text-red-600 hover:underline text-xs">
                Hapus
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" class="px-3 py-4 text-center text-gray-500">
            Belum ada data satuan.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection