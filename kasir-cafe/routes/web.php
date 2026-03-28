<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboard;
use App\Http\Controllers\Cashier\PosController;

use App\Http\Controllers\Admin\ReceivingController;
use App\Http\Controllers\Admin\RecipeController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ExpiredController;
use App\Http\Controllers\Admin\StockOpnameController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\CashExpenseController;
use App\Http\Controllers\Admin\FinanceReportController;
use App\Http\Controllers\Admin\PettyCashFundController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeMealController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AttendanceLateRuleController;
use App\Http\Controllers\Admin\AttendanceScheduleController;
use App\Http\Controllers\Admin\AttendanceShiftController;
use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AssetIncidentController;
use App\Http\Controllers\Admin\AssetCategoryController;
use App\Http\Controllers\Admin\AssetLocationController;
use App\Http\Controllers\SaleRefundController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicDisplayController;
use App\Models\Product;

/*
|--------------------------------------------------------------------------
| Landing Page (sebelum login)
|--------------------------------------------------------------------------
| Menampilkan katalog menu (produk aktif) + nama resto, dsb.
*/
Route::get('/', function () {
    $products = Product::where('is_active', true)
        ->orderBy('name')
        ->take(12)
        ->get();

    return view('welcome', compact('products'));
})->name('landing');
Route::get('/tv-informasi', [PublicDisplayController::class, 'tvInformation'])->name('tv.information');
Route::get('/attendance-kiosk', [AttendanceController::class, 'publicKiosk'])->name('attendance.public_kiosk');
Route::post('/attendance-kiosk', [AttendanceController::class, 'kioskStore'])->name('attendance.public_kiosk.store');

/*
|--------------------------------------------------------------------------
| Area yang butuh login
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'license'])->group(function () {
    // Refund (cashier/admin/manager)
    Route::middleware('role:cashier|admin|manager')->group(function () {
        Route::get('/sales/{sale}/refund', [SaleRefundController::class, 'create'])->name('cashier.refunds.create');
        Route::post('/sales/{sale}/refund', [SaleRefundController::class, 'store'])->name('cashier.refunds.store');
        Route::get('/manager/sales/{sale}/refund', [SaleRefundController::class, 'create'])->name('manager.refunds.create');
        Route::post('/manager/sales/{sale}/refund', [SaleRefundController::class, 'store'])->name('manager.refunds.store');
        Route::get('/admin/sales/{sale}/refund', [SaleRefundController::class, 'create'])->name('admin.refunds.create');
        Route::post('/admin/sales/{sale}/refund', [SaleRefundController::class, 'store'])->name('admin.refunds.store');
        Route::get('/dashboard/attendance-kiosk', [AttendanceController::class, 'kiosk'])->name('attendance.kiosk');
        Route::post('/dashboard/attendance-kiosk', [AttendanceController::class, 'kioskStore'])->name('attendance.kiosk.store');
    });
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | ROUTE DASHBOARD UMUM (dipakai setelah login)
    |--------------------------------------------------------------------------
    | AuthenticatedSessionController@store memanggil route('dashboard')
    | Di sini kita arahkan sesuai role:
    |  - admin   -> admin.dashboard
    |  - manager -> manager.dashboard
    |  - cashier -> cashier.pos
    */
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin -> dashboard admin
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Manager -> dashboard manager
        if ($user->hasRole('manager')) {
            return redirect()->route('manager.dashboard');
        }

        // Kasir -> langsung ke POS
        if ($user->hasRole('cashier')) {
            return redirect()->route('cashier.pos');
        }

        // Fallback kalau ada role lain
        return redirect()->route('landing');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ADMIN AREA
    | URL prefix : /admin/...
    | Nama route : admin.***
    | Role       : admin
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')
        ->as('admin.')
        ->middleware('role:admin')
        ->group(function () {

            // Dashboard Admin (sekarang di /admin/dashboard)
            Route::get('/dashboard', [AdminDashboard::class, 'index'])
                ->name('dashboard');

            // MASTER DATA

            // Produk / Menu (katalog yang dipakai kasir)
            Route::resource('products', ProductController::class);

            // Resep / BOM
            Route::resource('recipes', RecipeController::class);

            // Stok bahan (item) & satuan
            Route::resource('items', \App\Http\Controllers\Admin\ItemController::class);
            Route::resource('units', \App\Http\Controllers\Admin\UnitController::class);

            // Pengguna
            Route::resource('users', UserController::class)->except(['show']);
            Route::resource('employees', EmployeeController::class)->except(['show']);
            Route::resource('attendance-shifts', AttendanceShiftController::class)->except(['show'])->names('attendance_shifts');
            Route::resource('attendance-late-rules', AttendanceLateRuleController::class)->except(['show'])->names('attendance_late_rules');
            Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendances.index');
            Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendances.store');
            Route::get('/attendance/review', [AttendanceController::class, 'reviewQueue'])->name('attendances.review');
            Route::post('/attendance/{attendance}/review', [AttendanceController::class, 'reviewUpdate'])->name('attendances.review_update');
            Route::get('/attendance-schedules', [AttendanceScheduleController::class, 'index'])->name('attendance_schedules.index');
            Route::post('/attendance-schedules', [AttendanceScheduleController::class, 'store'])->name('attendance_schedules.store');
            Route::post('/attendance-schedules/bulk', [AttendanceScheduleController::class, 'bulkStore'])->name('attendance_schedules.bulk_store');
            Route::post('/attendance-schedules/copy', [AttendanceScheduleController::class, 'copyRoster'])->name('attendance_schedules.copy');
            Route::delete('/attendance-schedules/{attendance_schedule}', [AttendanceScheduleController::class, 'destroy'])->name('attendance_schedules.destroy');
            Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
            Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave_requests.store');
            Route::post('/leave-requests/{leave_request}/approve', [LeaveRequestController::class, 'approve'])->name('leave_requests.approve');
            Route::post('/leave-requests/{leave_request}/reject', [LeaveRequestController::class, 'reject'])->name('leave_requests.reject');

            // Pengaturan resto
            Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

            // Inventaris
            Route::resource('assets', AssetController::class);
            Route::resource('asset-incidents', AssetIncidentController::class)->except(['show'])->names('asset_incidents');
            Route::resource('asset-categories', AssetCategoryController::class)->except(['show'])->names('asset_categories');
            Route::resource('asset-locations', AssetLocationController::class)->except(['show'])->names('asset_locations');

            // Ringkasan stok
            Route::get('/stocks', [StockController::class, 'index'])->name('stock.index');

            // Receiving stok (barang masuk)
            Route::get('/receivings', [ReceivingController::class, 'index'])->name('receivings.index');
            Route::get('/receivings/create', [ReceivingController::class, 'create'])->name('receivings.create');
            Route::post('/receivings', [ReceivingController::class, 'store'])->name('receivings.store');
            Route::get('/receivings/{id}', [ReceivingController::class, 'show'])->name('receivings.show');
            // Expired disposal
            Route::get('/expired', [ExpiredController::class, 'index'])->name('expired.index');
            Route::post('/expired/{batchId}/dispose', [ExpiredController::class, 'dispose'])->name('expired.dispose');

            // Stock opname
            Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('stock_opname.index');
            Route::get('/stock-opname/create', [StockOpnameController::class, 'create'])->name('stock_opname.create');
            Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('stock_opname.store');
            Route::get('/stock-opname/{id}', [StockOpnameController::class, 'show'])->name('stock_opname.show');
            Route::get('/stock-opname/{id}/edit', [StockOpnameController::class, 'edit'])->name('stock_opname.edit');
            Route::put('/stock-opname/{id}', [StockOpnameController::class, 'update'])->name('stock_opname.update');

            // POST / CANCEL opname
            Route::post('/stock-opname/{id}/post', [StockOpnameController::class, 'post'])->name('stock_opname.post');
            Route::post('/stock-opname/{id}/cancel', [StockOpnameController::class, 'cancel'])->name('stock_opname.cancel');

            // Export PDF
            Route::get('/stock-opname/{id}/pdf', [StockOpnameController::class, 'pdf'])->name('stock_opname.pdf');

            // Laporan
            Route::get('/reports/sales', [\App\Http\Controllers\Admin\ReportController::class, 'sales'])
                ->name('reports.sales');
            Route::get('/reports/sales/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportSales'])
                ->name('reports.sales.export');
            Route::get('/reports/top-products', [\App\Http\Controllers\Admin\ReportController::class, 'topProducts'])
                ->name('reports.top_products');
            Route::get('/sales/{sale}/receipt', [PosController::class, 'receipt'])->name('sales.receipt');
            Route::get('/reports/stock-opname-diff', [ReportController::class, 'stockOpnameDiff'])->name('reports.opname_variance');
            Route::get('/reports/audit-logs', [AuditLogController::class, 'index'])->name('reports.audit_logs');
            Route::delete('/reports/audit-logs/{auditLog}', [AuditLogController::class, 'destroy'])->name('reports.audit_logs.destroy');
            Route::delete('/reports/audit-logs', [AuditLogController::class, 'destroyFiltered'])->name('reports.audit_logs.destroy_filtered');
            Route::get('/reports/finance', [FinanceReportController::class, 'index'])->name('reports.finance');
            Route::get('/reports/finance/export', [FinanceReportController::class, 'export'])->name('reports.finance.export');
            Route::get('/reports/attendance', [AttendanceReportController::class, 'index'])->name('reports.attendance');
            Route::get('/reports/attendance/export', [AttendanceReportController::class, 'export'])->name('reports.attendance.export');
            Route::get('/expenses', [CashExpenseController::class, 'index'])->name('expenses.index');
            Route::post('/expenses', [CashExpenseController::class, 'store'])->name('expenses.store');
            Route::delete('/expenses/{cashExpense}', [CashExpenseController::class, 'destroy'])->name('expenses.destroy');
            Route::post('/expenses/{cashExpense}/approve', [CashExpenseController::class, 'approve'])->name('expenses.approve');
            Route::post('/expenses/{cashExpense}/reject', [CashExpenseController::class, 'reject'])->name('expenses.reject');
            Route::get('/petty-cash', [PettyCashFundController::class, 'index'])->name('petty_cash.index');
            Route::get('/petty-cash/export', [PettyCashFundController::class, 'export'])->name('petty_cash.export');
            Route::post('/petty-cash', [PettyCashFundController::class, 'store'])->name('petty_cash.store');
            Route::post('/petty-cash/{pettyCashFund}/close', [PettyCashFundController::class, 'close'])->name('petty_cash.close');
            Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
            Route::get('/payroll/attendance-export', [PayrollController::class, 'exportAttendanceRecap'])->name('payroll.attendance_export');
            Route::get('/payroll/meal-deduction-preview', [PayrollController::class, 'mealDeductionPreview'])->name('payroll.meal_deduction_preview');
            Route::post('/payroll', [PayrollController::class, 'store'])->name('payroll.store');
            Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
            Route::post('/payroll/{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('payroll.mark_paid');
            Route::get('/payroll/{payroll}/slip', [PayrollController::class, 'slip'])->name('payroll.slip');
            Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
            Route::get('/employee-meals', [EmployeeMealController::class, 'index'])->name('employee_meals.index');
            Route::post('/employee-meals', [EmployeeMealController::class, 'store'])->name('employee_meals.store');
        });

    /*
    |--------------------------------------------------------------------------
    | MANAGER AREA
    |--------------------------------------------------------------------------
    */
    Route::prefix('manager')
        ->as('manager.')
        ->middleware('role:manager')
        ->group(function () {
            Route::get('/dashboard', [ManagerDashboard::class, 'index'])->name('dashboard');

            // Produk / Menu
            Route::resource('products', ProductController::class);
            Route::resource('employees', EmployeeController::class)->except(['show']);
            Route::resource('attendance-shifts', AttendanceShiftController::class)->except(['show'])->names('attendance_shifts');
            Route::resource('attendance-late-rules', AttendanceLateRuleController::class)->except(['show'])->names('attendance_late_rules');
            Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendances.index');
            Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendances.store');
            Route::get('/attendance/review', [AttendanceController::class, 'reviewQueue'])->name('attendances.review');
            Route::post('/attendance/{attendance}/review', [AttendanceController::class, 'reviewUpdate'])->name('attendances.review_update');
            Route::get('/attendance-schedules', [AttendanceScheduleController::class, 'index'])->name('attendance_schedules.index');
            Route::post('/attendance-schedules', [AttendanceScheduleController::class, 'store'])->name('attendance_schedules.store');
            Route::post('/attendance-schedules/bulk', [AttendanceScheduleController::class, 'bulkStore'])->name('attendance_schedules.bulk_store');
            Route::post('/attendance-schedules/copy', [AttendanceScheduleController::class, 'copyRoster'])->name('attendance_schedules.copy');
            Route::delete('/attendance-schedules/{attendance_schedule}', [AttendanceScheduleController::class, 'destroy'])->name('attendance_schedules.destroy');
            Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
            Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave_requests.store');
            Route::post('/leave-requests/{leave_request}/approve', [LeaveRequestController::class, 'approve'])->name('leave_requests.approve');
            Route::post('/leave-requests/{leave_request}/reject', [LeaveRequestController::class, 'reject'])->name('leave_requests.reject');

            // Resep / BOM
            Route::resource('recipes', RecipeController::class);

            // Pengaturan Resto
            Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

            // Inventaris
            Route::resource('assets', AssetController::class);
            Route::resource('asset-incidents', AssetIncidentController::class)->except(['show'])->names('asset_incidents');
            Route::resource('asset-categories', AssetCategoryController::class)->except(['show'])->names('asset_categories');
            Route::resource('asset-locations', AssetLocationController::class)->except(['show'])->names('asset_locations');

            // Laporan Penjualan & Selisih Opname
            Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
            Route::get('/reports/sales/export', [ReportController::class, 'exportSales'])->name('reports.sales.export');
            Route::get('/sales/{sale}/receipt', [PosController::class, 'receipt'])->name('sales.receipt');
            Route::get('/reports/stock-opname-diff', [ReportController::class, 'stockOpnameDiff'])->name('reports.opname_variance');
            Route::get('/reports/audit-logs', [AuditLogController::class, 'index'])->name('reports.audit_logs');
            Route::get('/reports/finance', [FinanceReportController::class, 'index'])->name('reports.finance');
            Route::get('/reports/finance/export', [FinanceReportController::class, 'export'])->name('reports.finance.export');
            Route::get('/reports/attendance', [AttendanceReportController::class, 'index'])->name('reports.attendance');
            Route::get('/reports/attendance/export', [AttendanceReportController::class, 'export'])->name('reports.attendance.export');
            Route::get('/expenses', [CashExpenseController::class, 'index'])->name('expenses.index');
            Route::post('/expenses', [CashExpenseController::class, 'store'])->name('expenses.store');
            Route::delete('/expenses/{cashExpense}', [CashExpenseController::class, 'destroy'])->name('expenses.destroy');
            Route::post('/expenses/{cashExpense}/approve', [CashExpenseController::class, 'approve'])->name('expenses.approve');
            Route::post('/expenses/{cashExpense}/reject', [CashExpenseController::class, 'reject'])->name('expenses.reject');
            Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
            Route::get('/payroll/attendance-export', [PayrollController::class, 'exportAttendanceRecap'])->name('payroll.attendance_export');
            Route::get('/payroll/meal-deduction-preview', [PayrollController::class, 'mealDeductionPreview'])->name('payroll.meal_deduction_preview');
            Route::post('/payroll', [PayrollController::class, 'store'])->name('payroll.store');
            Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
            Route::post('/payroll/{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('payroll.mark_paid');
            Route::get('/payroll/{payroll}/slip', [PayrollController::class, 'slip'])->name('payroll.slip');
            Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
            Route::get('/employee-meals', [EmployeeMealController::class, 'index'])->name('employee_meals.index');
            Route::post('/employee-meals', [EmployeeMealController::class, 'store'])->name('employee_meals.store');
        });

    /*
    |--------------------------------------------------------------------------
    | CASHIER AREA (POS)
    | URL prefix : /cashier/...
    | Nama route : cashier.***
    | Role       : cashier atau admin
    |--------------------------------------------------------------------------
    */
    Route::prefix('cashier')
        ->as('cashier.')
        ->middleware('role:cashier|admin')
        ->group(function () {

            // Dashboard kasir sederhana -> redirect ke POS
            Route::get('/dashboard', function () {
                return redirect()->route('cashier.pos');
            })->name('dashboard');

            // POS Kasir
            Route::get('/pos', [PosController::class, 'index'])->name('pos');
            Route::post('/pos/new', [PosController::class, 'newSale'])->name('pos.new');
            Route::post('/pos/add', [PosController::class, 'addLine'])->name('pos.add');
            Route::post('/pos/line/{line}/update', [PosController::class, 'updateLine'])->name('pos.line.update');
            Route::post('/pos/line/{line}/delete', [PosController::class, 'deleteLine'])->name('pos.line.delete');
            Route::post('/pos/clear', [PosController::class, 'clearCart'])->name('pos.clear');
            Route::post('/pos/cancel', [PosController::class, 'cancel'])->name('pos.cancel');
            Route::post('/pos/hold', [PosController::class, 'hold'])->name('pos.hold');
            Route::post('/pos/pay', [PosController::class, 'pay'])->name('pos.pay');
            Route::get('/pos/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');
            // >>> Laporan penjualan kasir (transaksi milik kasir login) <<<
            Route::get('/reports/sales', [\App\Http\Controllers\Admin\ReportController::class, 'salesForCashier'])
                ->name('reports.sales');
            Route::get('/reports/sales/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportSalesForCashier'])
                ->name('reports.sales.export');
            Route::get('/reports/finance', [FinanceReportController::class, 'index'])->name('reports.finance');
            Route::get('/reports/finance/export', [FinanceReportController::class, 'export'])->name('reports.finance.export');
            Route::get('/expenses', [CashExpenseController::class, 'index'])->name('expenses.index');
            Route::post('/expenses', [CashExpenseController::class, 'store'])->name('expenses.store');
            Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
            Route::get('/employee-meals', [EmployeeMealController::class, 'index'])->name('employee_meals.index');
            Route::post('/employee-meals', [EmployeeMealController::class, 'store'])->name('employee_meals.store');
        });
});

// Fallback anti 405: jika ada GET /logout (mis. klik link lama), tetap logout aman lalu redirect login.
Route::get('/logout', function (Request $request) {
    if (Auth::check()) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect()->route('login');
})->name('logout.get');

require __DIR__ . '/auth.php';
