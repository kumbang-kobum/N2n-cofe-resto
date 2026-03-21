<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name','Kasir Cafe') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('n2Nlogo.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('n2Nlogo.png') }}">
  <script>
    (() => {
      if (localStorage.getItem('n2n-theme') === 'dark') {
        document.documentElement.classList.add('theme-dark');
      }
    })();
  </script>
  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')
</head>
<body class="min-h-screen font-sans antialiased text-slate-900">
<div x-data="{ open:false }" class="min-h-screen">

  @php
    $settings = \App\Models\Setting::first();
  @endphp
  <header class="sticky top-0 z-30 border-b border-white/50 bg-[linear-gradient(135deg,rgba(15,31,82,0.96),rgba(37,87,190,0.95))] text-white shadow-[0_18px_40px_rgba(15,31,82,0.16)] backdrop-blur">
    <div class="mx-auto flex h-16 w-full max-w-[1440px] items-center justify-between px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3">
        <button @click="open=!open" class="rounded-xl border border-white/10 bg-white/5 p-2 text-lg transition hover:bg-white/10 lg:hidden">☰</button>
        @if (!empty($settings?->logo_path))
          <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="h-10 w-10 rounded-2xl bg-white/10 object-contain p-1.5 shadow-inner">
        @endif
        <div>
          <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-100/70">n2N Workspace</div>
          <div class="font-semibold tracking-tight text-white">
            {{ $settings->restaurant_name ?? config('app.name','Kasir Cafe') }}
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="hidden rounded-2xl border border-white/10 bg-white/5 px-3 py-2 sm:block">
          <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-blue-100/70">Akun Aktif</div>
          <div class="text-sm font-medium text-white">{{ auth()->user()->name }}</div>
        </div>
        <button type="button" data-theme-toggle class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20">
          Dark Mode
        </button>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20">Logout</button>
        </form>
      </div>
    </div>
  </header>

  <div class="mx-auto w-full max-w-[1440px] px-4 py-5 sm:px-6 lg:px-8 lg:py-7">
    <div class="flex gap-6">
      <aside class="hidden w-72 shrink-0 lg:block">
        @include('partials.sidebar')
      </aside>

      <div class="lg:hidden">
        <div x-show="open" class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm" @click="open=false"></div>
        <aside x-show="open" class="fixed left-0 top-0 z-50 h-full w-80 max-w-[88vw] p-4">
          <div class="shell-card h-full overflow-hidden bg-[linear-gradient(180deg,#17316f,#102453)] text-blue-50">
            <div class="flex items-center justify-between border-b border-white/10 px-4 py-4">
              <div>
                <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/60">Navigation</div>
                <div class="font-semibold text-white">Menu Dashboard</div>
              </div>
              <button @click="open=false" class="rounded-xl border border-white/10 bg-white/5 p-2 transition hover:bg-white/10">✕</button>
            </div>
            <div class="h-[calc(100%-73px)] overflow-y-auto p-3">
              @include('partials.sidebar')
            </div>
          </div>
        </aside>
      </div>

      <main class="min-w-0 flex-1 pb-8">
        @if(session('status'))
          <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
            {{ session('status') }}
          </div>
        @endif

        @yield('content')
      </main>
    </div>
  </div>
</div>

<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
@stack('scripts')
</body>
</html>
