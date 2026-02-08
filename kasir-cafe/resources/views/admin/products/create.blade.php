@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold text-blue-700 mb-4">Tambah Produk / Menu</h1>

@if($errors->any())
  <div class="mb-4 px-4 py-2 rounded bg-red-100 text-red-700 text-sm">
    <ul class="list-disc list-inside">
      @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST"
      action="{{ route('admin.products.store') }}"
      enctype="multipart/form-data"
      class="bg-white border rounded-lg p-4 space-y-4 max-w-xl">
  @csrf

  <div>
    <label class="block text-sm text-gray-700 mb-1">Nama Menu</label>
    <input type="text"
           name="name"
           value="{{ old('name') }}"
           class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
  </div>

  <div>
    <label class="block text-sm text-gray-700 mb-1">Harga Default</label>
    <input type="number"
           name="price_default"
           step="1"
           min="0"
           value="{{ old('price_default') }}"
           class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
  </div>

  <div>
    <label class="block text-sm text-gray-700 mb-1">Gambar Menu (opsional)</label>
    <input type="file"
           name="image"
           accept="image/*"
           class="w-full text-sm">
    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, WEBP. Maks 2MB.</p>
  </div>

  <div class="flex items-center gap-2">
    <input type="checkbox"
           name="is_active"
           value="1"
           id="is_active"
           class="rounded border-gray-300 text-blue-600"
           {{ old('is_active', true) ? 'checked' : '' }}>
    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
  </div>

  <div class="flex items-center gap-2">
    <a href="{{ route('admin.products.index') }}"
       class="px-3 py-2 rounded border text-sm">
      Batal
    </a>
    <button type="submit"
            class="px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
      Simpan
    </button>
  </div>
</form>
@endsection