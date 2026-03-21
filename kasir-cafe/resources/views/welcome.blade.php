<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>{{ config('app.name','n2N Kasir Cafe') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('n2Nlogo.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('n2Nlogo.png') }}">
  <script>
    (() => {
      if (localStorage.getItem('n2n-theme') === 'dark') {
        document.documentElement.classList.add('theme-dark');
      }
    })();
  </script>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
  $settings = \App\Models\Setting::first();
@endphp
<body class="bg-slate-50 text-slate-900">
  <div class="min-h-screen flex flex-col">

    {{-- TOP NAV --}}
    <header class="border-b bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
          @if (!empty($settings?->logo_path))
            <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="w-9 h-9 object-contain bg-blue-600/10 rounded-full p-1">
          @else
            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
              {{ strtoupper(substr(config('app.name','KC'),0,2)) }}
            </div>
          @endif
          <div>
            <div class="font-semibold text-blue-700 text-sm sm:text-base">
              {{ $settings->restaurant_name ?? config('app.name','Kasir Cafe') }}
            </div>
            <div class="text-xs text-slate-500">
              {{ $settings->restaurant_address ?? 'n2N Sistem Kasir & Stok untuk Cafe & Resto' }}
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button type="button" data-theme-toggle
             class="inline-flex px-3 py-2 rounded border border-slate-200 text-slate-700 text-sm hover:bg-slate-50">
            Dark Mode
          </button>
          <a href="{{ route('tv.information') }}"
             class="hidden sm:inline-flex px-3 py-2 rounded border border-slate-200 text-slate-700 text-sm hover:bg-slate-50">
            TV Informasi
          </a>
          <a href="{{ route('login') }}"
             class="hidden sm:inline-flex px-3 py-2 rounded border border-blue-600 text-blue-600 text-sm hover:bg-blue-50">
            Login Admin / Kasir / Manager
          </a>
          {{-- Registrasi dinonaktifkan --}}
        </div>
      </div>
    </header>

    {{-- HERO SECTION --}}
    <section class="overflow-hidden bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.18),transparent_22%),radial-gradient(circle_at_bottom_right,rgba(96,165,250,0.28),transparent_24%),linear-gradient(135deg,#0f172a_0%,#1d4ed8_52%,#2563eb_100%)] text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 grid gap-8 lg:grid-cols-[1.15fr_0.85fr] items-center">
        <div>
          <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-50/90 shadow-sm backdrop-blur">
            n2N Cafe & Resto Suite
          </div>
          <h1 class="mt-5 text-3xl sm:text-4xl lg:text-[3.4rem] font-bold leading-[1.05] tracking-tight">
            Satu sistem untuk
            <span class="text-blue-100">kasir, stok bahan, resep,</span>
            dan laporan resto.
          </h1>
          <p class="mt-5 text-sm sm:text-base text-blue-100/90 mb-7 max-w-2xl leading-7">
            Alur kerja dirancang agar operasional dapur, transaksi kasir, pengeluaran, payroll, dan laporan keuangan
            tetap sinkron. Cocok untuk cafe dan resto yang ingin sistem yang rapi, cepat, dan mudah diawasi.
          </p>

          <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('login') }}"
               class="px-4 py-3 rounded-xl bg-white text-blue-700 text-sm font-semibold shadow-[0_12px_28px_rgba(15,23,42,0.18)] hover:bg-blue-50">
              Masuk ke Dashboard
            </a>
            <a href="#katalog"
               class="px-4 py-3 rounded-xl border border-blue-100/40 bg-white/5 text-sm text-blue-50 hover:bg-white/10">
              Lihat katalog menu
            </a>
            <a href="#alur"
               class="px-4 py-3 rounded-xl border border-blue-100/40 bg-white/5 text-sm text-blue-50 hover:bg-white/10">
              Lihat alur kerja
            </a>
            <a href="{{ route('tv.information') }}"
               class="px-4 py-3 rounded-xl border border-blue-100/40 bg-white/5 text-sm text-blue-50 hover:bg-white/10">
              TV informasi
            </a>
          </div>

          <div class="mt-8 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-4 backdrop-blur">
              <div class="text-[11px] uppercase tracking-[0.22em] text-blue-100/70">Operasional</div>
              <div class="mt-2 text-lg font-semibold text-white">POS cepat & open bill</div>
              <div class="mt-1 text-sm text-blue-100/80">Dine-in, takeaway, hold bill, dan pembayaran akhir dalam satu layar.</div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-4 backdrop-blur">
              <div class="text-[11px] uppercase tracking-[0.22em] text-blue-100/70">Persediaan</div>
              <div class="mt-2 text-lg font-semibold text-white">Stok batch & FEFO</div>
              <div class="mt-1 text-sm text-blue-100/80">Receiving, expired, stock opname, dan nilai stok lebih mudah diawasi.</div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-4 backdrop-blur">
              <div class="text-[11px] uppercase tracking-[0.22em] text-blue-100/70">Analitik</div>
              <div class="mt-2 text-lg font-semibold text-white">Laporan transparan</div>
              <div class="mt-1 text-sm text-blue-100/80">Omzet, HPP, makan karyawan, payroll, dan beban operasional tersaji terpisah.</div>
            </div>
          </div>
        </div>

        <div class="rounded-[28px] border border-white/10 bg-white/10 p-5 sm:p-6 shadow-[0_28px_80px_rgba(15,23,42,0.28)] backdrop-blur">
          <div class="flex items-center justify-between gap-3">
            <div>
              <div class="text-[11px] uppercase tracking-[0.24em] text-blue-100/70">Ringkasan Sistem</div>
              <div class="mt-2 text-2xl font-semibold">Kontrol resto lebih terpusat</div>
            </div>
            <div class="rounded-full border border-emerald-300/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-100">
              Sinkron realtime
            </div>
          </div>

          <div class="mt-5 grid gap-3 sm:grid-cols-2 text-xs sm:text-sm">
            <div class="rounded-2xl border border-white/10 bg-slate-950/15 px-4 py-4">
              <div class="text-blue-100/70 mb-1 uppercase tracking-[0.18em] text-[11px]">Input Bahan & Stok</div>
              <div class="text-white font-semibold">Batch, FEFO, expired, opname</div>
              <div class="text-blue-100/80 mt-2 leading-6">
                Persediaan lebih akurat karena setiap receiving dan koreksi nilai stok tercatat.
              </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-950/15 px-4 py-4">
              <div class="text-blue-100/70 mb-1 uppercase tracking-[0.18em] text-[11px]">Menu & Resep</div>
              <div class="text-white font-semibold">Resep per porsi yang konsisten</div>
              <div class="text-blue-100/80 mt-2 leading-6">
                Setiap penjualan atau transaksi internal langsung mengurangi stok bahan sesuai resep.
              </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-950/15 px-4 py-4">
              <div class="text-blue-100/70 mb-1 uppercase tracking-[0.18em] text-[11px]">Kasir (POS)</div>
              <div class="text-white font-semibold">Layar kerja cepat dan ringkas</div>
              <div class="text-blue-100/80 mt-2 leading-6">
                Dirancang untuk transaksi cepat di laptop, tablet, maupun layar sentuh.
              </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-950/15 px-4 py-4">
              <div class="text-blue-100/70 mb-1 uppercase tracking-[0.18em] text-[11px]">Laporan & Keuangan</div>
              <div class="text-white font-semibold">Omzet, HPP, payroll, dan beban</div>
              <div class="text-blue-100/80 mt-2 leading-6">
                Data keuangan dipisahkan dengan jelas agar pemilik bisa membaca kinerja usaha secara transparan.
              </div>
            </div>
          </div>

          <div class="mt-5 rounded-2xl border border-white/10 bg-black/10 px-4 py-4">
            <div class="grid gap-3 sm:grid-cols-3">
              <div>
                <div class="text-2xl font-bold text-white">1 layar</div>
                <div class="mt-1 text-sm text-blue-100/75">untuk transaksi kasir aktif</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-white">FEFO</div>
                <div class="mt-1 text-sm text-blue-100/75">untuk batch bahan yang sensitif expired</div>
              </div>
              <div>
                <div class="text-2xl font-bold text-white">Excel</div>
                <div class="mt-1 text-sm text-blue-100/75">untuk audit dan transparansi laporan</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="-mt-1 py-8 sm:py-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-[28px] border border-slate-200 bg-white p-5 sm:p-6 shadow-[0_20px_50px_rgba(15,23,42,0.06)]">
          <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
              <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Highlights</div>
              <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Keunggulan Sistem</h2>
              <p class="mt-2 max-w-3xl text-sm sm:text-base text-slate-600">
                Dirancang untuk operasional resto yang butuh kecepatan transaksi, kontrol stok yang rapi, dan laporan yang mudah diaudit.
              </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
              Fokus utama: <span class="font-semibold text-slate-900">praktis dipakai, jelas dibaca, dan mudah diawasi.</span>
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
              <div class="text-sm font-semibold text-slate-900">Kasir lebih cepat</div>
              <div class="mt-2 text-sm leading-6 text-slate-600">
                Katalog menu, keranjang, open bill, dan pembayaran final dirancang untuk alur kerja kasir yang ringkas.
              </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
              <div class="text-sm font-semibold text-slate-900">Stok lebih akurat</div>
              <div class="mt-2 text-sm leading-6 text-slate-600">
                Receiving, FEFO, expired, stock opname, dan resep saling terhubung agar persediaan lebih terkontrol.
              </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
              <div class="text-sm font-semibold text-slate-900">Biaya lebih transparan</div>
              <div class="mt-2 text-sm leading-6 text-slate-600">
                Pengeluaran harian, payroll, makan karyawan, dan transaksi internal dicatat terpisah dari omzet.
              </div>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
              <div class="text-sm font-semibold text-slate-900">Laporan siap audit</div>
              <div class="mt-2 text-sm leading-6 text-slate-600">
                Excel export, top menu, audit log, dan rekap harian sampai bulanan memudahkan evaluasi usaha.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- KATALOG MENU --}}
    <section id="katalog" class="py-12 sm:py-14">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
          <div>
            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Menu Showcase</div>
            <h2 class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900">
              Katalog Menu
            </h2>
            <p class="mt-2 max-w-3xl text-sm sm:text-base text-slate-600">
              Ini adalah menu aktif yang tampil di sistem. Harga di bawah mengikuti master produk, sehingga yang terlihat di landing tetap sinkron dengan kasir dan laporan.
            </p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
            Admin dapat menambah, mengubah harga, dan mengganti gambar dari dashboard.
          </div>
        </div>

        @if($products->isNotEmpty())
          <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($products as $product)
              <div class="group overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(15,23,42,0.10)]">
                <div class="relative aspect-[4/3] bg-slate-100">
                  @if($product->image_path)
                    <img src="{{ asset('storage/'.$product->image_path) }}"
                         alt="{{ $product->name }}"
                         class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                  @else
                    <div class="flex h-full w-full items-center justify-center text-xs text-slate-400">
                      Gambar belum diunggah
                    </div>
                  @endif
                  <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/70 to-transparent px-4 pb-3 pt-8">
                    <div class="inline-flex rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/90 backdrop-blur">
                      Menu Aktif
                    </div>
                  </div>
                </div>
                <div class="flex h-full flex-col p-4 sm:p-5">
                  <div class="text-lg font-semibold tracking-tight text-slate-900">
                    {{ $product->name }}
                  </div>
                  <div class="mt-3 flex items-end justify-between gap-3">
                    <div>
                      <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Harga Jual</div>
                      <div class="mt-1 text-2xl font-bold text-blue-700">
                        Rp {{ number_format($product->price_default,0,',','.') }}
                      </div>
                    </div>
                    <div class="rounded-2xl bg-blue-50 px-3 py-2 text-right">
                      <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-500">Status</div>
                      <div class="mt-1 text-sm font-semibold text-blue-700">Siap dijual</div>
                    </div>
                  </div>
                  <div class="mt-4 text-[12px] leading-6 text-slate-500">
                    Terhubung ke resep & stok bahan di dapur. Penjualan melalui kasir akan mengurangi stok otomatis.
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="rounded-2xl border border-slate-200 bg-white px-4 py-6 text-sm text-slate-500 shadow-sm">
            Belum ada menu yang aktif. Login sebagai admin &gt; buka menu
            <span class="font-semibold">Produk</span> untuk menambahkan menu dan mengunggah gambar.
          </div>
        @endif
      </div>
    </section>

    {{-- ALUR KERJA --}}
    <section id="alur" class="pb-12 sm:pb-14">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
          <div>
            <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-600">Workflow</div>
            <h2 class="mt-2 text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900">
              Alur Kerja Sistem
            </h2>
            <p class="mt-2 max-w-3xl text-sm sm:text-base text-slate-600">
              Halaman ini menjelaskan urutan kerja yang benar, dari setup bahan sampai laporan akhir.
              Jika alur diikuti, stok, HPP, omzet, pengeluaran, payroll, dan laporan keuangan akan tetap sinkron.
            </p>
          </div>
          <a href="{{ route('login') }}"
             class="hidden sm:inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
            Masuk ke Dashboard
          </a>
        </div>

        <div class="mb-6 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
              <div>
                <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-600">Langkah Utama</div>
                <div class="mt-1 text-lg font-semibold text-slate-900">Urutan operasional harian</div>
              </div>
              <div class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                Dari setup ke laporan
              </div>
            </div>

            <div class="space-y-4">
              <div class="flex gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-600 font-semibold text-white">1</div>
                <div>
                  <div class="font-semibold text-slate-900">Setup master data</div>
                  <div class="mt-1 text-sm text-slate-600">
                    Admin menyiapkan <b>satuan, konversi unit, bahan, produk/menu, resep BOM, dan master karyawan</b>.
                  </div>
                  <div class="mt-2 text-xs text-slate-500">Output: struktur produk dan stok siap dipakai transaksi.</div>
                </div>
              </div>

              <div class="flex gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-600 font-semibold text-white">2</div>
                <div>
                  <div class="font-semibold text-slate-900">Receiving stok dan koreksi awal</div>
                  <div class="mt-1 text-sm text-slate-600">
                    Bahan masuk dicatat lewat <b>Receiving Stok</b> lengkap dengan qty, unit, cost, dan expired. Jika perlu koreksi, gunakan <b>Stock Opname</b>.
                  </div>
                  <div class="mt-2 text-xs text-slate-500">Output: stok aktif, nilai stok, dan batch FEFO terbentuk dengan benar.</div>
                </div>
              </div>

              <div class="flex gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-600 font-semibold text-white">3</div>
                <div>
                  <div class="font-semibold text-slate-900">Kasir memproses transaksi</div>
                  <div class="mt-1 text-sm text-slate-600">
                    Kasir membuka transaksi, memilih menu, menyimpan open bill bila perlu, lalu finalisasi pembayaran.
                  </div>
                  <div class="mt-2 text-xs text-slate-500">Output: penjualan tercatat, nota tercetak, status transaksi jelas.</div>
                </div>
              </div>

              <div class="flex gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-600 font-semibold text-white">4</div>
                <div>
                  <div class="font-semibold text-slate-900">Sistem kurangi stok otomatis</div>
                  <div class="mt-1 text-sm text-slate-600">
                    Saat transaksi dibayar atau transaksi internal diposting, sistem mengurangi bahan sesuai <b>resep + konversi unit + FEFO</b>.
                  </div>
                  <div class="mt-2 text-xs text-slate-500">Output: HPP, laba kotor, dan stok bahan tetap sinkron.</div>
                </div>
              </div>

              <div class="flex gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-600 font-semibold text-white">5</div>
                <div>
                  <div class="font-semibold text-slate-900">Catat biaya operasional dan transaksi internal</div>
                  <div class="mt-1 text-sm text-slate-600">
                    Pengeluaran kas, makan karyawan, payroll, disposal, refund, dan approval dicatat agar laporan keuangan tidak bias.
                  </div>
                  <div class="mt-2 text-xs text-slate-500">Output: pengeluaran dan beban perusahaan tercatat terpisah dari omzet.</div>
                </div>
              </div>

              <div class="flex gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-600 font-semibold text-white">6</div>
                <div>
                  <div class="font-semibold text-slate-900">Pantau laporan dan ekspor data</div>
                  <div class="mt-1 text-sm text-slate-600">
                    Owner atau manager memantau penjualan, top menu, stok, audit log, payroll, dan laporan keuangan harian sampai bulanan.
                  </div>
                  <div class="mt-2 text-xs text-slate-500">Output: keputusan operasional lebih cepat dan transparan.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-600">Pembagian Peran</div>
              <div class="mt-3 space-y-3 text-sm">
                <div class="rounded-xl bg-slate-50 p-3">
                  <div class="font-semibold text-slate-900">Admin</div>
                  <div class="mt-1 text-slate-600">Mengatur master data, pengaturan resto, stok, inventaris, dan laporan penuh.</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                  <div class="font-semibold text-slate-900">Manager</div>
                  <div class="mt-1 text-slate-600">Mengawasi operasional, approval biaya, payroll, dan monitoring laporan.</div>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                  <div class="font-semibold text-slate-900">Kasir</div>
                  <div class="mt-1 text-slate-600">Fokus pada POS, pengeluaran kas terbatas, dan transaksi makan karyawan.</div>
                </div>
              </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-[linear-gradient(180deg,#0f172a,#172554)] p-5 text-white shadow-sm">
              <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-200/70">Hasil Akhir</div>
              <div class="mt-3 grid gap-3 text-sm">
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                  <div class="font-semibold">Stok lebih akurat</div>
                  <div class="mt-1 text-blue-100/80">Karena bahan, resep, receiving, dan opname saling terhubung.</div>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                  <div class="font-semibold">Transaksi kasir lebih cepat</div>
                  <div class="mt-1 text-blue-100/80">POS, open bill, dan pembayaran akhir berjalan dalam satu layar kerja.</div>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                  <div class="font-semibold">Laporan lebih transparan</div>
                  <div class="mt-1 text-blue-100/80">Omzet, refund, HPP, makan karyawan, pengeluaran, dan payroll terbaca terpisah.</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 text-xs sm:text-sm text-slate-600">
          <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3">
            <b class="text-slate-900">Tip 1:</b> Isi <b>konversi unit</b> (kg↔g, liter↔ml) sebelum membuat resep.
          </div>
          <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3">
            <b class="text-slate-900">Tip 2:</b> Masukkan <b>receiving stok</b> dengan qty, unit, dan cost yang konsisten.
          </div>
          <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3">
            <b class="text-slate-900">Tip 3:</b> Gunakan <b>open bill</b> hanya untuk transaksi yang memang dibayar di akhir.
          </div>
          <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3">
            <b class="text-slate-900">Tip 4:</b> Finalisasi <b>approval pengeluaran dan payroll paid</b> agar laporan bersih akurat.
          </div>
        </div>
      </div>
    </section>

    <footer class="mt-auto border-t border-slate-200 bg-[linear-gradient(180deg,#ffffff,#f8fafc)]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-3">
            @if (!empty($settings?->logo_path))
              <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-11 w-11 rounded-2xl bg-blue-50 object-contain p-1.5">
            @else
              <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-sm font-bold text-white">
                {{ strtoupper(substr(config('app.name','KC'),0,2)) }}
              </div>
            @endif
            <div>
              <div class="text-sm font-semibold text-slate-900">{{ $settings->restaurant_name ?? 'n2N Cafe Resto' }}</div>
              <div class="text-xs text-slate-500">Sistem kasir, stok, resep, dan laporan untuk cafe & resto.</div>
            </div>
          </div>

          <div class="grid gap-1 text-[11px] sm:text-xs text-slate-500 sm:text-right">
            <div>&copy; {{ date('Y') }} n2N. Semua hak cipta dilindungi.</div>
            <div>Dirancang untuk operasional yang rapi, cepat, dan mudah dipantau.</div>
          </div>
        </div>
      </div>
    </footer>
  </div>
  <script>
    (() => {
      const buttons = document.querySelectorAll('[data-theme-toggle]');
      const sync = () => {
        const dark = document.documentElement.classList.contains('theme-dark');
        buttons.forEach((button) => {
          button.textContent = dark ? 'Light Mode' : 'Dark Mode';
        });
      };

      buttons.forEach((button) => {
        button.addEventListener('click', () => {
          document.documentElement.classList.toggle('theme-dark');
          localStorage.setItem('n2n-theme', document.documentElement.classList.contains('theme-dark') ? 'dark' : 'light');
          sync();
        });
      });

      sync();
    })();
  </script>
</body>
</html>
