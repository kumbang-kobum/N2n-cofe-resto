<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name','Kasir Cafe') }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')
</head>
<body class="bg-blue-50">
<div x-data="{ open:false }" class="min-h-screen">

  @php
    $settings = \App\Models\Setting::first();
  @endphp
  <header class="sticky top-0 z-30 bg-blue-700 text-white border-b border-blue-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <button @click="open=!open" class="lg:hidden p-2 rounded hover:bg-blue-600">☰</button>
        @if (!empty($settings?->logo_path))
          <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-8 w-8 object-contain bg-white/10 rounded p-1">
        @endif
        <div class="font-semibold">
          {{ $settings->restaurant_name ?? config('app.name','Kasir Cafe') }}
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="text-sm text-blue-100">{{ auth()->user()->name }}</div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="text-sm px-3 py-1.5 rounded bg-blue-900 text-white hover:bg-blue-800">Logout</button>
        </form>
      </div>
    </div>
  </header>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex">
      <aside class="hidden lg:block w-64 py-6">
        @include('partials.sidebar')
      </aside>

      <div class="lg:hidden">
        <div x-show="open" class="fixed inset-0 z-40 bg-black/40" @click="open=false"></div>
        <aside x-show="open" class="fixed z-50 top-0 left-0 w-72 h-full bg-blue-800 p-4 shadow text-blue-50">
          <div class="flex items-center justify-between mb-4">
            <div class="font-semibold">Menu</div>
            <button @click="open=false" class="p-2 rounded hover:bg-blue-700">✕</button>
          </div>
          @include('partials.sidebar')
        </aside>
      </div>

      <main class="flex-1 py-6 lg:pl-8">
        @if(session('status'))
          <div class="mb-4 p-3 rounded bg-green-50 text-green-800 border border-green-200">
            {{ session('status') }}
          </div>
        @endif

        @yield('content')
      </main>
    </div>
  </div>
</div>

<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
