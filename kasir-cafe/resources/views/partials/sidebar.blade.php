<nav class="space-y-1">

  {{-- DASHBOARD --}}
  @if(Route::has('admin.dashboard'))
    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
      Dashboard
    </a>
  @endif

  {{-- MASTER DATA --}}
  <div class="mt-4 text-xs font-semibold text-gray-500 uppercase">Master Data</div>

  @if(Route::has('admin.units.index'))
    <a href="{{ route('admin.units.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
      Units
    </a>
  @endif

  @if(Route::has('admin.items.index'))
    <a href="{{ route('admin.items.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
      Items / Bahan
    </a>
  @endif

  @if(Route::has('admin.products.index'))
    <a href="{{ route('admin.products.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
      Produk / Menu
    </a>
  @endif

  {{-- OPERASIONAL --}}
  <div class="mt-4 text-xs font-semibold text-gray-500 uppercase">Operasional</div>

  @if(Route::has('admin.recipes.index'))
    <a href="{{ route('admin.recipes.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
      Resep / BOM
    </a>
  @endif

  @if(Route::has('admin.receivings.index'))
    <a href="{{ route('admin.receivings.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
      Receiving Stok
    </a>
  @endif

  @if(Route::has('admin.stock.index'))
    <a href="{{ route('admin.stock.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
      Stok Bahan
    </a>
  @endif

  @if(Route::has('admin.expired.index'))
    <a href="{{ route('admin.expired.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 text-red-600">
      Expired Disposal
    </a>
  @endif

  @if(Route::has('admin.stock_opname.index'))
  <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('admin.stock_opname.index') }}">Stock Opname</a>
@endif

  {{-- LAPORAN --}}
  <div class="mt-4 text-xs font-semibold text-gray-500 uppercase">Laporan</div>

  @if(Route::has('admin.reports.sales'))
    <a href="{{ route('admin.reports.sales') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
      Laporan Penjualan
    </a>
  @endif

  <a href="{{ route('admin.reports.opname_variance') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
  Laporan Selisih Opname
  </a>

</nav>