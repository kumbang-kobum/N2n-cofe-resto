@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
  <div>
    <h1 class="text-xl font-semibold">Kategori Pengeluaran</h1>
    <p class="text-sm text-slate-600">Standarkan kategori belanja agar input manager/kasir lebih konsisten dan laporan lebih rapi.</p>
  </div>

  <a href="{{ route('admin.expense_categories.create') }}"
     class="px-3 py-2 rounded bg-blue-600 text-white text-sm">
    + Kategori Baru
  </a>
</div>

@if(session('status'))
  <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2">
    {{ session('status') }}
  </div>
@endif

@error('delete')
  <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2">
    {{ $message }}
  </div>
@enderror

<div class="bg-white border rounded-lg overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2 text-left w-16">No</th>
        <th class="px-3 py-2 text-left">Nama</th>
        <th class="px-3 py-2 text-left">Deskripsi</th>
        <th class="px-3 py-2 text-left">Status</th>
        <th class="px-3 py-2 text-right w-32">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($categories as $category)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $loop->iteration }}</td>
          <td class="px-3 py-2 font-medium">{{ $category->name }}</td>
          <td class="px-3 py-2 text-slate-600">{{ $category->description ?: '-' }}</td>
          <td class="px-3 py-2">
            <span class="text-xs px-2 py-1 rounded {{ $category->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
              {{ $category->is_active ? 'AKTIF' : 'NONAKTIF' }}
            </span>
          </td>
          <td class="px-3 py-2 text-right space-x-1">
            <a href="{{ route('admin.expense_categories.edit', $category) }}"
               class="text-blue-600 hover:underline text-xs">
              Edit
            </a>

            <form action="{{ route('admin.expense_categories.destroy', $category) }}"
                  method="POST"
                  class="inline"
                  onsubmit="return confirm('Hapus kategori ini?')">
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
          <td colspan="5" class="px-3 py-4 text-center text-gray-500">
            Belum ada kategori pengeluaran.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
