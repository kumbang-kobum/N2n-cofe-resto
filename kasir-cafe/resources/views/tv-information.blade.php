<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TV Informasi - {{ $settings->restaurant_name ?? config('app.name', 'n2N') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('n2Nlogo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes marquee-left {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        @keyframes menu-scroll-up {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }
        .tv-marquee-track {
            animation: marquee-left 24s linear infinite;
            white-space: nowrap;
        }
        /* pengaturan kecepatan scroll dengan mengubah durasi animasi (misalnya 24s untuk teks berjalan dan 36s untuk menu) */
        .tv-menu-scroll {
            animation: menu-scroll-up 150s linear infinite;
        }
        .tv-menu-scroll:hover {
            animation-play-state: paused;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <div id="tvInformationApp" class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(37,99,235,0.25),transparent_32%),linear-gradient(135deg,#020617,#0f172a_45%,#172554_100%)]">
        <header class="border-b border-white/10 bg-black/20 backdrop-blur">
            <div class="mx-auto flex max-w-[1600px] items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    @if (!empty($settings?->logo_path))
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-12 w-12 rounded-2xl bg-white/10 object-contain p-1.5">
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 font-bold">
                            {{ strtoupper(substr($settings->restaurant_name ?? config('app.name', 'n2N'), 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div class="text-xs uppercase tracking-[0.28em] text-blue-200/70">TV Informasi</div>
                        <div class="text-2xl font-semibold">{{ $settings->restaurant_name ?? 'n2N Cafe Resto' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="tvFullscreenButton" class="rounded-xl border border-white/10 bg-blue-600/80 px-4 py-2 text-sm font-medium hover:bg-blue-500">
                        Fullscreen
                    </button>
                    <a href="{{ route('landing') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm hover:bg-white/10">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto grid max-w-[1600px] gap-6 px-6 py-6 lg:grid-cols-[1.35fr_0.95fr]">
            <section class="rounded-[28px] border border-white/10 bg-black/20 p-5 shadow-[0_24px_70px_rgba(15,23,42,0.45)] backdrop-blur">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-[0.24em] text-blue-200/70">Now Showing</div>
                        <h1 class="text-3xl font-semibold">Video Informasi</h1>
                    </div>
                    <div class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-200">
                        Live Display
                    </div>
                </div>

                <div class="overflow-hidden rounded-[24px] border border-white/10 bg-black">
                    @if (!empty($settings?->tv_video_path))
                        <video class="aspect-video w-full bg-black object-cover" controls autoplay muted loop playsinline>
                            <source src="{{ asset('storage/' . $settings->tv_video_path) }}">
                            Browser tidak mendukung video.
                        </video>
                    @else
                        <div class="flex aspect-video w-full items-center justify-center bg-[linear-gradient(135deg,#111827,#0f172a,#1d4ed8)] p-8 text-center">
                            <div>
                                <div class="text-sm uppercase tracking-[0.24em] text-blue-200/70">Video belum diatur</div>
                                <div class="mt-3 text-2xl font-semibold">Unggah video dari Pengaturan Resto</div>
                                <div class="mt-2 text-sm text-slate-300">Admin, manager, dan kasir dapat mengganti video dan running text.</div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-white/10 bg-blue-600/10 px-4 py-3">
                    <div class="tv-marquee-track text-lg font-medium text-blue-50">
                        {{ $settings->tv_running_text ?: 'Selamat datang di resto kami • Promo terbaru tersedia hari ini • Terima kasih atas kunjungan Anda' }}
                    </div>
                </div>
            </section>

            <aside class="rounded-[28px] border border-white/10 bg-white/[0.06] p-5 shadow-[0_24px_70px_rgba(15,23,42,0.35)] backdrop-blur">
                <div class="mb-4">
                    <div class="text-xs uppercase tracking-[0.24em] text-blue-200/70">Katalog</div>
                    <h2 class="text-3xl font-semibold">Menu & Harga</h2>
                    <div class="mt-1 text-sm text-slate-300">Daftar menu aktif yang tampil ke pelanggan.</div>
                </div>

                <div class="max-h-[calc(100vh-220px)] overflow-hidden">
                    @if ($products->isNotEmpty())
                        @php
                            $loopProducts = $products->count() > 1 ? $products->concat($products) : $products;
                        @endphp
                        <div class="tv-menu-scroll space-y-3 pr-1">
                            @foreach ($loopProducts as $product)
                                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/15 p-3">
                                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-white/10">
                                        @if($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-300">
                                                No Image
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-xl font-semibold">{{ $product->name }}</div>
                                        <div class="mt-1 text-sm text-slate-300">Menu aktif</div>
                                    </div>
                                    <div class="shrink-0 rounded-2xl bg-emerald-400/10 px-3 py-2 text-right">
                                        <div class="text-[11px] uppercase tracking-[0.24em] text-emerald-200/80">Harga</div>
                                        <div class="text-xl font-bold text-emerald-200">Rp {{ number_format((float) $product->price_default, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-white/15 bg-black/10 px-4 py-8 text-center text-slate-300">
                            Belum ada menu aktif untuk ditampilkan.
                        </div>
                    @endif
                </div>
            </aside>
        </main>
    </div>
    <script>
        (() => {
            const app = document.getElementById('tvInformationApp');
            const button = document.getElementById('tvFullscreenButton');

            if (!app || !button) {
                return;
            }

            const syncLabel = () => {
                button.textContent = document.fullscreenElement ? 'Keluar Fullscreen' : 'Fullscreen';
            };

            const toggleFullscreen = async () => {
                try {
                    if (document.fullscreenElement) {
                        await document.exitFullscreen();
                    } else {
                        await app.requestFullscreen();
                    }
                } catch (error) {
                    console.error('Fullscreen gagal dijalankan.', error);
                } finally {
                    syncLabel();
                }
            };

            button.addEventListener('click', toggleFullscreen);
            app.addEventListener('dblclick', toggleFullscreen);
            document.addEventListener('fullscreenchange', syncLabel);
            syncLabel();
        })();
    </script>
</body>
</html>
