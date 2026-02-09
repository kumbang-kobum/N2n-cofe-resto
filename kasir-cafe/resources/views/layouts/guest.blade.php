<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $settings = \App\Models\Setting::first();
    @endphp
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-blue-900 via-blue-700 to-blue-500 px-4 py-10">
            <div class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                <div class="hidden md:flex flex-col justify-between rounded-2xl p-8 text-white bg-white/10 backdrop-blur border border-white/15">
                    <div class="flex items-center gap-3">
                        @if (!empty($settings?->logo_path))
                            <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-12 w-12 object-contain bg-white/10 rounded p-1">
                        @else
                            <div class="h-12 w-12 rounded bg-white/20 flex items-center justify-center font-bold">
                                {{ strtoupper(substr($settings->restaurant_name ?? config('app.name','KC'),0,2)) }}
                            </div>
                        @endif
                        <div>
                            <div class="text-lg font-semibold">
                                {{ $settings->restaurant_name ?? config('app.name','Kasir Cafe') }}
                            </div>
                            <div class="text-xs text-blue-100">
                                {{ $settings->restaurant_address ?? 'Sistem kasir & stok bahan' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="text-2xl font-semibold leading-tight">
                            Login untuk melanjutkan
                        </div>
                        <p class="text-sm text-blue-100 mt-2">
                            Akses admin, manager, dan kasir untuk mengelola transaksi, stok, resep, dan laporan.
                        </p>
                    </div>

                    <div class="text-xs text-blue-100">
                        &copy; {{ date('Y') }} {{ $settings->restaurant_name ?? config('app.name','Kasir Cafe') }}
                    </div>
                </div>

                <div class="w-full max-w-md mx-auto bg-white rounded-2xl shadow-xl border border-slate-100 px-6 py-6">
                    <div class="md:hidden flex items-center gap-3 mb-4">
                        @if (!empty($settings?->logo_path))
                            <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-10 w-10 object-contain bg-blue-50 rounded p-1">
                        @else
                            <div class="h-10 w-10 rounded bg-blue-600 text-white flex items-center justify-center font-bold">
                                {{ strtoupper(substr($settings->restaurant_name ?? config('app.name','KC'),0,2)) }}
                            </div>
                        @endif
                        <div>
                            <div class="text-base font-semibold text-blue-700">
                                {{ $settings->restaurant_name ?? config('app.name','Kasir Cafe') }}
                            </div>
                            <div class="text-xs text-slate-500">Login dashboard</div>
                        </div>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
