<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FinanceReportController extends Controller
{
    protected function buildData(Request $request): array
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $cashierId = $request->query('cashier_id');
        $cashierId = $cashierId !== null && $cashierId !== '' ? (int) $cashierId : null;

        if (auth()->user()->hasRole('cashier')) {
            $cashierId = auth()->id();
        }

        $salesQuery = Sale::query()
            ->with(['cashier', 'lines.product'])
            ->where('status', 'PAID')
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to);

        $expenseQuery = CashExpense::query()
            ->with(['cashier', 'approver'])
            ->whereDate('expense_at', '>=', $from)
            ->whereDate('expense_at', '<=', $to);

        if ($cashierId) {
            $salesQuery->where('cashier_id', $cashierId);
            $expenseQuery->where('cashier_id', $cashierId);
        }

        $sales = $salesQuery->get();
        $allExpenses = $expenseQuery->get();
        $approvedExpenses = $allExpenses->where('status', 'APPROVED')->values();

        $summary = [
            'subtotal' => (float) $sales->sum('total'),
            'discount' => (float) $sales->sum('discount_amount'),
            'tax' => (float) $sales->sum('tax_amount'),
            'omzet' => (float) $sales->sum('grand_total'),
            'refund' => (float) $sales->sum('refund_total'),
            'cogs' => (float) $sales->sum('cogs_total'),
            'gross_profit' => (float) $sales->sum('profit_gross'),
            'expense_total' => (float) $approvedExpenses->sum('amount'),
            'expense_pending' => (float) $allExpenses->where('status', 'PENDING')->sum('amount'),
            'expense_rejected' => (float) $allExpenses->where('status', 'REJECTED')->sum('amount'),
            'cash_sales_total' => (float) $sales
                ->filter(fn ($sale) => strtoupper((string) $sale->payment_method) === 'CASH')
                ->sum('grand_total'),
            'trx_total' => (int) $sales->count(),
        ];

        $summary['omzet_bruto'] = $summary['subtotal'] + $summary['tax'];
        $summary['omzet_netto'] = $summary['omzet'] - $summary['refund'];
        $summary['net_profit'] = $summary['gross_profit'] - $summary['expense_total'];
        $summary['cash_balance'] = $summary['cash_sales_total'] - $summary['expense_total'];
        $summary['per_payment'] = [];

        foreach ($sales as $sale) {
            $method = strtoupper((string) ($sale->payment_method ?: 'UNKNOWN'));
            $summary['per_payment'][$method] = ($summary['per_payment'][$method] ?? 0) + (float) ($sale->grand_total ?? 0);
        }

        $transactionRows = $sales
            ->sortByDesc(fn ($s) => optional($s->paid_at)?->timestamp ?? 0)
            ->map(function ($s) {
                return [
                    'paid_at' => optional($s->paid_at)?->format('Y-m-d H:i:s'),
                    'receipt_no' => $s->receipt_no,
                    'cashier_name' => optional($s->cashier)->name,
                    'payment_method' => strtoupper((string) $s->payment_method),
                    'subtotal' => (float) $s->total,
                    'discount' => (float) ($s->discount_amount ?? 0),
                    'tax' => (float) ($s->tax_amount ?? 0),
                    'total' => (float) ($s->grand_total ?? 0),
                    'refund' => (float) ($s->refund_total ?? 0),
                    'cogs' => (float) ($s->cogs_total ?? 0),
                    'gross_profit' => (float) ($s->profit_gross ?? 0),
                    'status' => (string) $s->status,
                ];
            })
            ->values();

        $itemRows = collect();
        foreach ($sales as $sale) {
            $saleSubtotal = (float) ($sale->total ?? 0);
            $lineCount = max(1, $sale->lines->count());
            foreach ($sale->lines as $line) {
                $lineSubtotal = (float) (($line->qty ?? 0) * ($line->price ?? 0));
                $ratio = $saleSubtotal > 0 ? ($lineSubtotal / $saleSubtotal) : (1 / $lineCount);
                $lineCogs = (float) ($sale->cogs_total ?? 0) * $ratio;
                $lineProfit = $lineSubtotal - $lineCogs;

                $itemRows->push([
                    'paid_at' => optional($sale->paid_at)?->format('Y-m-d H:i:s'),
                    'receipt_no' => $sale->receipt_no,
                    'menu' => optional($line->product)->name ?? ('#' . $line->product_id),
                    'qty' => (float) ($line->qty ?? 0),
                    'price' => (float) ($line->price ?? 0),
                    'omzet' => $lineSubtotal,
                    'cogs_estimated' => $lineCogs,
                    'profit_estimated' => $lineProfit,
                    'cashier_name' => optional($sale->cashier)->name,
                ]);
            }
        }

        $menuAnalysisRows = $itemRows
            ->groupBy('menu')
            ->map(function ($rows, $menu) {
                $qty = (float) $rows->sum('qty');
                $omzet = (float) $rows->sum('omzet');
                $cogs = (float) $rows->sum('cogs_estimated');
                $profit = $omzet - $cogs;
                return [
                    'menu' => $menu,
                    'qty' => $qty,
                    'omzet' => $omzet,
                    'cogs_estimated' => $cogs,
                    'profit_estimated' => $profit,
                    'profit_margin_pct' => $omzet > 0 ? ($profit / $omzet) * 100 : 0,
                ];
            })
            ->sortByDesc('omzet')
            ->values();

        $daily = [];
        foreach ($sales as $sale) {
            $key = optional($sale->paid_at)->format('Y-m-d');
            if (! $key) {
                continue;
            }
            $daily[$key] ??= [
                'date' => $key,
                'omzet' => 0,
                'refund' => 0,
                'cogs' => 0,
                'gross_profit' => 0,
                'expense_total' => 0,
            ];
            $daily[$key]['omzet'] += (float) ($sale->grand_total ?? 0);
            $daily[$key]['refund'] += (float) ($sale->refund_total ?? 0);
            $daily[$key]['cogs'] += (float) ($sale->cogs_total ?? 0);
            $daily[$key]['gross_profit'] += (float) ($sale->profit_gross ?? 0);
        }

        foreach ($approvedExpenses as $expense) {
            $key = optional($expense->expense_at)->format('Y-m-d');
            if (! $key) {
                continue;
            }
            $daily[$key] ??= [
                'date' => $key,
                'omzet' => 0,
                'refund' => 0,
                'cogs' => 0,
                'gross_profit' => 0,
                'expense_total' => 0,
            ];
            $daily[$key]['expense_total'] += (float) ($expense->amount ?? 0);
        }

        $dailyRows = collect($daily)
            ->map(function ($row) {
                $row['net_profit'] = $row['gross_profit'] - $row['expense_total'];
                return $row;
            })
            ->sortByDesc('date')
            ->values();

        $monthly = [];
        foreach ($dailyRows as $row) {
            $month = substr($row['date'], 0, 7);
            $monthly[$month] ??= [
                'month' => $month,
                'omzet' => 0,
                'refund' => 0,
                'cogs' => 0,
                'gross_profit' => 0,
                'expense_total' => 0,
                'net_profit' => 0,
            ];
            $monthly[$month]['omzet'] += $row['omzet'];
            $monthly[$month]['refund'] += $row['refund'];
            $monthly[$month]['cogs'] += $row['cogs'];
            $monthly[$month]['gross_profit'] += $row['gross_profit'];
            $monthly[$month]['expense_total'] += $row['expense_total'];
            $monthly[$month]['net_profit'] += $row['net_profit'];
        }

        $monthlyRows = collect($monthly)->sortByDesc('month')->values();

        $cashiers = User::role('cashier')->orderBy('name')->get();

        return [
            'from' => $from,
            'to' => $to,
            'cashierId' => $cashierId,
            'cashiers' => $cashiers,
            'summary' => $summary,
            'dailyRows' => $dailyRows,
            'monthlyRows' => $monthlyRows,
            'transactionRows' => $transactionRows,
            'itemRows' => $itemRows->sortByDesc('paid_at')->values(),
            'menuAnalysisRows' => $menuAnalysisRows,
            'expenseRows' => $allExpenses->sortByDesc('expense_at')->values(),
        ];
    }

    public function index(Request $request)
    {
        $data = $this->buildData($request);

        return view('admin.reports.finance', $data);
    }

    public function export(Request $request)
    {
        $data = $this->buildData($request);

        $filename = 'laporan_keuangan_' . $data['from'] . '_to_' . $data['to'] . '.xlsx';
        $spreadsheet = new Spreadsheet();

        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Executive Summary');
        $summarySheet->fromArray([
            ['Periode', $data['from'] . ' s/d ' . $data['to']],
            ['Total Transaksi', $data['summary']['trx_total']],
            ['Omzet Bruto', $data['summary']['omzet_bruto']],
            ['Subtotal', $data['summary']['subtotal']],
            ['Diskon', $data['summary']['discount']],
            ['Pajak', $data['summary']['tax']],
            ['Omzet (Setelah Diskon + Pajak)', $data['summary']['omzet']],
            ['Refund', $data['summary']['refund']],
            ['Omzet Netto (Setelah Refund)', $data['summary']['omzet_netto']],
            ['COGS (HPP)', $data['summary']['cogs']],
            ['Laba Kotor', $data['summary']['gross_profit']],
            ['Pengeluaran Approved', $data['summary']['expense_total']],
            ['Pengeluaran Pending', $data['summary']['expense_pending']],
            ['Pengeluaran Rejected', $data['summary']['expense_rejected']],
            ['Laba Bersih', $data['summary']['net_profit']],
            ['Saldo Kas (CASH - Pengeluaran)', $data['summary']['cash_balance']],
        ], null, 'A1');

        $summarySheet->fromArray([['']], null, 'A17');
        $summarySheet->fromArray([['Metode Bayar', 'Nominal']], null, 'A18');
        $summaryRow = 19;
        foreach ($data['summary']['per_payment'] as $method => $amount) {
            $summarySheet->fromArray([[$method, (float) $amount]], null, 'A' . $summaryRow);
            $summaryRow++;
        }

        $trxSheet = $spreadsheet->createSheet();
        $trxSheet->setTitle('Detail Transaksi');
        $trxSheet->fromArray(
            [[
                'Tanggal',
                'No Nota',
                'Kasir',
                'Metode',
                'Subtotal',
                'Diskon',
                'Pajak',
                'Total',
                'Refund',
                'COGS',
                'Laba Kotor',
                'Status',
            ]],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['transactionRows'] as $r) {
            $trxSheet->fromArray([[
                $r['paid_at'],
                $r['receipt_no'],
                $r['cashier_name'],
                $r['payment_method'],
                $r['subtotal'],
                $r['discount'],
                $r['tax'],
                $r['total'],
                $r['refund'],
                $r['cogs'],
                $r['gross_profit'],
                $r['status'],
            ]], null, 'A' . $row);
            $row++;
        }

        $itemSheet = $spreadsheet->createSheet();
        $itemSheet->setTitle('Detail Item');
        $itemSheet->fromArray(
            [[
                'Tanggal',
                'No Nota',
                'Kasir',
                'Menu',
                'Qty',
                'Harga Jual',
                'Omzet Item',
                'COGS Estimasi',
                'Profit Estimasi',
            ]],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['itemRows'] as $r) {
            $itemSheet->fromArray([[
                $r['paid_at'],
                $r['receipt_no'],
                $r['cashier_name'],
                $r['menu'],
                $r['qty'],
                $r['price'],
                $r['omzet'],
                $r['cogs_estimated'],
                $r['profit_estimated'],
            ]], null, 'A' . $row);
            $row++;
        }

        $expenseSheet = $spreadsheet->createSheet();
        $expenseSheet->setTitle('Pengeluaran Harian');
        $expenseSheet->fromArray(
            [[
                'Waktu',
                'Kategori',
                'Nominal',
                'Kasir Input',
                'Status',
                'Approver',
                'Waktu Approval',
                'Catatan',
                'Catatan Approval',
            ]],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['expenseRows'] as $r) {
            $expenseSheet->fromArray([[
                optional($r->expense_at)->format('Y-m-d H:i:s'),
                $r->category,
                (float) $r->amount,
                optional($r->cashier)->name,
                $r->status,
                optional($r->approver)->name,
                optional($r->approved_at)->format('Y-m-d H:i:s'),
                $r->note,
                $r->approval_note,
            ]], null, 'A' . $row);
            $row++;
        }

        $periodSheet = $spreadsheet->createSheet();
        $periodSheet->setTitle('Rekap Harian Bulanan');
        $periodSheet->fromArray(
            [['Tipe', 'Periode', 'Omzet', 'Refund', 'COGS', 'Laba Kotor', 'Pengeluaran', 'Laba Bersih']],
            null,
            'A1'
        );

        $row = 2;
        foreach ($data['dailyRows'] as $r) {
            $periodSheet->fromArray([[
                'HARIAN',
                $r['date'],
                $r['omzet'],
                $r['refund'],
                $r['cogs'],
                $r['gross_profit'],
                $r['expense_total'],
                $r['net_profit'],
            ]], null, 'A' . $row);
            $row++;
        }

        foreach ($data['monthlyRows'] as $r) {
            $periodSheet->fromArray([[
                'BULANAN',
                $r['month'],
                $r['omzet'],
                $r['refund'],
                $r['cogs'],
                $r['gross_profit'],
                $r['expense_total'],
                $r['net_profit'],
            ]], null, 'A' . $row);
            $row++;
        }

        $menuSheet = $spreadsheet->createSheet();
        $menuSheet->setTitle('Analisis Menu');
        $menuSheet->fromArray(
            [['Menu', 'Qty', 'Omzet', 'COGS Estimasi', 'Profit Estimasi', 'Margin Profit %']],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['menuAnalysisRows'] as $r) {
            $menuSheet->fromArray([[
                $r['menu'],
                $r['qty'],
                $r['omzet'],
                $r['cogs_estimated'],
                $r['profit_estimated'],
                $r['profit_margin_pct'] / 100,
            ]], null, 'A' . $row);
            $row++;
        }

        foreach ([$summarySheet, $trxSheet, $itemSheet, $expenseSheet, $periodSheet, $menuSheet] as $sheet) {
            foreach (range('A', 'L') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        $this->applyHeaderStyle($summarySheet, 'A1:B18');
        $this->applyHeaderStyle($trxSheet, 'A1:L1');
        $this->applyHeaderStyle($itemSheet, 'A1:I1');
        $this->applyHeaderStyle($expenseSheet, 'A1:I1');
        $this->applyHeaderStyle($periodSheet, 'A1:H1');
        $this->applyHeaderStyle($menuSheet, 'A1:F1');

        $this->applyProfitColorBySign($summarySheet, 'B15'); // Laba Bersih
        $this->applyProfitColorBySign($trxSheet, 'K2:K9999'); // Laba Kotor transaksi
        $this->applyProfitColorBySign($itemSheet, 'I2:I9999'); // Profit estimasi item
        $this->applyProfitColorBySign($periodSheet, 'H2:H9999'); // Laba bersih harian/bulanan
        $this->applyProfitColorBySign($menuSheet, 'E2:E9999'); // Profit estimasi menu

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function applyHeaderStyle($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1D4ED8'],
            ],
        ]);
    }

    protected function applyProfitColorBySign($sheet, string $range): void
    {
        $conditionalStyles = $sheet->getStyle($range)->getConditionalStyles();

        $positive = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $positive->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $positive->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHANOREQUAL);
        $positive->addCondition('0');
        $positive->getStyle()->applyFromArray([
            'font' => ['color' => ['rgb' => '166534'], 'bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DCFCE7'],
            ],
        ]);

        $negative = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $negative->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $negative->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
        $negative->addCondition('0');
        $negative->getStyle()->applyFromArray([
            'font' => ['color' => ['rgb' => '991B1B'], 'bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEE2E2'],
            ],
        ]);

        $conditionalStyles[] = $positive;
        $conditionalStyles[] = $negative;
        $sheet->getStyle($range)->setConditionalStyles($conditionalStyles);
    }
}
