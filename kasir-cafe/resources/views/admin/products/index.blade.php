@extends('layouts.dashboard')

@section('content')
@php
  $routePrefix = request()->routeIs('manager.*') ? 'manager' : 'admin';
@endphp

<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold text-blue-700">Produk / Menu</h1>

  <a href="{{ route($routePrefix.'.products.create') }}"
     class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 text-sm">
    + Tambah Produk
  </a>
</div>

@if(session('status'))
  <div class="mb-4 px-4 py-2 rounded bg-green-100 text-green-800 text-sm">
    {{ session('status') }}
  </div>
@endif

<form method="GET" class="mb-4">
  <div class="flex items-center gap-2">
    <input type="text"
           name="q"
           value="{{ $q ?? '' }}"
           placeholder="Cari nama menu..."
           class="w-full max-w-sm rounded border px-3 py-2 text-sm">
    <button type="submit"
            class="rounded bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
      Cari
    </button>
    @if(!empty($q))
      <a href="{{ route($routePrefix.'.products.index') }}"
         class="rounded border px-3 py-2 text-sm hover:bg-gray-50">
        Reset
      </a>
    @endif
  </div>
</form>

<div class="bg-white border rounded-lg overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-blue-50">
      <tr>
        <th class="p-2 text-left">Gambar</th>
        <th class="p-2 text-left">Nama</th>
        <th class="p-2 text-right">Harga Default</th>
        <th class="p-2 text-center">Aktif</th>
        <th class="p-2 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($products as $product)
        <tr class="border-t">
          <td class="p-2">
            @if($product->image_path)
              <img src="{{ asset('storage/'.$product->image_path) }}"
                   alt="{{ $product->name }}"
                   class="w-12 h-12 rounded object-cover">
            @else
              <div class="w-12 h-12 rounded bg-blue-100 flex items-center justify-center text-xs text-blue-600">
                No Img
              </div>
            @endif
          </td>
          <td class="p-2">{{ $product->name }}</td>
          <td class="p-2 text-right">Rp {{ number_format($product->price_default,0,',','.') }}</td>
          <td class="p-2 text-center">
            @if($product->is_active)
              <span class="inline-flex px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Aktif</span>
            @else
              <span class="inline-flex px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">Nonaktif</span>
            @endif
          </td>
          <td class="p-2 text-right">
            <a href="{{ route($routePrefix.'.products.edit', $product) }}"
               class="text-xs px-2 py-1 rounded bg-blue-500 text-white hover:bg-blue-600">
              Edit
            </a>

            <form action="{{ route($routePrefix.'.products.destroy', $product) }}"
                  method="POST"
                  class="inline-block"
                  onsubmit="return confirm('Hapus produk ini?')">
              @csrf
              @method('DELETE')
              <button type="submit"
                      class="text-xs px-2 py-1 rounded bg-red-500 text-white hover:bg-red-600">
                Hapus
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="p-4 text-center text-gray-500">
            Belum ada produk. Tambahkan produk pertama Anda.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">
  {{ $products->links() }}
</div>
@endsection
