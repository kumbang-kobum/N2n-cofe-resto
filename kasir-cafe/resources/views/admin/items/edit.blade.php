@extends('layouts.dashboard')

@section('content')
<h1 class="mb-4 text-xl font-semibold">Edit Bahan</h1>

@if ($errors->any())
  <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
    <div class="font-semibold">Gagal menyimpan. Periksa kembali input:</div>
    <ul class="ml-5 list-disc">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.items.update', $item) }}" class="space-y-4 max-w-xl">
  @csrf
  @method('PUT')

  <div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Nama Bahan</label>
    <input type="text" name="name" value="{{ old('name', $item->name) }}"
           class="w-full rounded border px-3 py-2 text-sm"
           required>
  </div>

  <div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Satuan Dasar</label>
    <select name="base_unit_id" class="w-full rounded border px-3 py-2 text-sm" required>
      @foreach($units as $u)
        <option value="{{ $u->id }}" @selected(old('base_unit_id', $item->base_unit_id) == $u->id)>
          {{ $u->name }} ({{ $u->symbol }})
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Min Stok (base)</label>
    <input type="number" step="0.001" name="min_stock"
           value="{{ old('min_stock', $item->min_stock) }}"
           class="w-full rounded border px-3 py-2 text-sm">
  </div>

  <div class="flex items-center gap-4">
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
      <input type="checkbox" name="track_expiry" value="1"
             @checked(old('track_expiry', $item->track_expiry))>
      Track tanggal kedaluwarsa
    </label>

    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
      <input type="checkbox" name="is_active" value="1"
             @checked(old('is_active', $item->is_active))>
      Aktif
    </label>
  </div>

  <div class="flex items-center gap-2">
    <a href="{{ route('admin.items.index') }}"
       class="rounded border px-3 py-2 text-sm text-gray-700">Batal</a>
    <button type="submit"
            class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
      Simpan Perubahan
    </button>
  </div>
</form>
@endsection