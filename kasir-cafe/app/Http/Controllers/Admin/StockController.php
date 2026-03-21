<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemBatch;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $batchStats = ItemBatch::query()
            ->selectRaw('item_id, SUM(qty_on_hand_base) as stock_base, SUM(qty_on_hand_base * unit_cost_base) as stock_value')
            ->where('status', 'ACTIVE')
            ->groupBy('item_id')
            ->get()
            ->keyBy('item_id');

        $items = Item::with('baseUnit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($item) use ($batchStats) {
                $stats = $batchStats->get($item->id);
                $stockBase = (float) ($stats->stock_base ?? 0);
                $stockValue = (float) ($stats->stock_value ?? 0);

                $item->stock_base = $stockBase;
                $item->stock_value = $stockValue;
                $item->avg_unit_cost_base = $stockBase > 0 ? ($stockValue / $stockBase) : 0;
                return $item;
            });

        $batchesExpSoon = ItemBatch::with('item')
            ->where('status', 'ACTIVE')
            ->where('qty_on_hand_base', '>', 0)
            ->whereDate('expired_at', '<=', now()->addDays(7)->toDateString())
            ->orderBy('expired_at')
            ->limit(50)
            ->get();

        return view('admin.stock.index', compact('items', 'batchesExpSoon'));
    }
}
