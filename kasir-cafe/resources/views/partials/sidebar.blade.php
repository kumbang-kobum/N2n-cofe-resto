{{-- resources/views/partials/sidebar.blade.php --}}

<div class="flex flex-col w-full bg-blue-800 text-blue-50">
    {{-- Brand / Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-blue-700/60 bg-blue-900">
        <div class="text-lg font-semibold text-white">
            {{ config('app.name', 'Kasir Cafe') }}
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4 text-sm space-y-6">

        {{-- ====================== ADMIN ====================== --}}
        @role('admin')
            {{-- Dashboard --}}
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1">
                    Main
                </div>

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Admin Dashboard</span>
                </a>
            </div>

            {{-- Master Data --}}
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1 mt-4">
                    Master Data
                </div>

                {{-- Produk / Menu --}}
                <a href="{{ route('admin.products.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.products.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Produk / Menu</span>
                </a>

                {{-- Resep / BOM --}}
                <a href="{{ route('admin.recipes.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.recipes.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Resep / BOM</span>
                </a>

                {{-- Stok Bahan --}}
                <a href="{{ route('admin.items.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.items.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Stok Bahan</span>
                </a>

                {{-- Satuan --}}
                <a href="{{ route('admin.units.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.units.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Satuan</span>
                </a>

                {{-- Pengguna --}}
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.users.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Pengguna</span>
                </a>

                {{-- Pengaturan Resto --}}
                <a href="{{ route('admin.settings.edit') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.settings.edit') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Pengaturan Resto</span>
                </a>

                {{-- Inventaris --}}
                <a href="{{ route('admin.assets.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.assets.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Inventaris</span>
                </a>

                <a href="{{ route('admin.asset_categories.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.asset_categories.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Master Kategori</span>
                </a>

                <a href="{{ route('admin.asset_locations.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.asset_locations.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Master Lokasi</span>
                </a>

                <a href="{{ route('admin.asset_incidents.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.asset_incidents.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Kerusakan/Pemusnahan</span>
                </a>
            </div>

            {{-- Operasional --}}
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1 mt-4">
                    Operasional
                </div>

                {{-- Receiving Stok --}}
                <a href="{{ route('admin.receivings.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.receivings.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Receiving Stok</span>
                </a>

                {{-- Expired Disposal --}}
                <a href="{{ route('admin.expired.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.expired.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Expired Disposal</span>
                </a>

                {{-- Stok Saat Ini --}}
                <a href="{{ route('admin.stock.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.stock.index') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Stok Saat Ini</span>
                </a>

                {{-- Stock Opname --}}
                <a href="{{ route('admin.stock_opname.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.stock_opname.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Stock Opname</span>
                </a>

                {{-- POS Kasir (akses juga dari admin) --}}
                <a href="{{ route('cashier.pos') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('cashier.pos') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>POS Kasir</span>
                </a>
            </div>

            {{-- Laporan --}}
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1 mt-4">
                    Laporan
                </div>

                <a href="{{ route('admin.reports.sales') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.reports.sales') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Laporan Penjualan</span>
                </a>

                <a href="{{ route('admin.reports.opname_variance') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.reports.opname_variance') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Laporan Selisih Opname</span>
                </a>

                <a href="{{ route('admin.reports.audit_logs') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('admin.reports.audit_logs') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Audit Log</span>
                </a>
            </div>
        @endrole

        {{-- ====================== MANAGER ====================== --}}
        @role('manager')
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1">
                    Manager
                </div>

                <a href="{{ route('manager.dashboard') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.dashboard') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('manager.products.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.products.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Produk / Menu</span>
                </a>

                <a href="{{ route('manager.recipes.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.recipes.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Resep / BOM</span>
                </a>

                <a href="{{ route('manager.settings.edit') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.settings.edit') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Pengaturan Resto</span>
                </a>

                <a href="{{ route('manager.assets.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.assets.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Inventaris</span>
                </a>

                <a href="{{ route('manager.asset_categories.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.asset_categories.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Master Kategori</span>
                </a>

                <a href="{{ route('manager.asset_locations.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.asset_locations.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Master Lokasi</span>
                </a>

                <a href="{{ route('manager.asset_incidents.index') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.asset_incidents.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Kerusakan/Pemusnahan</span>
                </a>

                <a href="{{ route('manager.reports.sales') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.reports.sales') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Laporan Penjualan</span>
                </a>

                <a href="{{ route('manager.reports.opname_variance') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.reports.opname_variance') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Laporan Selisih Opname</span>
                </a>

                <a href="{{ route('manager.reports.audit_logs') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('manager.reports.audit_logs') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Audit Log</span>
                </a>

                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('profile.edit') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Ubah Password</span>
                </a>
            </div>
        @endrole

        {{-- ====================== CASHIER ====================== --}}
        @role('cashier')
            <div>
                <div class="text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1">
                    Kasir
                </div>

                {{-- Dashboard Kasir (redirect ke POS) --}}
                <a href="{{ route('cashier.dashboard') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('cashier.dashboard') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Dashboard</span>
                </a>

                {{-- POS Kasir --}}
                <a href="{{ route('cashier.pos') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('cashier.pos') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>POS Kasir</span>
                </a>

                {{-- Tambahan: menu laporan penjualan kasir --}}
                <a href="{{ route('cashier.reports.sales') }}"
                class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('cashier.reports.sales') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    Laporan Penjualan
                </a>

                {{-- Ubah Password --}}
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2
                          {{ request()->routeIs('profile.edit') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Ubah Password</span>
                </a>
            </div>
        @endrole

    </div>
</div>
