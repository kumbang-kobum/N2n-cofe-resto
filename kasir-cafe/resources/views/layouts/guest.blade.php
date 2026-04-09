<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'n2N Kasir Cafe') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('n2Nlogo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('n2Nlogo.png') }}">
        @include('partials.theme-init')

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
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.12),transparent_18%),radial-gradient(circle_at_bottom_right,rgba(96,165,250,0.22),transparent_24%),linear-gradient(135deg,#0f172a_0%,#1d4ed8_52%,#2563eb_100%)] px-4 py-10">
            <div class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-6xl items-center">
                <div class="grid w-full gap-6 lg:grid-cols-[1.08fr_0.92fr]">
                    <div class="hidden rounded-[32px] border border-white/10 bg-white/10 p-8 text-white shadow-[0_28px_80px_rgba(15,23,42,0.28)] backdrop-blur lg:flex lg:flex-col lg:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-50/85">
                                Secure Access
                            </div>
                            <div class="mt-6 flex items-center gap-3">
                                @if (!empty($settings?->logo_path))
                                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-14 w-14 rounded-2xl bg-white/10 object-contain p-1.5 shadow-inner">
                                @else
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-lg font-bold">
                                        {{ strtoupper(substr($settings->restaurant_name ?? config('app.name','KC'),0,2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="text-2xl font-semibold tracking-tight">
                                        {{ $settings->restaurant_name ?? config('app.name','Kasir Cafe') }}
                                    </div>
                                    <div class="mt-1 text-sm text-blue-100/80">
                                        {{ $settings->restaurant_address ?? 'Sistem kasir, stok bahan, resep, dan laporan operasional.' }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10 max-w-xl">
                                <div class="text-4xl font-bold leading-tight tracking-tight">
                                    Akses operasional resto dalam satu dashboard yang rapi.
                                </div>
                                <p class="mt-4 text-base leading-7 text-blue-100/85">
                                    Login sebagai admin, manager, atau kasir untuk mengelola transaksi, resep, stok bahan, pengeluaran, payroll, dan laporan keuangan dengan alur yang sinkron.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/10 bg-slate-950/15 p-4">
                                <div class="text-[11px] uppercase tracking-[0.2em] text-blue-100/70">Kasir</div>
                                <div class="mt-2 text-lg font-semibold">Transaksi cepat</div>
                                <div class="mt-1 text-sm text-blue-100/80">POS, open bill, pembayaran, dan receipt.</div>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/15 p-4">
                                <div class="text-[11px] uppercase tracking-[0.2em] text-blue-100/70">Stok</div>
                                <div class="mt-2 text-lg font-semibold">Batch & FEFO</div>
                                <div class="mt-1 text-sm text-blue-100/80">Receiving, expired, dan opname lebih terkendali.</div>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-slate-950/15 p-4">
                                <div class="text-[11px] uppercase tracking-[0.2em] text-blue-100/70">Laporan</div>
                                <div class="mt-2 text-lg font-semibold">Siap audit</div>
                                <div class="mt-1 text-sm text-blue-100/80">Omzet, HPP, pengeluaran, payroll, dan excel.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-auto flex w-full max-w-lg flex-col justify-center">
                        <div class="rounded-[32px] border border-white/10 bg-white/95 px-6 py-6 shadow-[0_28px_80px_rgba(15,23,42,0.18)] backdrop-blur sm:px-8 sm:py-8">
                            <div class="mb-4 flex justify-end">
                                @include('partials.theme-select', [
                                    'wrapperClass' => 'inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50',
                                    'selectClass' => 'rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm text-slate-700 outline-none',
                                ])
                            </div>
                            <div class="mb-6 flex items-center gap-3 lg:hidden">
                                @if (!empty($settings?->logo_path))
                                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-11 w-11 rounded-2xl bg-blue-50 object-contain p-1.5">
                                @else
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-sm font-bold text-white">
                                        {{ strtoupper(substr($settings->restaurant_name ?? config('app.name','KC'),0,2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="text-base font-semibold text-slate-900">
                                        {{ $settings->restaurant_name ?? config('app.name','Kasir Cafe') }}
                                    </div>
                                    <div class="text-xs text-slate-500">Login dashboard operasional</div>
                                </div>
                            </div>

                            {{ $slot }}

                            <div class="mt-6 border-t border-slate-200 pt-4 text-[11px] sm:text-xs text-slate-500">
                                &copy; {{ date('Y') }} {{ $settings->restaurant_name ?? config('app.name','Kasir Cafe') }}. Akses hanya untuk akun yang berwenang.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('partials.theme-script')
    </body>
</html>
