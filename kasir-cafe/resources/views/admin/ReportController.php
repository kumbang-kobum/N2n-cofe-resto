<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Laporan penjualan:
     * - filter tanggal
     * - grup harian / bulanan
     * - total per metode pembayaran (CASH / QRIS / DEBIT)
     */
    public function sales(Request $request)
    {
        $group = $request->get('group', 'daily'); // daily | monthly

        // Default: 1 bulan terakhir (awal bulan s/d hari ini)
        if (!$request->filled('from') || !$request->filled('to')) {
            $to = Carbon::today();
            $from = $to->copy()->startOfMonth();
        } else {
            $from = Carbon::parse($request->get('from'));
            $to   = Carbon::parse($request->get('to'));
        }

        // Ambil semua SALE yang sudah dibayar dalam range tanggal
        $sales = Sale::where('status', 'PAID')
            ->whereDate('paid_at', '>=', $from->toDateString())
            ->whereDate('paid_at', '<=', $to->toDateString())
            ->orderBy('paid_at')
            ->get();

        // Struktur ringkasan per periode
        $rows = [];
        $overall = [
            'transactions' => 0,
            'total'        => 0,
            'cash'         => 0,
            'qris'         => 0,
            'debit'        => 0,
        ];

        foreach ($sales as $sale) {
            // Key grouping: harian atau bulanan
            $key = $group === 'monthly'
                ? $sale->paid_at->format('Y-m')
                : $sale->paid_at->format('Y-m-d');

            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'label'        => $group === 'monthly'
                        ? $sale->paid_at->translatedFormat('F Y') // contoh: Februari 2026
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
                $overall['cash']     += $sale->total;
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

        // Urutkan berdasarkan key tanggal
        $rows = collect($rows)->sortKeys();

        return view('admin.reports.sales', [
            'group'   => $group,
            'from'    => $from,
            'to'      => $to,
            'rows'    => $rows,
            'overall' => $overall,
        ]);
    }

    /**
     * Placeholder untuk laporan selisih opname.
     * Kalau kamu sudah punya implementasi lama, taruh di sini lagi.
     */
    public function stockOpnameDiff(Request $request)
    {
        return view('admin.reports.stock_opname_diff');
    }
}