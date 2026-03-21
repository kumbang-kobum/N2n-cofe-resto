<div
    class="shell-card overflow-hidden bg-[linear-gradient(180deg,#17316f_0%,#102453_100%)] text-blue-50"
    x-data="{
        open: {
            adminMain: true, adminMaster: false, adminOps: false, adminReports: false,
            managerMain: true, managerReports: false,
            cashierMain: true
        }
    }"
>
    @php
        $settings = \App\Models\Setting::first();
        $brandName = $settings->restaurant_name ?? config('app.name', 'Kasir Cafe');
    @endphp

    <div class="border-b border-white/10 bg-white/5 px-5 py-5">
        <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-100/60">Navigation</div>
        <div class="mt-2 text-xl font-semibold tracking-tight text-white">{{ $brandName }}</div>
        <div class="mt-1 text-sm text-blue-100/70">Akses cepat menu operasional dan laporan.</div>
    </div>

    <div class="space-y-5 px-3 py-4 text-sm">
        @role('admin')
            <div class="space-y-2">
                <button type="button" @click="open.adminMain = !open.adminMain" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Main</span>
                    <span class="text-sm" x-text="open.adminMain ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminMain" class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">
                        Admin Dashboard
                    </a>
                </div>
            </div>

            <div class="space-y-2">
                <button type="button" @click="open.adminMaster = !open.adminMaster" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Master Data</span>
                    <span class="text-sm" x-text="open.adminMaster ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminMaster" class="space-y-1">
                    <a href="{{ route('admin.products.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.products.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Produk / Menu</a>
                    <a href="{{ route('admin.recipes.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.recipes.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Resep / BOM</a>
                    <a href="{{ route('admin.items.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.items.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Stok Bahan</a>
                    <a href="{{ route('admin.units.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.units.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Satuan</a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.users.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengguna</a>
                    <a href="{{ route('admin.employees.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.employees.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Karyawan</a>
                    <a href="{{ route('admin.settings.edit') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.settings.edit') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengaturan Resto</a>
                    <a href="{{ route('admin.assets.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.assets.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Inventaris</a>
                    <a href="{{ route('admin.asset_categories.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.asset_categories.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Kategori</a>
                    <a href="{{ route('admin.asset_locations.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.asset_locations.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Lokasi</a>
                    <a href="{{ route('admin.asset_incidents.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.asset_incidents.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kerusakan / Pemusnahan</a>
                </div>
            </div>

            <div class="space-y-2">
                <button type="button" @click="open.adminOps = !open.adminOps" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Operasional</span>
                    <span class="text-sm" x-text="open.adminOps ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminOps" class="space-y-1">
                    <a href="{{ route('admin.receivings.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.receivings.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Receiving Stok</a>
                    <a href="{{ route('admin.expired.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.expired.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Expired Disposal</a>
                    <a href="{{ route('admin.stock.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.stock.index') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Stok Saat Ini</a>
                    <a href="{{ route('admin.stock_opname.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.stock_opname.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Stock Opname</a>
                    <a href="{{ route('admin.payroll.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.payroll.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Penggajian Petugas</a>
                    <a href="{{ route('cashier.pos') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.pos') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">POS Kasir</a>
                </div>
            </div>

            <div class="space-y-2">
                <button type="button" @click="open.adminReports = !open.adminReports" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Laporan</span>
                    <span class="text-sm" x-text="open.adminReports ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminReports" class="space-y-1">
                    <a href="{{ route('admin.reports.sales') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.sales') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Penjualan</a>
                    <a href="{{ route('admin.reports.top_products') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.top_products') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Top 10 Penjualan</a>
                    <a href="{{ route('admin.reports.finance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.finance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Keuangan</a>
                    <a href="{{ route('admin.expenses.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.expenses.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengeluaran Harian</a>
                    <a href="{{ route('admin.reports.opname_variance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.opname_variance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Selisih Opname</a>
                    <a href="{{ route('admin.reports.audit_logs') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.audit_logs') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Audit Log</a>
                </div>
            </div>
        @endrole

        @role('manager')
            <div class="space-y-2">
                <button type="button" @click="open.managerMain = !open.managerMain" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Manager</span>
                    <span class="text-sm" x-text="open.managerMain ? '−' : '+'"></span>
                </button>
                <div x-show="open.managerMain" class="space-y-1">
                    <a href="{{ route('manager.dashboard') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.dashboard') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Dashboard</a>
                    <a href="{{ route('manager.products.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.products.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Produk / Menu</a>
                    <a href="{{ route('manager.recipes.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.recipes.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Resep / BOM</a>
                    <a href="{{ route('manager.settings.edit') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.settings.edit') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengaturan Resto</a>
                    <a href="{{ route('manager.employees.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.employees.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Karyawan</a>
                    <a href="{{ route('manager.assets.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.assets.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Inventaris</a>
                    <a href="{{ route('manager.asset_categories.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.asset_categories.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Kategori</a>
                    <a href="{{ route('manager.asset_locations.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.asset_locations.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Lokasi</a>
                    <a href="{{ route('manager.asset_incidents.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.asset_incidents.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kerusakan / Pemusnahan</a>
                    <a href="{{ route('manager.payroll.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.payroll.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Penggajian Petugas</a>
                </div>

                <button type="button" @click="open.managerReports = !open.managerReports" class="mt-4 flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Laporan</span>
                    <span class="text-sm" x-text="open.managerReports ? '−' : '+'"></span>
                </button>
                <div x-show="open.managerReports" class="space-y-1">
                    <a href="{{ route('manager.reports.sales') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.reports.sales') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Penjualan</a>
                    <a href="{{ route('manager.reports.finance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.reports.finance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Keuangan</a>
                    <a href="{{ route('manager.expenses.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.expenses.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengeluaran Harian</a>
                    <a href="{{ route('manager.reports.opname_variance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.reports.opname_variance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Selisih Opname</a>
                    <a href="{{ route('manager.reports.audit_logs') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.reports.audit_logs') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Audit Log</a>
                </div>
            </div>
        @endrole

        @role('cashier')
            <div class="space-y-2">
                <button type="button" @click="open.cashierMain = !open.cashierMain" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Kasir</span>
                    <span class="text-sm" x-text="open.cashierMain ? '−' : '+'"></span>
                </button>
                <div x-show="open.cashierMain" class="space-y-1">
                    <a href="{{ route('cashier.pos') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.pos') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">POS Kasir</a>
                    <a href="{{ route('cashier.reports.sales') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.reports.sales') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Penjualan</a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('profile.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Ubah Password</a>
                    <a href="{{ route('cashier.expenses.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.expenses.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengeluaran Harian</a>
                </div>
            </div>
        @endrole
    </div>
</div>
