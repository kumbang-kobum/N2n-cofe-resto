<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\Sale;
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

        return view('admin.dashboard.index', compact('summary', 'from', 'to', 'lowStock', 'expSoon'));
    }
}