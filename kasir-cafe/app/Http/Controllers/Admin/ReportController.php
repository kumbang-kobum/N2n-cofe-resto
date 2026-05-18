<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StockOpnameLine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    protected function grossSaleAmount(Sale $sale): float
    {
        return (float) ($sale->grand_total ?: ((float) $sale->total - (float) ($sale->discount_amount ?? 0) + (float) ($sale->tax_amount ?? 0)));
    }

    protected function refundRatio(Sale $sale): float
    {
        $subtotal = (float) ($sale->total ?? 0);

        if ($subtotal <= 0) {
            return 0.0;
        }

        return max(0.0, min(1.0, (float) ($sale->refund_total ?? 0) / $subtotal));
    }

    protected function refundedGrossAmount(Sale $sale): float
    {
        return $this->grossSaleAmount($sale) * $this->refundRatio($sale);
    }

    protected function refundedNetSalesAmount(Sale $sale): float
    {
        $netSales = max(0, (float) ($sale->total ?? 0) - (float) ($sale->discount_amount ?? 0));

        return $netSales * $this->refundRatio($sale);
    }

    protected function refundedCogsAmount(Sale $sale): float
    {
        return (float) ($sale->cogs_total ?? 0) * $this->refundRatio($sale);
    }

    protected function netSaleAmount(Sale $sale): float
    {
        $gross = $this->grossSaleAmount($sale);

        return max(0, $gross - $this->refundedGrossAmount($sale));
    }

    protected function netCogsAmount(Sale $sale): float
    {
        return max(0, (float) ($sale->cogs_total ?? 0) - $this->refundedCogsAmount($sale));
    }

    protected function netProfitAmount(Sale $sale): float
    {
        $netSalesExTax = max(0, ((float) ($sale->total ?? 0) - (float) ($sale->discount_amount ?? 0)) - $this->refundedNetSalesAmount($sale));

        return $netSalesExTax - $this->netCogsAmount($sale);
    }

    protected function decorateSaleMetrics(Sale $sale): Sale
    {
        $sale->effective_refund_total = $this->refundedGrossAmount($sale);
        $sale->effective_net_payment_total = $this->netSaleAmount($sale);
        $sale->effective_cogs_total = $this->netCogsAmount($sale);
        $sale->effective_profit_gross = $this->netProfitAmount($sale);

        return $sale;
    }

    /**
     * Build query & summary untuk laporan penjualan.
     *
     * @param  Request   $request
     * @param  int|null  $cashierId  kalau null → semua kasir, kalau ada → filter kasir tertentu
     * @return array [ $sales, $summary, $from, $to ]
     */
    protected function buildSalesData(Request $request, ?int $cashierId = null, bool $paginate = true): array
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->toDateString());
        $receiptNo = trim((string) $request->query('receipt_no', ''));
        $limit = max(10, min(200, (int) $request->query('limit', 50)));

        $query = Sale::query()
            ->with('cashier')
            ->whereIn('status', ['PAID', 'REFUND'])
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to);

        if ($cashierId) {
            $query->where('cashier_id', $cashierId);
        }

        if ($receiptNo !== '') {
            $query->where('receipt_no', 'like', '%' . $receiptNo . '%');
        }

        $summarySales = (clone $query)
            ->orderByDesc('paid_at')
            ->get()
            ->map(fn (Sale $sale) => $this->decorateSaleMetrics($sale));

        $sales = $paginate
            ? $query->orderByDesc('paid_at')->paginate($limit)->withQueryString()
            : $summarySales;

        if ($paginate) {
            $sales->getCollection()->transform(fn (Sale $sale) => $this->decorateSaleMetrics($sale));
        }

        $summary = [
            'subtotal' => (float) $summarySales->sum('total'),
            'discount' => (float) $summarySales->sum('discount_amount'),
            'tax'      => (float) $summarySales->sum('tax_amount'),
            'omzet'    => (float) $summarySales->sum('grand_total'),
            'refund'   => (float) $summarySales->sum('effective_refund_total'),
            'cogs'     => (float) $summarySales->sum('effective_cogs_total'),
            'profit'   => (float) $summarySales->sum('effective_profit_gross'),
            'per_payment' => [],
        ];

        foreach ($summarySales as $s) {
            $method = strtoupper($s->payment_method ?? 'UNKNOWN');

            if (! isset($summary['per_payment'][$method])) {
                $summary['per_payment'][$method] = 0;
            }

            $summary['per_payment'][$method] += $this->netSaleAmount($s);
        }

        return [$sales, $summary, $from, $to, $receiptNo, $limit];
    }

    protected function exportSalesExcel($sales, string $from, string $to)
    {
        $filename = 'laporan_penjualan_' . $from . '_to_' . $to . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Transaksi');

        $headers = [
            'Tanggal',
            'ID',
            'No Nota',
            'Kasir',
            'Metode',
            'Subtotal',
            'Diskon',
            'Pajak',
            'Total',
            'Refund',
            'COGS',
            'Laba',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $row = 2;
        foreach ($sales as $s) {
            $grand = $s->grand_total ?? ($s->total - ($s->discount_amount ?? 0) + ($s->tax_amount ?? 0));
            $effectiveRefund = $s->effective_refund_total ?? $this->refundedGrossAmount($s);
            $effectiveCogs = $s->effective_cogs_total ?? $this->netCogsAmount($s);
            $effectiveProfit = $s->effective_profit_gross ?? $this->netProfitAmount($s);

            $sheet->setCellValue('A' . $row, optional($s->paid_at)->format('Y-m-d H:i:s'));
            $sheet->setCellValue('B' . $row, $s->id);
            $sheet->setCellValue('C' . $row, $s->receipt_no ?? '-');
            $sheet->setCellValue('D' . $row, optional($s->cashier)->name ?? '-');
            $sheet->setCellValue('E' . $row, strtoupper($s->payment_method ?? '-'));
            $sheet->setCellValue('F' . $row, (float) $s->total);
            $sheet->setCellValue('G' . $row, (float) ($s->discount_amount ?? 0));
            $sheet->setCellValue('H' . $row, (float) ($s->tax_amount ?? 0));
            $sheet->setCellValue('I' . $row, (float) $grand);
            $sheet->setCellValue('J' . $row, (float) $effectiveRefund);
            $sheet->setCellValue('K' . $row, (float) $effectiveCogs);
            $sheet->setCellValue('L' . $row, (float) $effectiveProfit);
            $row++;
        }

        $lastRow = max(2, $row - 1);
        $sheet->getStyle('F2:L' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Laporan penjualan (ADMIN) – semua kasir.
     */
    public function sales(Request $request)
    {
        $cashierId = $request->query('cashier_id');
        $cashierId = $cashierId !== null && $cashierId !== '' ? (int) $cashierId : null;

        [$sales, $summary, $from, $to, $receiptNo, $limit] = $this->buildSalesData($request, $cashierId);

        $cashiers = User::role('cashier')->orderBy('name')->get();
        $selectedCashier = $cashierId;

        return view('admin.reports.sales', compact('sales', 'summary', 'from', 'to', 'cashiers', 'selectedCashier', 'receiptNo', 'limit'));
    }

    public function exportSales(Request $request)
    {
        $cashierId = $request->query('cashier_id');
        $cashierId = $cashierId !== null && $cashierId !== '' ? (int) $cashierId : null;

        [$sales, $summary, $from, $to, $receiptNo, $limit] = $this->buildSalesData($request, $cashierId);

        return $this->exportSalesExcel($sales, $from, $to);
    }

    /**
     * Laporan penjualan untuk KASIR (hanya transaksi kasir yang login).
     */
    public function salesForCashier(Request $request)
    {
        [$sales, $summary, $from, $to, $receiptNo, $limit] = $this->buildSalesData($request, auth()->id());

        return view('admin.reports.sales', compact('sales', 'summary', 'from', 'to', 'receiptNo', 'limit'));
    }

    public function exportSalesForCashier(Request $request)
    {
        [$sales, $summary, $from, $to, $receiptNo, $limit] = $this->buildSalesData($request, auth()->id());

        return $this->exportSalesExcel($sales, $from, $to);
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

    public function topProducts(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());

        $rows = SaleLine::query()
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

        return view('admin.reports.top_products', compact('rows', 'from', 'to'));
    }

    public function allProducts(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());

        $rows = SaleLine::query()
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
            ->get();

        return view('admin.reports.all_products', compact('rows', 'from', 'to'));
    }
}
