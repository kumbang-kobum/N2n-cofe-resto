@extends('layouts.dashboard')

@section('content')
<div class="max-w-xl">
  <h1 class="text-xl font-semibold mb-4">Edit Satuan</h1>

  <form method="POST"
        action="{{ route('admin.units.update', $unit) }}"
        class="bg-white border rounded-lg p-4 space-y-4">
    @csrf
    @method('PUT')

    <div>
      <label class="block text-sm text-gray-700 mb-1">Nama Satuan</label>
      <input type="text"
             name="name"
             value="{{ old('name', $unit->name) }}"
             class="w-full rounded border px-3 py-2 @error('name') border-red-500 @enderror">
      @error('name')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label class="block text-sm text-gray-700 mb-1">
        Simbol <span class="text-gray-400 text-xs">(mis: pcs, kg)</span>
      </label>
      <input type="text"
             name="symbol"
             value="{{ old('symbol', $unit->symbol) }}"
             class="w-full rounded border px-3 py-2 @error('symbol') border-red-500 @enderror">
      @error('symbol')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div class="flex items-center justify-between">
      <a href="{{ route('admin.units.index') }}"
         class="text-sm text-gray-600 hover:underline">
        Kembali
      </a>

      <button type="submit"
              class="px-4 py-2 rounded bg-blue-600 text-white text-sm">
        Update
      </button>
    </div>
  </form>
</div>
@endsection