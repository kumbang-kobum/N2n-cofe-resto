<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockOpnameLine;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OpnameVarianceReportController extends Controller
{
    public function index(Request $request)
    {
        // default: bulan ini
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        // default: POSTED (biar variance yang sudah mengubah stok)
        $status = $request->input('status', 'POSTED');
        $qItem  = trim((string) $request->input('q', ''));

        $linesQuery = StockOpnameLine::query()
            ->select([
                'stock_opname_lines.*',
            ])
            ->with(['item.baseUnit', 'opname'])
            ->whereHas('opname', function ($q) use ($from, $to, $status) {
                $q->whereBetween('counted_at', [$from, $to]);

                if ($status !== 'ALL') {
                    $q->where('status', $status);
                }
            })
            ->when($qItem !== '', function ($q) use ($qItem) {
                $q->whereHas('item', function ($qi) use ($qItem) {
                    $qi->where('name', 'like', "%{$qItem}%");
                });
            })
            ->orderByDesc('stock_opname_id');

        $lines = $linesQuery->paginate(30)->withQueryString();

        // Summary (total plus/minus/value)
        $allForSummary = (clone $linesQuery)->get();

        $totalPlusQty  = 0.0;
        $totalMinusQty = 0.0;
        $totalPlusVal  = 0.0;
        $totalMinusVal = 0.0;

        foreach ($allForSummary as $l) {
            $diff = (float) $l->diff_qty_base;
            $cost = (float) ($l->unit_cost_base ?? 0);

            if ($diff > 0) {
                $totalPlusQty += $diff;
                $totalPlusVal += $diff * $cost;
            } elseif ($diff < 0) {
                $totalMinusQty += abs($diff);
                $totalMinusVal += abs($diff) * $cost; // catatan: pakai cost line
            }
        }

        $netQty = $totalPlusQty - $totalMinusQty;
        $netVal = $totalPlusVal - $totalMinusVal;

        return view('admin.reports.opname_variance', [
            'lines' => $lines,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'status' => $status,
                'q' => $qItem,
            ],
            'summary' => [
                'plus_qty' => $totalPlusQty,
                'minus_qty' => $totalMinusQty,
                'plus_val' => $totalPlusVal,
                'minus_val' => $totalMinusVal,
                'net_qty' => $netQty,
                'net_val' => $netVal,
            ],
        ]);
    }

    public function pdf(Request $request)
    {
        // sama dengan index, hanya output PDF
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());
        $status = $request->input('status', 'POSTED');
        $qItem  = trim((string) $request->input('q', ''));

        $lines = StockOpnameLine::query()
            ->with(['item.baseUnit', 'opname'])
            ->whereHas('opname', function ($q) use ($from, $to, $status) {
                $q->whereBetween('counted_at', [$from, $to]);
                if ($status !== 'ALL') {
                    $q->where('status', $status);
                }
            })
            ->when($qItem !== '', function ($q) use ($qItem) {
                $q->whereHas('item', function ($qi) use ($qItem) {
                    $qi->where('name', 'like', "%{$qItem}%");
                });
            })
            ->orderByDesc('stock_opname_id')
            ->get();

        $totalPlusQty  = 0.0;
        $totalMinusQty = 0.0;
        $totalPlusVal  = 0.0;
        $totalMinusVal = 0.0;

        foreach ($lines as $l) {
            $diff = (float) $l->diff_qty_base;
            $cost = (float) ($l->unit_cost_base ?? 0);

            if ($diff > 0) {
                $totalPlusQty += $diff;
                $totalPlusVal += $diff * $cost;
            } elseif ($diff < 0) {
                $totalMinusQty += abs($diff);
                $totalMinusVal += abs($diff) * $cost;
            }
        }

        $pdf = Pdf::loadView('admin.reports.opname_variance_pdf', [
            'lines' => $lines,
            'filters' => compact('from', 'to', 'status', 'qItem'),
            'summary' => [
                'plus_qty' => $totalPlusQty,
                'minus_qty' => $totalMinusQty,
                'plus_val' => $totalPlusVal,
                'minus_val' => $totalMinusVal,
                'net_qty' => $totalPlusQty - $totalMinusQty,
                'net_val' => $totalPlusVal - $totalMinusVal,
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan-Selisih-Opname-{$from}-sd-{$to}.pdf");
    }
}