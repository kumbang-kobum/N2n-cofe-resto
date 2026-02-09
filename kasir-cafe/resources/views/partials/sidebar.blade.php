{{-- resources/views/partials/sidebar.blade.php --}}

<div class="flex flex-col w-full bg-blue-800 text-blue-50"
     x-data="{
        open: {
            adminMain: true, adminMaster: false, adminOps: false, adminReports: false,
            managerMain: true, managerReports: false,
            cashierMain: true
        }
     }">
    {{-- Brand / Logo --}}
    <div class="flex items-center h-16 px-4 border-b border-blue-700/60 bg-blue-900">
        <div class="text-lg font-semibold text-white">
            {{ config('app.name', 'Kasir Cafe') }}
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4 text-sm space-y-6">

        {{-- ====================== ADMIN ====================== --}}
        @role('admin')
            <div>
                <button type="button"
                        @click="open.adminMain = !open.adminMain"
                        class="w-full flex items-center justify-between text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1">
                    <span>Main</span>
                    <span class="text-xs" x-text="open.adminMain ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminMain" class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Admin Dashboard</span>
                    </a>
                </div>
            </div>

            <div>
                <button type="button"
                        @click="open.adminMaster = !open.adminMaster"
                        class="w-full flex items-center justify-between text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1 mt-4">
                    <span>Master Data</span>
                    <span class="text-xs" x-text="open.adminMaster ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminMaster" class="space-y-1">
                    <a href="{{ route('admin.products.index') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.products.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Produk / Menu</span>
                    </a>
                    <a href="{{ route('admin.recipes.index') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.recipes.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Resep / BOM</span>
                    </a>
                    <a href="{{ route('admin.items.index') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.items.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Stok Bahan</span>
                    </a>
                    <a href="{{ route('admin.units.index') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.units.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Satuan</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.users.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Pengguna</span>
                    </a>
                    <a href="{{ route('admin.settings.edit') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.settings.edit') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Pengaturan Resto</span>
                    </a>
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
            </div>

            <div>
                <button type="button"
                        @click="open.adminOps = !open.adminOps"
                        class="w-full flex items-center justify-between text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1 mt-4">
                    <span>Operasional</span>
                    <span class="text-xs" x-text="open.adminOps ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminOps" class="space-y-1">
                    <a href="{{ route('admin.receivings.index') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.receivings.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Receiving Stok</span>
                    </a>
                    <a href="{{ route('admin.expired.index') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.expired.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Expired Disposal</span>
                    </a>
                    <a href="{{ route('admin.stock.index') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.stock.index') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Stok Saat Ini</span>
                    </a>
                    <a href="{{ route('admin.stock_opname.index') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('admin.stock_opname.*') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Stock Opname</span>
                    </a>
                    <a href="{{ route('cashier.pos') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('cashier.pos') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>POS Kasir</span>
                    </a>
                </div>
            </div>

            <div>
                <button type="button"
                        @click="open.adminReports = !open.adminReports"
                        class="w-full flex items-center justify-between text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1 mt-4">
                    <span>Laporan</span>
                    <span class="text-xs" x-text="open.adminReports ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminReports" class="space-y-1">
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
            </div>
        @endrole

        {{-- ====================== MANAGER ====================== --}}
        @role('manager')
            <div>
                <button type="button"
                        @click="open.managerMain = !open.managerMain"
                        class="w-full flex items-center justify-between text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1">
                    <span>Manager</span>
                    <span class="text-xs" x-text="open.managerMain ? '−' : '+'"></span>
                </button>
                <div x-show="open.managerMain" class="space-y-1">
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
                </div>

                <button type="button"
                        @click="open.managerReports = !open.managerReports"
                        class="w-full flex items-center justify-between text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1 mt-4">
                    <span>Laporan</span>
                    <span class="text-xs" x-text="open.managerReports ? '−' : '+'"></span>
                </button>
                <div x-show="open.managerReports" class="space-y-1">
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
                </div>

                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2 mt-2
                          {{ request()->routeIs('profile.edit') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Ubah Password</span>
                </a>
            </div>
        @endrole

        {{-- ====================== CASHIER ====================== --}}
        @role('cashier')
            <div>
                <button type="button"
                        @click="open.cashierMain = !open.cashierMain"
                        class="w-full flex items-center justify-between text-[11px] font-semibold tracking-widest text-blue-200/80 uppercase mb-1">
                    <span>Kasir</span>
                    <span class="text-xs" x-text="open.cashierMain ? '−' : '+'"></span>
                </button>
                <div x-show="open.cashierMain" class="space-y-1">
                    <a href="{{ route('cashier.dashboard') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('cashier.dashboard') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('cashier.pos') }}"
                       class="flex items-center gap-2 rounded-md px-3 py-2
                              {{ request()->routeIs('cashier.pos') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        <span>POS Kasir</span>
                    </a>
                    <a href="{{ route('cashier.reports.sales') }}"
                       class="block px-3 py-2 rounded-md text-sm
                              {{ request()->routeIs('cashier.reports.sales') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                        Laporan Penjualan
                    </a>
                </div>

                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 rounded-md px-3 py-2 mt-2
                          {{ request()->routeIs('profile.edit') ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-700/70' }}">
                    <span>Ubah Password</span>
                </a>
            </div>
        @endrole

    </div>
</div>
