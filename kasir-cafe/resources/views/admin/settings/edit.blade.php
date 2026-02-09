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

    <div class="border-t pt-4">
      <div class="text-sm font-semibold text-gray-700 mb-2">Lisensi</div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">License Key / Serial</label>
          <input type="text" name="license_key" value="{{ old('license_key', $setting->license_key ?? '') }}"
                 class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
          @error('license_key')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Installation Code</label>
          <input type="text" value="{{ $setting->installation_code ?? '-' }}" readonly
                 class="w-full rounded border border-gray-300 px-3 py-2 text-sm bg-gray-50">
          <div class="text-xs text-gray-500 mt-1">
            Kirim kode ini ke penyedia untuk mendapatkan license key.
          </div>
        </div>
      </div>

      @php
        $installedAt = $setting?->installed_at;
      @endphp
      <div class="text-xs text-gray-500 mt-2">
        @if ($installedAt)
          Tanggal instalasi: {{ \Carbon\Carbon::parse($installedAt)->format('d/m/Y') }}
        @else
          Tanggal instalasi akan tercatat otomatis saat aplikasi pertama kali diakses.
        @endif
      </div>
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
