{{-- resources/views/partials/sidebar.blade.php --}}

<aside class="hidden md:flex md:flex-col w-64 border-r bg-white/90 backdrop-blur">
    {{-- Brand / Logo --}}
    <div class="flex items-center h-16 px-4 border-b bg-blue-50">
        <div class="text-lg font-semibold text-blue-700">
            {{ config('app.name', 'Kasir Cafe') }}
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4 text-sm text-gray-700 space-y-6">

        {{-- ====================== ADMIN ====================== --}}
        @role('admin')
            {{-- Dashboard --}}
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-gray-400 uppercase mb-1">
                    Main
                </div>

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Admin Dashboard</span>
                </a>
            </div>

            {{-- Master Data --}}
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-gray-400 uppercase mb-1 mt-4">
                    Master Data
                </div>

                {{-- Produk / Menu --}}
                <a href="{{ route('admin.products.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.products.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Produk / Menu</span>
                </a>

                {{-- Resep / BOM --}}
                <a href="{{ route('admin.recipes.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.recipes.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Resep / BOM</span>
                </a>

                {{-- Stok Bahan --}}
                <a href="{{ route('admin.items.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.items.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Stok Bahan</span>
                </a>

                {{-- Satuan --}}
                <a href="{{ route('admin.units.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.units.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Satuan</span>
                </a>

                {{-- Pengguna --}}
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Pengguna</span>
                </a>
            </div>

            {{-- Operasional --}}
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-gray-400 uppercase mb-1 mt-4">
                    Operasional
                </div>

                {{-- Receiving Stok --}}
                <a href="{{ route('admin.receivings.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.receivings.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Receiving Stok</span>
                </a>

                {{-- Expired Disposal --}}
                <a href="{{ route('admin.expired.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.expired.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Expired Disposal</span>
                </a>

                {{-- Stok Saat Ini --}}
                <a href="{{ route('admin.stock.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.stock.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Stok Saat Ini</span>
                </a>

                {{-- Stock Opname --}}
                <a href="{{ route('admin.stock_opname.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.stock_opname.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Stock Opname</span>
                </a>

                {{-- POS Kasir (akses juga dari admin) --}}
                <a href="{{ route('cashier.pos') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('cashier.pos') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>POS Kasir</span>
                </a>
            </div>

            {{-- Laporan --}}
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-gray-400 uppercase mb-1 mt-4">
                    Laporan
                </div>

                <a href="{{ route('admin.reports.sales') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.reports.sales') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Laporan Penjualan</span>
                </a>

                <a href="{{ route('admin.reports.opname_variance') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.reports.opname_variance') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Laporan Selisih Opname</span>
                </a>
            </div>
        @endrole

        {{-- ====================== CASHIER ====================== --}}
        @role('cashier')
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-gray-400 uppercase mb-1">
                    Kasir
                </div>

                {{-- Dashboard Kasir (redirect ke POS) --}}
                <a href="{{ route('cashier.dashboard') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('cashier.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>Dashboard</span>
                </a>

                {{-- POS Kasir --}}
                <a href="{{ route('cashier.pos') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('cashier.pos') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-50' }}">
                    <span>POS Kasir</span>
                </a>

                {{-- Tambahan: menu laporan penjualan kasir --}}
                <a href="{{ route('cashier.reports.sales') }}"
                class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('cashier.reports.sales') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                    Laporan Penjualan
                </a>
            </div>
        @endrole

    </div>
</aside>
