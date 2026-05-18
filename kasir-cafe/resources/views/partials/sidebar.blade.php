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
                    <span>Utama</span>
                    <span class="text-sm" x-text="open.adminMain ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminMain" class="space-y-1">
                    @can('access.admin_dashboard')
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">
                            Admin Dashboard
                        </a>
                    @endcan
                </div>
            </div>

            <div class="space-y-2">
                <button type="button" @click="open.adminMaster = !open.adminMaster" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Master Data</span>
                    <span class="text-sm" x-text="open.adminMaster ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminMaster" class="space-y-1">
                    @can('access.users')<a href="{{ route('admin.users.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.users.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengguna &amp; Hak Akses</a>@endcan
                    @can('access.employees')<a href="{{ route('admin.employees.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.employees.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Karyawan</a>@endcan
                    @can('access.attendance_shifts')<a href="{{ route('admin.attendance_shifts.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.attendance_shifts.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Shift</a>@endcan
                    @can('access.attendance_late_rules')<a href="{{ route('admin.attendance_late_rules.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.attendance_late_rules.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Rule Keterlambatan</a>@endcan
                    @can('access.products')<a href="{{ route('admin.products.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.products.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Produk / Menu</a>@endcan
                    @can('access.recipes')<a href="{{ route('admin.recipes.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.recipes.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Resep / BOM</a>@endcan
                    @can('access.items')<a href="{{ route('admin.items.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.items.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Stok Bahan</a>@endcan
                    @can('access.units')<a href="{{ route('admin.units.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.units.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Satuan</a>@endcan
                    @can('access.expense_categories')<a href="{{ route('admin.expense_categories.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.expense_categories.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kategori Pengeluaran</a>@endcan
                    @can('access.settings')<a href="{{ route('admin.settings.edit') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.settings.edit') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengaturan Resto</a>@endcan
                    @can('access.assets')<a href="{{ route('admin.assets.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.assets.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Inventaris</a>@endcan
                    @can('access.asset_categories')<a href="{{ route('admin.asset_categories.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.asset_categories.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kategori Inventaris</a>@endcan
                    @can('access.asset_locations')<a href="{{ route('admin.asset_locations.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.asset_locations.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Lokasi Inventaris</a>@endcan
                    @can('access.asset_incidents')<a href="{{ route('admin.asset_incidents.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.asset_incidents.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kerusakan / Pemusnahan</a>@endcan
                </div>
            </div>

            <div class="space-y-2">
                <button type="button" @click="open.adminOps = !open.adminOps" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Operasional</span>
                    <span class="text-sm" x-text="open.adminOps ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminOps" class="space-y-1">
                    @can('access.pos')<a href="{{ route('cashier.pos') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.pos') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">POS Kasir</a>@endcan
                    @can('access.attendance_kiosk')<a href="{{ route('attendance.kiosk') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('attendance.kiosk') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kiosk Absensi</a>@endcan
                    @can('access.attendances')<a href="{{ route('admin.attendances.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.attendances.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Absensi Karyawan</a>@endcan
                    @can('access.attendance_review')<a href="{{ route('admin.attendances.review') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.attendances.review') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Review Wajah Absensi</a>@endcan
                    @can('access.attendance_schedules')<a href="{{ route('admin.attendance_schedules.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.attendance_schedules.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Roster Bulanan Shift</a>@endcan
                    @can('access.leave_requests')<a href="{{ route('admin.leave_requests.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.leave_requests.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Izin / Cuti / Sakit</a>@endcan
                    @can('access.employee_meals')<a href="{{ route('admin.employee_meals.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.employee_meals.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Makan Karyawan</a>@endcan
                    @can('access.expenses')<a href="{{ route('admin.expenses.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.expenses.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengeluaran Operasional</a>@endcan
                    @can('access.petty_cash')<a href="{{ route('admin.petty_cash.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.petty_cash.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kas Kecil</a>@endcan
                    @can('access.payroll')<a href="{{ route('admin.payroll.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.payroll.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Penggajian Petugas</a>@endcan
                    @can('access.receivings')<a href="{{ route('admin.receivings.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.receivings.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Receiving Stok</a>@endcan
                    @can('access.stock')<a href="{{ route('admin.stock.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.stock.index') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Stok Saat Ini</a>@endcan
                    @can('access.stock_opname')<a href="{{ route('admin.stock_opname.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.stock_opname.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Stock Opname</a>@endcan
                    @can('access.expired')<a href="{{ route('admin.expired.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.expired.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Expired Disposal</a>@endcan
                </div>
            </div>

            <div class="space-y-2">
                <button type="button" @click="open.adminReports = !open.adminReports" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Laporan</span>
                    <span class="text-sm" x-text="open.adminReports ? '−' : '+'"></span>
                </button>
                <div x-show="open.adminReports" class="space-y-1">
                    @can('access.sales_reports')<a href="{{ route('admin.reports.sales') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.sales') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Penjualan</a>@endcan
                    @can('access.top_products_report')<a href="{{ route('admin.reports.top_products') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.top_products') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Top 10 Penjualan</a>@endcan
                    @can('access.all_products_report')<a href="{{ route('admin.reports.all_products') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.all_products') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Semua Menu Terjual</a>@endcan
                    @can('access.customers')<a href="{{ route('admin.customers.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.customers.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Data Pelanggan</a>@endcan
                    @can('access.finance_report')<a href="{{ route('admin.reports.finance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.finance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Keuangan</a>@endcan
                    @can('access.attendance_report')<a href="{{ route('admin.reports.attendance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.attendance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Rekap Absensi Bulanan</a>@endcan
                    @can('access.opname_variance_report')<a href="{{ route('admin.reports.opname_variance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.opname_variance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Selisih Opname</a>@endcan
                    @can('access.audit_logs')<a href="{{ route('admin.reports.audit_logs') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('admin.reports.audit_logs') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Audit Log</a>@endcan
                </div>
            </div>
        @endrole

        @role('manager')
            <div class="space-y-2">
                <button type="button" @click="open.managerMain = !open.managerMain" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Operasional</span>
                    <span class="text-sm" x-text="open.managerMain ? '−' : '+'"></span>
                </button>
                <div x-show="open.managerMain" class="space-y-1">
                    @can('access.manager_dashboard')<a href="{{ route('manager.dashboard') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.dashboard') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Dashboard</a>@endcan
                    @can('access.employees')<a href="{{ route('manager.employees.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.employees.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Karyawan</a>@endcan
                    @can('access.attendance_shifts')<a href="{{ route('manager.attendance_shifts.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.attendance_shifts.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Master Shift</a>@endcan
                    @can('access.attendance_late_rules')<a href="{{ route('manager.attendance_late_rules.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.attendance_late_rules.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Rule Keterlambatan</a>@endcan
                    @can('access.attendances')<a href="{{ route('manager.attendances.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.attendances.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Absensi Karyawan</a>@endcan
                    @can('access.attendance_review')<a href="{{ route('manager.attendances.review') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.attendances.review') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Review Wajah Absensi</a>@endcan
                    @can('access.attendance_kiosk')<a href="{{ route('attendance.kiosk') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('attendance.kiosk') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kiosk Absensi</a>@endcan
                    @can('access.attendance_schedules')<a href="{{ route('manager.attendance_schedules.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.attendance_schedules.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Roster Bulanan Shift</a>@endcan
                    @can('access.leave_requests')<a href="{{ route('manager.leave_requests.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.leave_requests.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Izin / Cuti / Sakit</a>@endcan
                    @can('access.employee_meals')<a href="{{ route('manager.employee_meals.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.employee_meals.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Makan Karyawan</a>@endcan
                    @can('access.expenses')<a href="{{ route('manager.expenses.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.expenses.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengeluaran Operasional</a>@endcan
                    @can('access.payroll')<a href="{{ route('manager.payroll.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.payroll.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Penggajian Petugas</a>@endcan
                    @can('access.products')<a href="{{ route('manager.products.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.products.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Produk / Menu</a>@endcan
                    @can('access.recipes')<a href="{{ route('manager.recipes.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.recipes.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Resep / BOM</a>@endcan
                    @can('access.assets')<a href="{{ route('manager.assets.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.assets.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Inventaris</a>@endcan
                    @can('access.asset_categories')<a href="{{ route('manager.asset_categories.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.asset_categories.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kategori Inventaris</a>@endcan
                    @can('access.asset_locations')<a href="{{ route('manager.asset_locations.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.asset_locations.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Lokasi Inventaris</a>@endcan
                    @can('access.asset_incidents')<a href="{{ route('manager.asset_incidents.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.asset_incidents.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kerusakan / Pemusnahan</a>@endcan
                    @can('access.settings')<a href="{{ route('manager.settings.edit') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.settings.edit') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengaturan Resto</a>@endcan
                </div>

                <button type="button" @click="open.managerReports = !open.managerReports" class="mt-4 flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Laporan</span>
                    <span class="text-sm" x-text="open.managerReports ? '−' : '+'"></span>
                </button>
                <div x-show="open.managerReports" class="space-y-1">
                    @can('access.sales_reports')<a href="{{ route('manager.reports.sales') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.reports.sales') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Penjualan</a>@endcan
                    @can('access.finance_report')<a href="{{ route('manager.reports.finance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.reports.finance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Keuangan</a>@endcan
                    @can('access.attendance_report')<a href="{{ route('manager.reports.attendance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.reports.attendance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Rekap Absensi Bulanan</a>@endcan
                    @can('access.opname_variance_report')<a href="{{ route('manager.reports.opname_variance') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.reports.opname_variance') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Selisih Opname</a>@endcan
                    @can('access.audit_logs')<a href="{{ route('manager.reports.audit_logs') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('manager.reports.audit_logs') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Audit Log</a>@endcan
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
                    @can('access.pos')<a href="{{ route('cashier.pos') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.pos') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">POS Kasir</a>@endcan
                    @can('access.attendance_kiosk')<a href="{{ route('attendance.kiosk') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('attendance.kiosk') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kiosk Absensi</a>@endcan
                    @can('access.employee_meals')<a href="{{ route('cashier.employee_meals.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.employee_meals.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Makan Karyawan</a>@endcan
                    @can('access.sales_reports')<a href="{{ route('cashier.reports.sales') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.reports.sales') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Laporan Penjualan</a>@endcan
                    @can('access.expenses')<a href="{{ route('cashier.expenses.index') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.expenses.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengeluaran Operasional</a>@endcan
                    @can('access.settings')<a href="{{ route('cashier.settings.edit') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('cashier.settings.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Pengaturan Resto</a>@endcan
                    @can('access.profile')<a href="{{ route('profile.edit') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('profile.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Ubah Password</a>@endcan
                </div>
            </div>
        @endrole

        @role('petugas')
            <div class="space-y-2">
                <button type="button" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-blue-100/75 transition hover:bg-white/5">
                    <span>Petugas</span>
                </button>
                <div class="space-y-1">
                    @can('access.attendance_kiosk')<a href="{{ route('attendance.kiosk') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('attendance.kiosk') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Kiosk Absensi</a>@endcan
                    @can('access.profile')<a href="{{ route('profile.edit') }}" class="flex items-center rounded-xl px-3 py-2.5 transition {{ request()->routeIs('profile.*') ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-blue-50 hover:bg-white/10' }}">Ubah Password</a>@endcan
                </div>
            </div>
        @endrole
    </div>
</div>
