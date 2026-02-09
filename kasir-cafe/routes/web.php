<?php

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
use App\Http\Controllers\ProfileController;
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

/*
|--------------------------------------------------------------------------
| Area yang butuh login
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'license'])->group(function () {
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

            // Pengaturan resto
            Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

            // Ringkasan stok
            Route::get('/stocks', [StockController::class, 'index'])->name('stock.index');

            // Receiving stok (barang masuk)
            Route::get('/receivings', [ReceivingController::class, 'index'])->name('receivings.index');
            Route::get('/receivings/create', [ReceivingController::class, 'create'])->name('receivings.create');
            Route::post('/receivings', [ReceivingController::class, 'store'])->name('receivings.store');
            Route::get('/receivings/{id}', [ReceivingController::class, 'show'])->name('receivings.show');
            // 👉 alias untuk link "Dispose" di index.blade.php
            Route::get('/expired/{id}/dispose', [ExpiredController::class, 'create'])->name('expired.dispose'); 

            // Expired disposal
            Route::get('/expired', [ExpiredController::class, 'index'])->name('expired.index');
            Route::get('/expired/create', [ExpiredController::class, 'create'])->name('expired.create');
            Route::post('/expired', [ExpiredController::class, 'store'])->name('expired.store');
            Route::get('/expired/{id}', [ExpiredController::class, 'show'])->name('expired.show');

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
            Route::get('/reports/stock-opname-diff', [ReportController::class, 'stockOpnameDiff'])->name('reports.opname_variance');
            Route::get('/reports/audit-logs', [AuditLogController::class, 'index'])->name('reports.audit_logs');
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

            // Resep / BOM
            Route::resource('recipes', RecipeController::class);

            // Pengaturan Resto
            Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

            // Laporan Penjualan & Selisih Opname
            Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
            Route::get('/reports/stock-opname-diff', [ReportController::class, 'stockOpnameDiff'])->name('reports.opname_variance');
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
            Route::post('/pos/pay', [PosController::class, 'pay'])->name('pos.pay');
            Route::get('/pos/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');
            // >>> Laporan penjualan kasir (transaksi milik kasir login) <<<
            Route::get('/reports/sales', [\App\Http\Controllers\Admin\ReportController::class, 'salesForCashier'])
                ->name('reports.sales');
        });
});

require __DIR__ . '/auth.php';
