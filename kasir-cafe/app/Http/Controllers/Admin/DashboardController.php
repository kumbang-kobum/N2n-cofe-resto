<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\Sale;
use App\Models\SaleLine;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $from = now()->startOfMonth()->toDateString();
        $to   = now()->toDateString();

        $sales = Sale::query()
            ->where('status', 'PAID')
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to)
            ->get();

        $summary = [
            'omzet'  => (float) $sales->sum('total'),
            'cogs'   => (float) $sales->sum('cogs_total'),
            'profit' => (float) $sales->sum('profit_gross'),
            'trx'    => (int) $sales->count(),
        ];

        $pendingExpenseAlerts = [
            'pending_total' => (int) CashExpense::query()
                ->where('status', 'PENDING')
                ->count(),
            'pending_amount_total' => (float) CashExpense::query()
                ->where('status', 'PENDING')
                ->sum('amount'),
            'over_limit_count' => (int) CashExpense::query()
                ->where('status', 'PENDING')
                ->where('exceeds_approval_limit', true)
                ->count(),
            'over_limit_amount_total' => (float) CashExpense::query()
                ->where('status', 'PENDING')
                ->where('exceeds_approval_limit', true)
                ->sum('amount'),
        ];

        $overLimitExpenseItems = CashExpense::query()
            ->with(['cashier', 'expenseCategory'])
            ->where('status', 'PENDING')
            ->where('exceeds_approval_limit', true)
            ->orderByDesc('expense_at')
            ->take(5)
            ->get();

        $topProducts = SaleLine::query()
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('SUM(sale_lines.qty) as qty_total'),
                DB::raw('SUM(sale_lines.qty * sale_lines.price) as omzet_total'),
                DB::raw('COUNT(DISTINCT sale_lines.sale_id) as trx_total'),
            ])
            ->join('sales', 'sales.id', '=', 'sale_lines.sale_id')
            ->join('products', 'products.id', '=', 'sale_lines.product_id')
            ->where('sales.status', 'PAID')
            ->whereDate('sales.paid_at', '>=', $from)
            ->whereDate('sales.paid_at', '<=', $to)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty_total')
            ->limit(10)
            ->get();

        $topProductChart = [
            'labels' => $topProducts->pluck('product_name')->values(),
            'qty' => $topProducts->pluck('qty_total')->map(fn ($v) => (float) $v)->values(),
        ];

        // stok menipis (MVP): min_stock dibanding total stok base
        $lowStock = Item::with('baseUnit')
            ->where('is_active', true)
            ->get()
            ->map(function ($item) {
                $stock = (float) ItemBatch::where('item_id', $item->id)
                    ->where('status', 'ACTIVE')
                    ->sum('qty_on_hand_base');

                $item->stock_base = $stock;
                return $item;
            })
            ->filter(fn($item) => $item->min_stock !== null && $item->stock_base <= (float)$item->min_stock)
            ->sortBy('stock_base')
            ->take(10)
            ->values();

        // batch expiring soon (7 hari)
        $expSoon = ItemBatch::with('item')
            ->where('status', 'ACTIVE')
            ->where('qty_on_hand_base', '>', 0)
            ->whereDate('expired_at', '<=', now()->addDays(7)->toDateString())
            ->orderBy('expired_at')
            ->take(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'summary',
            'pendingExpenseAlerts',
            'overLimitExpenseItems',
            'from',
            'to',
            'lowStock',
            'expSoon',
            'topProducts',
            'topProductChart',
        ));
    }
}
