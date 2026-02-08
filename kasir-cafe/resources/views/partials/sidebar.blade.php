{{-- resources/views/patrials/sidebar.blade.php --}}
<aside class="w-full sm:w-64 bg-white border-r min-h-screen">
  <div class="px-4 py-4 text-lg font-semibold text-blue-700">
    Laravel
  </div>

  <nav class="px-2 pb-6 space-y-6 text-sm">

    {{-- MAIN --}}
    <div>
      <a href="{{ route('admin.dashboard') }}"
         class="flex items-center px-3 py-2 rounded
                {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
        Dashboard
      </a>
    </div>

    {{-- MASTER DATA --}}
    <div>
      <div class="px-3 mb-1 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
        Master Data
      </div>

      {{-- PRODUK / MENU (BARU) --}}
      <a href="{{ route('admin.products.index') }}"
         class="flex items-center px-3 py-2 rounded
                {{ request()->routeIs('admin.products.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
        Produk / Menu
      </a>

      {{-- Contoh: Resep / BOM (kalau sudah ada route-nya) --}}
      <a href="{{ route('admin.recipes.index') }}"
         class="flex items-center px-3 py-2 rounded
                {{ request()->routeIs('admin.recipes.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
        Resep / BOM
      </a>

      {{-- Item / Stok Bahan (sesuaikan route-nya dengan projectmu) --}}
      <a href="{{ route('admin.items.index') }}"
         class="flex items-center px-3 py-2 rounded
                {{ request()->routeIs('admin.items.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
        Stok Bahan
      </a>
    </div>

    {{-- OPERASIONAL --}}
    <div>
      <div class="px-3 mb-1 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
        Operasional
      </div>

      <a href="{{ route('admin.receiving.index') }}"
         class="flex items-center px-3 py-2 rounded
                {{ request()->routeIs('admin.receiving.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
        Receiving Stok
      </a>

      <a href="{{ route('admin.expired_disposal.index') }}"
         class="flex items-center px-3 py-2 rounded
                {{ request()->routeIs('admin.expired_disposal.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
        Expired Disposal
      </a>

      <a href="{{ route('admin.stock_opname.index') }}"
         class="flex items-center px-3 py-2 rounded
                {{ request()->routeIs('admin.stock_opname.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
        Stock Opname
      </a>
    </div>

    {{-- LAPORAN --}}
    <div>
      <div class="px-3 mb-1 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
        Laporan
      </div>

      <a href="{{ route('admin.report.sales') }}"
         class="flex items-center px-3 py-2 rounded
                {{ request()->routeIs('admin.report.sales') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
        Laporan Penjualan
      </a>

      <a href="{{ route('admin.report.stock_opname_diff') }}"
         class="flex items-center px-3 py-2 rounded
                {{ request()->routeIs('admin.report.stock_opname_diff') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
        Laporan Selisih Opname
      </a>
    </div>

  </nav>
</aside>