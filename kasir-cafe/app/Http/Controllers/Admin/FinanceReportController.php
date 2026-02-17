<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
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
            ->with('cashier')
            ->where('status', 'PAID')
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to);

        $expenseQuery = CashExpense::query()
            ->with('cashier')
            ->whereDate('expense_at', '>=', $from)
            ->whereDate('expense_at', '<=', $to)
            ->where('status', 'APPROVED');

        if ($cashierId) {
            $salesQuery->where('cashier_id', $cashierId);
            $expenseQuery->where('cashier_id', $cashierId);
        }

        $sales = $salesQuery->get();
        $expenses = $expenseQuery->get();

        $summary = [
            'subtotal' => (float) $sales->sum('total'),
            'discount' => (float) $sales->sum('discount_amount'),
            'tax' => (float) $sales->sum('tax_amount'),
            'omzet' => (float) $sales->sum('grand_total'),
            'refund' => (float) $sales->sum('refund_total'),
            'cogs' => (float) $sales->sum('cogs_total'),
            'gross_profit' => (float) $sales->sum('profit_gross'),
            'expense_total' => (float) $expenses->sum('amount'),
            'cash_sales_total' => (float) $sales
                ->filter(fn ($sale) => strtoupper((string) $sale->payment_method) === 'CASH')
                ->sum('grand_total'),
        ];

        $summary['net_profit'] = $summary['gross_profit'] - $summary['expense_total'];
        $summary['cash_balance'] = $summary['cash_sales_total'] - $summary['expense_total'];

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

        foreach ($expenses as $expense) {
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
        $summarySheet->setTitle('Ringkasan');
        $summarySheet->fromArray([
            ['Periode', $data['from'] . ' s/d ' . $data['to']],
            ['Subtotal', $data['summary']['subtotal']],
            ['Diskon', $data['summary']['discount']],
            ['Pajak', $data['summary']['tax']],
            ['Omzet', $data['summary']['omzet']],
            ['Refund', $data['summary']['refund']],
            ['COGS (HPP)', $data['summary']['cogs']],
            ['Laba Kotor', $data['summary']['gross_profit']],
            ['Pengeluaran Kas (Approved)', $data['summary']['expense_total']],
            ['Laba Bersih', $data['summary']['net_profit']],
            ['Saldo Kas (CASH - Pengeluaran)', $data['summary']['cash_balance']],
        ], null, 'A1');

        $dailySheet = $spreadsheet->createSheet();
        $dailySheet->setTitle('Harian');
        $dailySheet->fromArray(
            [['Tanggal', 'Omzet', 'Refund', 'COGS', 'Laba Kotor', 'Pengeluaran', 'Laba Bersih']],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['dailyRows'] as $r) {
            $dailySheet->fromArray([[
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

        $monthlySheet = $spreadsheet->createSheet();
        $monthlySheet->setTitle('Bulanan');
        $monthlySheet->fromArray(
            [['Bulan', 'Omzet', 'Refund', 'COGS', 'Laba Kotor', 'Pengeluaran', 'Laba Bersih']],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['monthlyRows'] as $r) {
            $monthlySheet->fromArray([[
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

        foreach ([$summarySheet, $dailySheet, $monthlySheet] as $sheet) {
            foreach (range('A', 'H') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
