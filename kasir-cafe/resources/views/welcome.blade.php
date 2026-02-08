<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>{{ config('app.name','Kasir Cafe') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
  <div class="min-h-screen flex flex-col">

    {{-- TOP NAV --}}
    <header class="border-b bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
            {{ strtoupper(substr(config('app.name','KC'),0,2)) }}
          </div>
          <div>
            <div class="font-semibold text-blue-700 text-sm sm:text-base">
              {{ config('app.name','Kasir Cafe') }}
            </div>
            <div class="text-xs text-slate-500">
              Sistem Kasir & Stok untuk Cafe & Resto
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <a href="{{ route('login') }}"
             class="hidden sm:inline-flex px-3 py-2 rounded border border-blue-600 text-blue-600 text-sm hover:bg-blue-50">
            Login Admin / Kasir
          </a>
          @if (Route::has('register'))
            <a href="{{ route('register') }}"
               class="px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
              Daftar
            </a>
          @endif
        </div>
      </div>
    </header>

    {{-- HERO SECTION --}}
    <section class="bg-gradient-to-r from-blue-700 to-blue-500 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 lg:py-16 grid gap-8 lg:grid-cols-2 items-center">
        <div>
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight mb-4">
            Dashboard Kasir, Stok Bahan, dan Resep<br class="hidden sm:block">
            dalam satu aplikasi.
          </h1>
          <p class="text-sm sm:text-base text-blue-100 mb-6 max-w-xl">
            Pantau penjualan makanan & minuman, kontrol stok bahan dapur (BHP),
            atur resep, dan jalankan kasir dengan lebih rapi. Cocok untuk cafe dan resto skala kecil sampai menengah.
          </p>

          <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('login') }}"
               class="px-4 py-2.5 rounded bg-white text-blue-700 text-sm font-semibold hover:bg-blue-50">
              Masuk sebagai Admin / Kasir
            </a>
            <a href="#katalog"
               class="px-4 py-2.5 rounded border border-blue-100 text-sm text-blue-50 hover:bg-blue-600/40">
              Lihat katalog menu
            </a>
          </div>
        </div>

        <div class="bg-white/10 rounded-2xl p-4 sm:p-6 backdrop-blur">
          <div class="text-xs uppercase tracking-wide text-blue-100 mb-3">
            Ringkasan Sistem
          </div>
          <div class="grid grid-cols-2 gap-3 text-xs sm:text-sm">
            <div class="bg-white/10 rounded-xl px-3 py-3">
              <div class="text-blue-100 mb-1">Input Bahan & Stok</div>
              <div class="text-white font-semibold">Stok dapur realtime</div>
              <div class="text-blue-100 mt-1">
                FEFO, batch, expired, opname.
              </div>
            </div>
            <div class="bg-white/10 rounded-xl px-3 py-3">
              <div class="text-blue-100 mb-1">Menu & Resep</div>
              <div class="text-white font-semibold">Resep per porsi</div>
              <div class="text-blue-100 mt-1">
                Otomatis kurangi stok bahan.
              </div>
            </div>
            <div class="bg-white/10 rounded-xl px-3 py-3">
              <div class="text-blue-100 mb-1">Kasir (POS)</div>
              <div class="text-white font-semibold">Simple & cepat</div>
              <div class="text-blue-100 mt-1">
                Cocok untuk laptop & tablet.
              </div>
            </div>
            <div class="bg-white/10 rounded-xl px-3 py-3">
              <div class="text-blue-100 mb-1">Laporan</div>
              <div class="text-white font-semibold">Penjualan & COGS</div>
              <div class="text-blue-100 mt-1">
                Pantau performa harian / bulanan.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- KATALOG MENU --}}
    <section id="katalog" class="py-10 sm:py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-slate-900">
              Katalog Menu
            </h2>
            <p class="text-xs sm:text-sm text-slate-500">
              Beberapa menu yang tersedia di sistem. Admin dapat menambah / ubah dari dashboard.
            </p>
          </div>
        </div>

        @if($products->isNotEmpty())
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($products as $product)
              <div class="bg-white border border-slate-200 rounded-xl overflow-hidden flex flex-col">
                <div class="aspect-[4/3] bg-slate-100">
                  @if($product->image_path)
                    <img src="{{ asset('storage/'.$product->image_path) }}"
                         alt="{{ $product->name }}"
                         class="w-full h-full object-cover">
                  @else
                    <div class="w-full h-full flex items-center justify-center text-xs text-slate-400">
                      Gambar belum diunggah
                    </div>
                  @endif
                </div>
                <div class="p-3 sm:p-4 flex-1 flex flex-col">
                  <div class="text-sm font-semibold text-slate-900 mb-1">
                    {{ $product->name }}
                  </div>
                  <div class="text-sm text-blue-700 font-bold">
                    Rp {{ number_format($product->price_default,0,',','.') }}
                  </div>
                  <div class="mt-2 text-[11px] sm:text-xs text-slate-500">
                    Terhubung ke resep & stok bahan di dapur. Penjualan melalui kasir akan mengurangi stok otomatis.
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="bg-white border rounded-xl px-4 py-6 text-sm text-slate-500">
            Belum ada menu yang aktif. Login sebagai admin &gt; buka menu
            <span class="font-semibold">Produk</span> untuk menambahkan menu dan mengunggah gambar.
          </div>
        @endif
      </div>
    </section>

    {{-- FITUR SISTEM --}}
    <section class="pb-10 sm:pb-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-lg sm:text-xl font-semibold text-slate-900 mb-4">
          Alur Kerja Sistem
        </h2>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 text-sm">
          <div class="bg-white border rounded-xl p-4 flex flex-col">
            <div class="text-blue-600 font-semibold mb-1">1. Input Bahan Makanan</div>
            <div class="text-slate-600">
              Admin input <b>Item/BHP</b>, satuan (kg, gram, pcs), dan stok awal
              melalui penerimaan barang & stok opname.
            </div>
          </div>

          <div class="bg-white border rounded-xl p-4 flex flex-col">
            <div class="text-blue-600 font-semibold mb-1">2. Input Menu & Resep</div>
            <div class="text-slate-600">
              Buat <b>Produk/Menu</b> (misal Nasi Ayam, Kopi Susu), lalu set
              <b>resep</b> per porsi (berapa gram ayam, berapa ml susu, dsb).
            </div>
          </div>

          <div class="bg-white border rounded-xl p-4 flex flex-col">
            <div class="text-blue-600 font-semibold mb-1">3. Kasir Melayani Pesanan</div>
            <div class="text-slate-600">
              Kasir gunakan <b>POS</b> untuk input pesanan. Setelah pembayaran,
              stok bahan di dapur otomatis berkurang berdasarkan resep.
            </div>
          </div>

          <div class="bg-white border rounded-xl p-4 flex flex-col">
            <div class="text-blue-600 font-semibold mb-1">4. Monitoring & Laporan</div>
            <div class="text-slate-600">
              Owner bisa cek <b>stok bahan, stok opname, penjualan, dan COGS</b>
              untuk memastikan tidak ada kecurangan dan pemborosan.
            </div>
          </div>
        </div>

        <div class="mt-6">
          <a href="{{ route('login') }}"
             class="inline-flex items-center px-4 py-2.5 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
            Masuk ke Dashboard Admin
          </a>
        </div>
      </div>
    </section>

    <footer class="mt-auto border-t bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-between text-[11px] sm:text-xs text-slate-500">
        <div>&copy; {{ date('Y') }} {{ config('app.name','Kasir Cafe') }}</div>
        <div>Sistem kasir & stok untuk cafe & resto</div>
      </div>
    </footer>
  </div>
</body>
</html>