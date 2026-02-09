@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Edit Inventaris</h1>

<div class="bg-white border rounded-lg p-4 max-w-2xl">
  <form method="POST" action="{{ route('admin.assets.update', $asset) }}" class="space-y-4">
    @csrf
    @method('PUT')

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
      <input type="text" name="name" value="{{ old('name', $asset->name) }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      @error('name')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
      <input type="text" name="category" value="{{ old('category', $asset->category) }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
      <input type="text" name="location" value="{{ old('location', $asset->location) }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Beli</label>
      <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli</label>
      <input type="number" name="purchase_cost" value="{{ old('purchase_cost', $asset->purchase_cost) }}" min="0" step="1000"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi</label>
      <select name="condition" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
        @foreach(['GOOD','MINOR','DAMAGED','DISPOSED'] as $c)
          <option value="{{ $c }}" @selected(old('condition', $asset->condition) === $c)>{{ $c }}</option>
        @endforeach
      </select>
    </div>

    <div class="flex items-center gap-2">
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $asset->is_active))>
        Aktif
      </label>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.assets.index') }}" class="px-3 py-2 rounded border text-sm">Batal</a>
      <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">Simpan</button>
    </div>
  </form>
</div>
@endsection
