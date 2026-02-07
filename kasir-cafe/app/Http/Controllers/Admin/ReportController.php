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
}