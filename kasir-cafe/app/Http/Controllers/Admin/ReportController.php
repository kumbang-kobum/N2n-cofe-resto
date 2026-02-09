<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\StockOpnameLine;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Build query & summary untuk laporan penjualan.
     *
     * @param  Request   $request
     * @param  int|null  $cashierId  kalau null → semua kasir, kalau ada → filter kasir tertentu
     * @return array [ $sales, $summary, $from, $to ]
     */
    protected function buildSalesData(Request $request, ?int $cashierId = null): array
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->toDateString());

        $query = Sale::query()
            ->with('cashier')
            ->where('status', 'PAID')
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to);

        if ($cashierId) {
            $query->where('cashier_id', $cashierId);
        }

        $sales = $query->orderByDesc('paid_at')->get();

        $summary = [
            'subtotal' => (float) $sales->sum('total'),
            'tax'      => (float) $sales->sum('tax_amount'),
            'omzet'    => (float) $sales->sum('grand_total'),
            'cogs'     => (float) $sales->sum('cogs_total'),
            'profit'   => (float) $sales->sum('profit_gross'),
            'per_payment' => [],
        ];

        foreach ($sales as $s) {
            $method = strtoupper($s->payment_method ?? 'UNKNOWN');

            if (! isset($summary['per_payment'][$method])) {
                $summary['per_payment'][$method] = 0;
            }

            $summary['per_payment'][$method] += (float) ($s->grand_total ?: ($s->total + ($s->tax_amount ?? 0)));
        }

        return [$sales, $summary, $from, $to];
    }

    /**
     * Laporan penjualan (ADMIN) – semua kasir.
     */
    public function sales(Request $request)
    {
        $cashierId = $request->query('cashier_id');
        $cashierId = $cashierId !== null && $cashierId !== '' ? (int) $cashierId : null;

        [$sales, $summary, $from, $to] = $this->buildSalesData($request, $cashierId);

        $cashiers = User::role('cashier')->orderBy('name')->get();
        $selectedCashier = $cashierId;

        return view('admin.reports.sales', compact('sales', 'summary', 'from', 'to', 'cashiers', 'selectedCashier'));
    }

    /**
     * Laporan penjualan untuk KASIR (hanya transaksi kasir yang login).
     */
    public function salesForCashier(Request $request)
    {
        [$sales, $summary, $from, $to] = $this->buildSalesData($request, auth()->id());

        return view('admin.reports.sales', compact('sales', 'summary', 'from', 'to'));
    }

    /**
     * Laporan selisih stock opname (nama lama).
     */
    public function opnameVariance(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $rows = StockOpnameLine::query()
            ->select([
                'stock_opname_lines.*',
                'stock_opnames.code as opname_code',
                'stock_opnames.counted_at',
                'stock_opnames.status as opname_status',
                'items.name as item_name',
            ])
            ->join('stock_opnames', 'stock_opnames.id', '=', 'stock_opname_lines.stock_opname_id')
            ->join('items', 'items.id', '=', 'stock_opname_lines.item_id')
            ->whereBetween('stock_opnames.counted_at', [$from, $to])
            ->where('stock_opnames.status', '!=', 'CANCELLED')
            ->whereRaw('ABS(stock_opname_lines.diff_qty_base) > 0.000001')
            ->orderByDesc('stock_opnames.counted_at')
            ->orderBy('items.name')
            ->paginate(50)
            ->withQueryString();

        return view('admin.reports.opname_variance', compact('rows', 'from', 'to'));
    }

    /**
     * Alias untuk nama method lama (kalau route masih pakai stockOpnameDiff).
     */
    public function stockOpnameDiff(Request $request)
    {
        $from   = $request->input('from', now()->startOfMonth()->toDateString());
        $to     = $request->input('to',   now()->toDateString());
        $status = $request->input('status', ''); // <-- default kosong / ALL

        $query = StockOpnameLine::query()
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
            ->whereRaw('ABS(stock_opname_lines.diff_qty_base) > 0.000001');

        // kalau di form ada filter status, kita terapkan
        if ($status === 'POSTED') {
            $query->where('stock_opnames.status', 'POSTED');
        } elseif ($status === 'DRAFT') {
            $query->where('stock_opnames.status', 'DRAFT');
        }
        // kalau kosong: pakai semua status kecuali CANCELLED (sudah difilter di atas)

        $rows = $query
            ->orderByDesc('stock_opnames.counted_at')
            ->orderBy('items.name')
            ->paginate(50)
            ->withQueryString();

        $filters = [
            'from'   => $from,
            'to'     => $to,
            'status' => $status,  // <-- penting, biar $filters['status'] selalu ada
        ];

        $collection = $rows->getCollection();

        $summary = [
            'total_rows'  => $rows->total(),
            'total_plus'  => (float) $collection
                ->where('diff_qty_base', '>', 0)
                ->sum('diff_qty_base'),
            'total_minus' => (float) $collection
                ->where('diff_qty_base', '<', 0)
                ->sum('diff_qty_base'),
        ];

        return view('admin.reports.opname_variance', compact('rows', 'filters', 'summary'));
    }
}
