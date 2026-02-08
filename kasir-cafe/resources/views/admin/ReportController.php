<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Laporan penjualan umum (dipakai admin & manager).
     * Bisa di-filter per kasir kalau $request->cashier_id diisi.
     */
    public function sales(Request $request)
    {
        $group = $request->get('group', 'daily'); // daily | monthly

        // Default range: awal bulan s/d hari ini
        if (!$request->filled('from') || !$request->filled('to')) {
            $to = Carbon::today();
            $from = $to->copy()->startOfMonth();
        } else {
            $from = Carbon::parse($request->get('from'));
            $to   = Carbon::parse($request->get('to'));
        }

        // optional, kalau di-filter per kasir
        $cashierId = $request->get('cashier_id');

        $salesQuery = Sale::where('status', 'PAID')
            ->whereDate('paid_at', '>=', $from->toDateString())
            ->whereDate('paid_at', '<=', $to->toDateString());

        if ($cashierId) {
            $salesQuery->where('cashier_id', $cashierId);
        }

        $sales = $salesQuery->orderBy('paid_at')->get();

        $rows = [];
        $overall = [
            'transactions' => 0,
            'total'        => 0,
            'cash'         => 0,
            'qris'         => 0,
            'debit'        => 0,
        ];

        foreach ($sales as $sale) {
            // Key per hari / per bulan
            $key = $group === 'monthly'
                ? $sale->paid_at->format('Y-m')
                : $sale->paid_at->format('Y-m-d');

            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'label'        => $group === 'monthly'
                        ? $sale->paid_at->translatedFormat('F Y')
                        : $sale->paid_at->format('d/m/Y'),
                    'transactions' => 0,
                    'total'        => 0,
                    'cash'         => 0,
                    'qris'         => 0,
                    'debit'        => 0,
                ];
            }

            $rows[$key]['transactions']++;
            $rows[$key]['total'] += $sale->total;

            $method = strtoupper($sale->payment_method ?? '');

            if ($method === 'CASH') {
                $rows[$key]['cash'] += $sale->total;
                $overall['cash']    += $sale->total;
            } elseif ($method === 'QRIS') {
                $rows[$key]['qris'] += $sale->total;
                $overall['qris']    += $sale->total;
            } elseif (in_array($method, ['DEBIT', 'CARD'])) {
                $rows[$key]['debit'] += $sale->total;
                $overall['debit']    += $sale->total;
            }

            $overall['transactions']++;
            $overall['total'] += $sale->total;
        }

        $rows = collect($rows)->sortKeys();

        return view('admin.reports.sales', [
            'group'      => $group,
            'from'       => $from,
            'to'         => $to,
            'rows'       => $rows,
            'overall'    => $overall,
            'cashier_id' => $cashierId,
        ]);
    }

    /**
     * Laporan penjualan khusus kasir:
     * hanya transaksi milik kasir yang sedang login.
     * Dipanggil dari route cashier.reports.sales.
     */
    public function salesForCashier(Request $request)
    {
        // Inject cashier_id = user login
        $request->merge([
            'cashier_id' => auth()->id(),
        ]);

        // Re-use logika dari method sales()
        return $this->sales($request);
    }

    /**
     * Laporan selisih stock opname (kalau sudah ada view-nya).
     * Sesuaikan isi method ini dengan implementasi kamu sebelumnya,
     * kalau berbeda.
     */
    public function stockOpnameDiff(Request $request)
    {
        return view('admin.reports.stock_opname_diff');
    }
}