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
        $items = Item::with('baseUnit')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                $stock = ItemBatch::where('item_id', $item->id)
                    ->whereIn('status', ['ACTIVE'])
                    ->sum('qty_on_hand_base');

                $item->stock_base = (float)$stock;
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