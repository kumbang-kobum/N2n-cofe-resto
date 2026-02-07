<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->toDateString());

        $sales = Sale::query()
            ->where('status', 'PAID')
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to)
            ->orderByDesc('paid_at')
            ->get();

        $summary = [
            'omzet' => (float) $sales->sum('total'),
            'cogs' => (float) $sales->sum('cogs_total'),
            'profit' => (float) $sales->sum('profit_gross'),
        ];

        return view('admin.reports.sales', compact('sales', 'from', 'to', 'summary'));
    }

    public function opnameVariance(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $rows = \App\Models\StockOpnameLine::query()
            ->select([
                'stock_opname_lines.*',
                'stock_opnames.code as opname_code',
                'stock_opnames.counted_at',
                'stock_opnames.status as opname_status',
                'items.name as item_name',
            ])
            ->join('stock_opnames','stock_opnames.id','=','stock_opname_lines.stock_opname_id')
            ->join('items','items.id','=','stock_opname_lines.item_id')
            ->whereBetween('stock_opnames.counted_at', [$from, $to])
            ->where('stock_opnames.status','!=','CANCELLED')
            ->whereRaw('ABS(stock_opname_lines.diff_qty_base) > 0.000001')
            ->orderByDesc('stock_opnames.counted_at')
            ->orderBy('items.name')
            ->paginate(50)
            ->withQueryString();

        return view('admin.reports.opname_variance', compact('rows','from','to'));
    }
}