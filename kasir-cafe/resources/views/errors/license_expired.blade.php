@extends('layouts.dashboard')

@section('content')
<div class="bg-white border rounded-lg p-6 max-w-2xl">
  <h1 class="text-xl font-semibold mb-2">Akses Dibatasi</h1>
  <p class="text-sm text-gray-600 mb-4">
    {{ $message ?? 'Lisensi tidak valid atau masa trial berakhir.' }}
  </p>

  @if (!empty($can_set_license) && auth()->user()?->hasRole('admin'))
    <a href="{{ route('admin.settings.edit') }}"
       class="inline-flex items-center px-3 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
      Masukkan License Key
    </a>
  @endif
</div>
@endsection
