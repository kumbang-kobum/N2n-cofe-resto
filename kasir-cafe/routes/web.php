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

Route::get('/', fn () => view('welcome'));

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $u = auth()->user();
        if ($u->hasRole('admin')) return redirect()->route('admin.dashboard');
        if ($u->hasRole('manager')) return redirect()->route('manager.dashboard');
        return redirect()->route('cashier.pos');
    })->name('dashboard');

    // ======================
    // ADMIN
    // ======================
    Route::prefix('admin')->middleware('role:admin')->group(function () {

        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('admin.dashboard');

        // Receiving
        Route::get('/receivings', [ReceivingController::class, 'index'])->name('admin.receivings.index');
        Route::get('/receivings/create', [ReceivingController::class, 'create'])->name('admin.receivings.create');
        Route::post('/receivings', [ReceivingController::class, 'store'])->name('admin.receivings.store');

        // Recipes / BOM
        Route::get('/recipes', [RecipeController::class, 'index'])->name('admin.recipes.index');
        Route::get('/recipes/{productId}/edit', [RecipeController::class, 'edit'])->name('admin.recipes.edit');
        Route::post('/recipes/{productId}', [RecipeController::class, 'update'])->name('admin.recipes.update');

        // Stock
        Route::get('/stock', [StockController::class, 'index'])->name('admin.stock.index');

        // Reports
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('admin.reports.sales');

        // Expired Disposal
        Route::get('/expired', [ExpiredController::class, 'index'])->name('admin.expired.index');
        Route::post('/expired/{batchId}/dispose', [ExpiredController::class, 'dispose'])->name('admin.expired.dispose');

       // Stock Opname
        Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('admin.stock_opname.index');
        Route::get('/stock-opname/create', [StockOpnameController::class, 'create'])->name('admin.stock_opname.create');
        Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('admin.stock_opname.store');

        Route::get('/stock-opname/{id}', [StockOpnameController::class, 'show'])->name('admin.stock_opname.show');
        Route::get('/stock-opname/{id}/edit', [StockOpnameController::class, 'edit'])->name('admin.stock_opname.edit');
        Route::post('/stock-opname/{id}/update', [StockOpnameController::class, 'update'])->name('admin.stock_opname.update');

        Route::post('/stock-opname/{id}/post', [StockOpnameController::class, 'post'])->name('admin.stock_opname.post');

        // ✅ CANCEL
        Route::post('/stock-opname/{id}/cancel', [StockOpnameController::class, 'cancel'])->name('admin.stock_opname.cancel');

        // ✅ PDF
        Route::get('/stock-opname/{id}/pdf', [StockOpnameController::class, 'pdf'])->name('admin.stock_opname.pdf');

        // ✅ Report variance opname
        Route::get('/reports/opname-variance', [ReportController::class,'opnameVariance'])->name('admin.reports.opname_variance');
    });

    // ======================
    // MANAGER (read-only)
    // ======================
    Route::prefix('manager')->middleware('role:manager|admin')->group(function () {
        Route::get('/dashboard', [ManagerDashboard::class, 'index'])->name('manager.dashboard');
    });

    // ======================
    // CASHIER
    // ======================
    Route::prefix('cashier')->middleware('role:cashier|admin')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('cashier.pos');
        Route::post('/pos/new', [PosController::class, 'newSale'])->name('cashier.pos.new');
        Route::post('/pos/add', [PosController::class, 'addLine'])->name('cashier.pos.add');
        Route::post('/pos/pay', [PosController::class, 'pay'])->name('cashier.pos.pay');
    });
});

require __DIR__ . '/auth.php';