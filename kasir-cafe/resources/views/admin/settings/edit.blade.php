@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Pengaturan Resto</h1>

@if (session('status'))
  <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
    {{ session('status') }}
  </div>
@endif

<div class="bg-white border rounded-lg p-4 max-w-2xl">
  <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Nama Resto</label>
      <input type="text" name="restaurant_name" value="{{ old('restaurant_name', $setting->restaurant_name ?? '') }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      @error('restaurant_name')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
      <input type="text" name="restaurant_address" value="{{ old('restaurant_address', $setting->restaurant_address ?? '') }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      @error('restaurant_address')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
      <input type="text" name="restaurant_phone" value="{{ old('restaurant_phone', $setting->restaurant_phone ?? '') }}"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      @error('restaurant_phone')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Logo (PNG/JPG/WebP)</label>
      <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp"
             class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
      @error('logo')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
      @if (!empty($setting?->logo_path))
        <div class="mt-2">
          <img src="{{ asset('storage/' . $setting->logo_path) }}" alt="Logo" class="h-16">
        </div>
      @endif
    </div>

    <div class="flex items-center gap-2">
      <button type="submit"
              class="px-3 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
        Simpan
      </button>
    </div>
  </form>
</div>
@endsection
