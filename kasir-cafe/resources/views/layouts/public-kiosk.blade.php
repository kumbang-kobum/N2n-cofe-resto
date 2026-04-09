<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Kasir Cafe') }} - Kiosk Absensi</title>
    <link rel="icon" type="image/png" href="{{ asset('n2Nlogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('n2Nlogo.png') }}">
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.12),transparent_18%),radial-gradient(circle_at_bottom_right,rgba(96,165,250,0.22),transparent_24%),linear-gradient(135deg,#0f172a_0%,#1d4ed8_52%,#2563eb_100%)] text-slate-900">
    @php
        $settings = \App\Models\Setting::first();
    @endphp
    <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
        <header class="mx-auto flex w-full max-w-7xl items-center justify-between rounded-[28px] border border-white/10 bg-white/10 px-5 py-4 text-white shadow-[0_18px_50px_rgba(15,23,42,0.2)] backdrop-blur">
            <div class="flex items-center gap-3">
                @if (!empty($settings?->logo_path))
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-11 w-11 rounded-2xl bg-white/10 object-contain p-1.5 shadow-inner">
                @else
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/20 text-sm font-bold">
                        {{ strtoupper(substr($settings->restaurant_name ?? config('app.name', 'KC'), 0, 2)) }}
                    </div>
                @endif
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75">Kiosk Absensi</div>
                    <div class="text-lg font-semibold text-white">{{ $settings->restaurant_name ?? config('app.name', 'Kasir Cafe') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @include('partials.theme-select', [
                    'wrapperClass' => 'hidden sm:flex items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/20',
                    'selectClass' => 'rounded-lg border border-white/10 bg-white/10 px-2 py-1 text-sm text-white outline-none',
                ])
                <a href="{{ route('landing') }}" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20">
                    Kembali
                </a>
            </div>
        </header>

        <main class="mx-auto mt-6 w-full max-w-7xl">
            @yield('content')
        </main>
    </div>

    @include('partials.theme-script')
    @stack('scripts')
</body>
</html>
