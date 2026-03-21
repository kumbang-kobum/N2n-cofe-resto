@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Pengaturan Resto</h1>

@if (session('status'))
  <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
    {{ session('status') }}
  </div>
@endif

@if ($errors->has('tv_video'))
  <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
    <div class="font-medium">Upload video TV gagal.</div>
    <div class="mt-1">{{ $errors->first('tv_video') }}</div>
  </div>
@endif

<div class="bg-white border rounded-lg p-4 max-w-2xl">
  @php
    $settingsUpdateRoute = request()->routeIs('manager.*')
      ? route('manager.settings.update')
      : (request()->routeIs('cashier.*') ? route('cashier.settings.update') : route('admin.settings.update'));
  @endphp
  <form method="POST" action="{{ $settingsUpdateRoute }}" enctype="multipart/form-data" class="space-y-4">
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

    <div class="rounded border border-gray-200 p-3">
      <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
        <input type="checkbox" name="tax_enabled" value="1"
               @checked(old('tax_enabled', $setting->tax_enabled ?? true))
               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        Aktifkan PPN 10%
      </label>
      <div class="text-xs text-gray-500 mt-1">
        Jika nonaktif, pajak tidak dihitung pada transaksi dan tidak tampil pada nota.
      </div>
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
      <div class="text-sm font-semibold text-gray-700 mb-2">TV Informasi</div>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Video TV (MP4/WebM/Ogg)</label>
          <input type="file" name="tv_video" accept=".mp4,.webm,.ogg,video/mp4,video/webm,video/ogg"
                 class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
          @error('tv_video')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
          <div class="text-xs text-gray-500 mt-1">
            Video akan diputar otomatis di halaman TV Informasi. Batas upload aplikasi: 100 MB.
          </div>
          @if (!empty($setting?->tv_video_path))
            <div class="mt-2">
              <video src="{{ asset('storage/' . $setting->tv_video_path) }}" controls class="h-40 rounded border bg-black"></video>
            </div>
            <label class="mt-2 inline-flex items-center gap-2 text-sm text-red-700">
              <input type="checkbox" name="remove_tv_video" value="1"
                     class="rounded border-gray-300 text-red-600 focus:ring-red-500">
              Hapus video saat ini
            </label>
          @endif
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Running Text</label>
          <textarea name="tv_running_text" rows="3"
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm"
                    placeholder="Contoh: Selamat datang di resto kami • Promo paket hemat tersedia hari ini • Terima kasih atas kunjungan Anda">{{ old('tv_running_text', $setting->tv_running_text ?? '') }}</textarea>
          @error('tv_running_text')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </div>
      </div>
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
