@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold text-blue-700 mb-4">Edit Produk / Menu</h1>

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
      action="{{ route('admin.products.update', $product) }}"
      enctype="multipart/form-data"
      class="bg-white border rounded-lg p-4 space-y-4 max-w-xl">
  @csrf
  @method('PUT')

  <div>
    <label class="block text-sm text-gray-700 mb-1">Nama Menu</label>
    <input type="text"
           name="name"
           value="{{ old('name', $product->name) }}"
           class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
  </div>

  <div>
    <label class="block text-sm text-gray-700 mb-1">Harga Default</label>
    <input type="number"
           name="price_default"
           step="1"
           min="0"
           value="{{ old('price_default', $product->price_default) }}"
           class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
  </div>

  <div>
    <label class="block text-sm text-gray-700 mb-1">Gambar Menu</label>

    @if($product->image_path)
      <div class="mb-2">
        <img src="{{ asset('storage/'.$product->image_path) }}"
             alt="{{ $product->name }}"
             class="w-24 h-24 rounded object-cover">
      </div>
    @endif

    <input type="file"
           name="image"
           accept="image/*"
           class="w-full text-sm">
    <p class="text-xs text-gray-500 mt-1">
      Biarkan kosong jika tidak ingin mengubah gambar.
    </p>
  </div>

  <div class="flex items-center gap-2">
    <input type="checkbox"
           name="is_active"
           value="1"
           id="is_active"
           class="rounded border-gray-300 text-blue-600"
           {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
  </div>

  <div class="flex items-center gap-2">
    <a href="{{ route('admin.products.index') }}"
       class="px-3 py-2 rounded border text-sm">
      Batal
    </a>
    <button type="submit"
            class="px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
      Update
    </button>
  </div>
</form>
@endsection