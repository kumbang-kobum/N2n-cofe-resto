<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use App\Models\EmployeeMeal;
use App\Models\PettyCashFund;
use App\Models\Payroll;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FinanceReportController extends Controller
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
        return max(0, $this->grossSaleAmount($sale) - $this->refundedGrossAmount($sale));
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
            ->whereIn('status', ['PAID', 'REFUND'])
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to);

        $expenseQuery = CashExpense::query()
            ->with(['cashier', 'approver', 'pettyCashFund'])
            ->whereDate('expense_at', '>=', $from)
            ->whereDate('expense_at', '<=', $to);

        if ($cashierId) {
            $salesQuery->where('cashier_id', $cashierId);
            $expenseQuery->where('cashier_id', $cashierId);
        }

        $payrollQuery = Payroll::query()
            ->with(['employeeMaster', 'employee', 'approver', 'payer'])
            ->where('status', 'PAID')
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', '>=', $from)
            ->whereDate('paid_at', '<=', $to);

        $employeeMealQuery = EmployeeMeal::query()
            ->with(['employee', 'cashier', 'payroll', 'lines.product'])
            ->whereDate('consumed_at', '>=', $from)
            ->whereDate('consumed_at', '<=', $to);

        if ($cashierId) {
            $payrollQuery->where(function ($q) use ($cashierId) {
                $q->where('employee_id', $cashierId)
                    ->orWhereHas('employeeMaster', fn ($qq) => $qq->where('user_id', $cashierId));
            });
            $employeeMealQuery->where('cashier_id', $cashierId);
        }

        $sales = $salesQuery->get()->map(fn (Sale $sale) => $this->decorateSaleMetrics($sale));
        $allExpenses = $expenseQuery->get();
        $approvedExpenses = $allExpenses->where('status', 'APPROVED')->values();
        $payrolls = $payrollQuery->get();
        $employeeMeals = $employeeMealQuery->get();
        $directApprovedExpenses = $approvedExpenses->where('funding_source', 'DIRECT_CASH')->values();
        $pettyCashApprovedExpenses = $approvedExpenses->where('funding_source', 'PETTY_CASH')->values();
        $expenseCategoryRows = $approvedExpenses
            ->groupBy(fn ($expense) => $expense->expenseCategory?->name ?: $expense->category ?: 'Tanpa Kategori')
            ->map(function ($rows, $category) {
                $direct = $rows->where('funding_source', 'DIRECT_CASH')->sum('amount');
                $petty = $rows->where('funding_source', 'PETTY_CASH')->sum('amount');
                return [
                    'category' => $category,
                    'count' => (int) $rows->count(),
                    'direct_total' => (float) $direct,
                    'petty_cash_total' => (float) $petty,
                    'total' => (float) $rows->sum('amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();
        $pettyCashFunds = PettyCashFund::query()
            ->withSum(['expenses as approved_used_total' => fn ($query) => $query->where('status', 'APPROVED')], 'amount')
            ->whereDate('period_start', '<=', $to)
            ->whereDate('period_end', '>=', $from)
            ->get();

        $pettyCashOpeningTotal = (float) $pettyCashFunds->sum('opening_balance');
        $pettyCashReturnedTotal = (float) $pettyCashFunds->sum('returned_amount');
        $pettyCashBalanceTotal = (float) $pettyCashFunds->sum(function ($fund) {
            return (float) $fund->opening_balance - (float) ($fund->approved_used_total ?? 0) - (float) $fund->returned_amount;
        });

        $summary = [
            'subtotal' => (float) $sales->sum('total'),
            'discount' => (float) $sales->sum('discount_amount'),
            'tax' => (float) $sales->sum('tax_amount'),
            'omzet' => (float) $sales->sum('grand_total'),
            'refund' => (float) $sales->sum('effective_refund_total'),
            'cogs' => (float) $sales->sum('effective_cogs_total'),
            'gross_profit' => (float) $sales->sum('effective_profit_gross'),
            'expense_total' => (float) $approvedExpenses->sum('amount'),
            'direct_expense_total' => (float) $directApprovedExpenses->sum('amount'),
            'petty_cash_expense_total' => (float) $pettyCashApprovedExpenses->sum('amount'),
            'expense_pending' => (float) $allExpenses->where('status', 'PENDING')->sum('amount'),
            'expense_rejected' => (float) $allExpenses->where('status', 'REJECTED')->sum('amount'),
            'petty_cash_opening_total' => $pettyCashOpeningTotal,
            'petty_cash_returned_total' => $pettyCashReturnedTotal,
            'petty_cash_balance_total' => $pettyCashBalanceTotal,
            'employee_meal_total' => (float) $employeeMeals->sum('total_amount'),
            'employee_meal_cogs_total' => (float) $employeeMeals->sum('cogs_total'),
            'employee_meal_expense_total' => (float) $employeeMeals->sum('expense_cogs_total'),
            'employee_meal_excess_total' => (float) $employeeMeals->sum('excess_amount'),
            'employee_meal_pending_deduction' => (float) $employeeMeals->whereNull('payroll_id')->sum('excess_amount'),
            'employee_meal_count' => (int) $employeeMeals->count(),
            'payroll_total' => (float) $payrolls->sum(fn ($payroll) => $payroll->expense_amount),
            'payroll_cash_total' => (float) $payrolls->sum('net_amount'),
            'cash_sales_total' => (float) $sales
                ->filter(fn ($sale) => strtoupper((string) $sale->payment_method) === 'CASH')
                ->sum(fn (Sale $sale) => $this->netSaleAmount($sale)),
            'trx_total' => (int) $sales->count(),
        ];

        $summary['omzet_bruto'] = $summary['subtotal'] + $summary['tax'];
        $summary['omzet_netto'] = $summary['omzet'] - $summary['refund'];
        $summary['net_profit'] = $summary['gross_profit'] - $summary['expense_total'] - $summary['employee_meal_expense_total'];
        $summary['net_profit_after_payroll'] = $summary['net_profit'] - $summary['payroll_total'];
        $summary['cash_balance'] = $summary['cash_sales_total'] - $summary['direct_expense_total'];
        $summary['cash_balance_after_payroll'] = $summary['cash_balance'] - $summary['payroll_cash_total'];
        $summary['per_payment'] = [];

        foreach ($sales as $sale) {
            $method = strtoupper((string) ($sale->payment_method ?: 'UNKNOWN'));
            $summary['per_payment'][$method] = ($summary['per_payment'][$method] ?? 0) + $this->netSaleAmount($sale);
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
                    'refund' => (float) ($s->effective_refund_total ?? 0),
                    'cogs' => (float) ($s->effective_cogs_total ?? 0),
                    'gross_profit' => (float) ($s->effective_profit_gross ?? 0),
                    'status' => (string) $s->status,
                ];
            })
            ->values();

        $itemRows = collect();
        foreach ($sales as $sale) {
            $saleSubtotal = (float) ($sale->total ?? 0);
            $lineCount = max(1, $sale->lines->count());
            $saleDiscount = (float) ($sale->discount_amount ?? 0);
            $saleTax = (float) ($sale->tax_amount ?? 0);
            $saleRefund = (float) ($sale->effective_refund_total ?? 0);
            foreach ($sale->lines as $line) {
                $lineSubtotal = (float) (($line->qty ?? 0) * ($line->price ?? 0));
                $ratio = $saleSubtotal > 0 ? ($lineSubtotal / $saleSubtotal) : (1 / $lineCount);
                $lineDiscount = $saleDiscount * $ratio;
                $lineTax = $saleTax * $ratio;
                $lineRefund = $saleRefund * $ratio;
                $lineNetOmzet = $lineSubtotal - $lineDiscount + $lineTax - $lineRefund;
                $lineCogs = (float) ($sale->effective_cogs_total ?? 0) * $ratio;
                $lineProfit = $lineNetOmzet - $lineCogs;

                $itemRows->push([
                    'paid_at' => optional($sale->paid_at)?->format('Y-m-d H:i:s'),
                    'receipt_no' => $sale->receipt_no,
                    'menu' => optional($line->product)->name ?? ('#' . $line->product_id),
                    'qty' => (float) ($line->qty ?? 0),
                    'price' => (float) ($line->price ?? 0),
                    'gross_omzet' => $lineSubtotal,
                    'discount_allocated' => $lineDiscount,
                    'tax_allocated' => $lineTax,
                    'refund_allocated' => $lineRefund,
                    'net_omzet' => $lineNetOmzet,
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
                $grossOmzet = (float) $rows->sum('gross_omzet');
                $discountAllocated = (float) $rows->sum('discount_allocated');
                $taxAllocated = (float) $rows->sum('tax_allocated');
                $refundAllocated = (float) $rows->sum('refund_allocated');
                $netOmzet = (float) $rows->sum('net_omzet');
                $cogs = (float) $rows->sum('cogs_estimated');
                $profit = $netOmzet - $cogs;
                return [
                    'menu' => $menu,
                    'qty' => $qty,
                    'gross_omzet' => $grossOmzet,
                    'discount_allocated' => $discountAllocated,
                    'tax_allocated' => $taxAllocated,
                    'refund_allocated' => $refundAllocated,
                    'net_omzet' => $netOmzet,
                    'cogs_estimated' => $cogs,
                    'profit_estimated' => $profit,
                    'profit_margin_pct' => $netOmzet > 0 ? ($profit / $netOmzet) * 100 : 0,
                ];
            })
            ->sortByDesc('net_omzet')
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
                'direct_expense_total' => 0,
                'petty_cash_expense_total' => 0,
                'employee_meal_expense_total' => 0,
                'payroll_total' => 0,
            ];
            $daily[$key]['omzet'] += (float) ($sale->grand_total ?? 0);
            $daily[$key]['refund'] += (float) ($sale->effective_refund_total ?? 0);
            $daily[$key]['cogs'] += (float) ($sale->effective_cogs_total ?? 0);
            $daily[$key]['gross_profit'] += (float) ($sale->effective_profit_gross ?? 0);
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
                'direct_expense_total' => 0,
                'petty_cash_expense_total' => 0,
                'employee_meal_expense_total' => 0,
                'payroll_total' => 0,
            ];
            $daily[$key]['expense_total'] += (float) ($expense->amount ?? 0);
            if (($expense->funding_source ?? 'DIRECT_CASH') === 'PETTY_CASH') {
                $daily[$key]['petty_cash_expense_total'] += (float) ($expense->amount ?? 0);
            } else {
                $daily[$key]['direct_expense_total'] += (float) ($expense->amount ?? 0);
            }
        }

        foreach ($employeeMeals as $employeeMeal) {
            $key = optional($employeeMeal->consumed_at)->format('Y-m-d');
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
                'direct_expense_total' => 0,
                'petty_cash_expense_total' => 0,
                'employee_meal_expense_total' => 0,
                'payroll_total' => 0,
            ];
            $daily[$key]['employee_meal_expense_total'] += (float) ($employeeMeal->expense_cogs_total ?? 0);
        }

        foreach ($payrolls as $payroll) {
            $key = optional($payroll->paid_at)->format('Y-m-d');
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
                'direct_expense_total' => 0,
                'petty_cash_expense_total' => 0,
                'employee_meal_expense_total' => 0,
                'payroll_total' => 0,
            ];
            $daily[$key]['payroll_total'] += (float) ($payroll->expense_amount ?? 0);
        }

        $dailyRows = collect($daily)
            ->map(function ($row) {
                $row['net_profit'] = $row['gross_profit'] - $row['expense_total'] - $row['employee_meal_expense_total'];
                $row['net_after_payroll'] = $row['net_profit'] - $row['payroll_total'];
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
                'direct_expense_total' => 0,
                'petty_cash_expense_total' => 0,
                'employee_meal_expense_total' => 0,
                'net_profit' => 0,
                'payroll_total' => 0,
                'net_after_payroll' => 0,
            ];
            $monthly[$month]['omzet'] += $row['omzet'];
            $monthly[$month]['refund'] += $row['refund'];
            $monthly[$month]['cogs'] += $row['cogs'];
            $monthly[$month]['gross_profit'] += $row['gross_profit'];
            $monthly[$month]['expense_total'] += $row['expense_total'];
            $monthly[$month]['direct_expense_total'] += $row['direct_expense_total'];
            $monthly[$month]['petty_cash_expense_total'] += $row['petty_cash_expense_total'];
            $monthly[$month]['employee_meal_expense_total'] += $row['employee_meal_expense_total'];
            $monthly[$month]['net_profit'] += $row['net_profit'];
            $monthly[$month]['payroll_total'] += $row['payroll_total'];
            $monthly[$month]['net_after_payroll'] += $row['net_after_payroll'];
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
            'expenseCategoryRows' => $expenseCategoryRows,
            'pettyCashFunds' => $pettyCashFunds,
            'employeeMealRows' => $employeeMeals->sortByDesc('consumed_at')->values(),
            'payrollRows' => $payrolls->sortByDesc('paid_at')->values(),
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
        $summarySheet->setTitle('Ringkasan Keuangan');
        $summaryRows = [
            ['Periode', $data['from'] . ' s/d ' . $data['to']],
            ['Total Transaksi', (int) $data['summary']['trx_total']],
            ['Omzet Bruto', (float) $data['summary']['omzet_bruto']],
            ['Subtotal', (float) $data['summary']['subtotal']],
            ['Diskon', (float) $data['summary']['discount']],
            ['Pajak', (float) $data['summary']['tax']],
            ['Omzet (Setelah Diskon + Pajak)', (float) $data['summary']['omzet']],
            ['Refund', (float) $data['summary']['refund']],
            ['Omzet Netto (Setelah Refund)', (float) $data['summary']['omzet_netto']],
            ['COGS (HPP)', (float) $data['summary']['cogs']],
            ['Laba Kotor', (float) $data['summary']['gross_profit']],
            ['Pengeluaran Approved', (float) $data['summary']['expense_total']],
            ['Pengeluaran Kas Penjualan', (float) $data['summary']['direct_expense_total']],
            ['Pengeluaran Kas Kecil', (float) $data['summary']['petty_cash_expense_total']],
            ['Dana Kas Kecil Dibuka', (float) $data['summary']['petty_cash_opening_total']],
            ['Kas Kecil Dikembalikan', (float) $data['summary']['petty_cash_returned_total']],
            ['Saldo Kas Kecil Berjalan', (float) $data['summary']['petty_cash_balance_total']],
            ['Beban Konsumsi Karyawan', (float) $data['summary']['employee_meal_expense_total']],
            ['Nilai Makan Karyawan', (float) $data['summary']['employee_meal_total']],
            ['Lebih Jatah Karyawan', (float) $data['summary']['employee_meal_excess_total']],
            ['Pengeluaran Pending', (float) $data['summary']['expense_pending']],
            ['Pengeluaran Rejected', (float) $data['summary']['expense_rejected']],
            ['Laba Bersih (tanpa payroll)', (float) $data['summary']['net_profit']],
            ['Payroll Terbayar', (float) $data['summary']['payroll_total']],
            ['Kas Keluar Payroll', (float) $data['summary']['payroll_cash_total']],
            ['Laba Bersih Setelah Payroll', (float) $data['summary']['net_profit_after_payroll']],
            ['Saldo Kas Penjualan (CASH - Pengeluaran Langsung)', (float) $data['summary']['cash_balance']],
            ['Saldo Kas Setelah Payroll', (float) $data['summary']['cash_balance_after_payroll']],
        ];
        foreach ($summaryRows as $index => $summaryRowData) {
            $excelRow = $index + 1;
            $summarySheet->setCellValue('A' . $excelRow, $summaryRowData[0]);
            $summarySheet->setCellValue('B' . $excelRow, $summaryRowData[1]);
        }

        $summarySheet->fromArray([['']], null, 'A30');
        $summarySheet->fromArray([['Metode Bayar', 'Nominal']], null, 'A31');
        $summaryRow = 32;
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
                'Omzet Kotor',
                'Diskon Alokasi',
                'Pajak Alokasi',
                'Refund Alokasi',
                'Omzet Netto Item',
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
                $r['gross_omzet'],
                $r['discount_allocated'],
                $r['tax_allocated'],
                $r['refund_allocated'],
                $r['net_omzet'],
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
                'Diajukan Oleh',
                'Sumber Dana',
                'Dana Kas Kecil',
                'Status',
                'Approver',
                'Waktu Approval',
                'Bukti',
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
                $r->expenseCategory?->name ?: $r->category,
                (float) $r->amount,
                optional($r->cashier)->name,
                ($r->funding_source ?? 'DIRECT_CASH') === 'PETTY_CASH' ? 'Kas Kecil' : 'Kas Penjualan',
                optional($r->pettyCashFund)->name,
                $r->status,
                optional($r->approver)->name,
                optional($r->approved_at)->format('Y-m-d H:i:s'),
                $r->receipt_path ? asset('storage/' . $r->receipt_path) : null,
                $r->note,
                $r->approval_note,
            ]], null, 'A' . $row);
            $row++;
        }

        $expenseCategorySheet = $spreadsheet->createSheet();
        $expenseCategorySheet->setTitle('Kategori Pengeluaran');
        $expenseCategorySheet->fromArray(
            [[
                'Kategori',
                'Jumlah Transaksi',
                'Kas Penjualan',
                'Kas Kecil',
                'Total Approved',
            ]],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['expenseCategoryRows'] as $r) {
            $expenseCategorySheet->fromArray([[
                $r['category'],
                $r['count'],
                $r['direct_total'],
                $r['petty_cash_total'],
                $r['total'],
            ]], null, 'A' . $row);
            $row++;
        }

        $pettyCashSheet = $spreadsheet->createSheet();
        $pettyCashSheet->setTitle('Ringkasan Kas Kecil');
        $pettyCashSheet->fromArray(
            [[
                'Periode Mulai',
                'Periode Selesai',
                'Nama Dana',
                'Status',
                'Dana Awal',
                'Terpakai Approved',
                'Dikembalikan',
                'Saldo',
            ]],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['pettyCashFunds'] as $fund) {
            $used = (float) ($fund->approved_used_total ?? 0);
            $remaining = (float) $fund->opening_balance - $used - (float) $fund->returned_amount;
            $pettyCashSheet->fromArray([[
                optional($fund->period_start)->format('Y-m-d'),
                optional($fund->period_end)->format('Y-m-d'),
                $fund->name,
                $fund->status,
                (float) $fund->opening_balance,
                $used,
                (float) $fund->returned_amount,
                $remaining,
            ]], null, 'A' . $row);
            $row++;
        }

        $mealSheet = $spreadsheet->createSheet();
        $mealSheet->setTitle('Makan Karyawan');
        $mealSheet->fromArray(
            [[
                'Waktu',
                'Karyawan',
                'Kasir Input',
                'Nilai Menu',
                'COGS Aktual',
                'Beban Konsumsi',
                'Lebih Jatah',
                'Payroll',
                'Catatan',
            ]],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['employeeMealRows'] as $r) {
            $mealSheet->fromArray([[
                optional($r->consumed_at)->format('Y-m-d H:i:s'),
                optional($r->employee)->name,
                optional($r->cashier)->name,
                (float) $r->total_amount,
                (float) $r->cogs_total,
                (float) $r->expense_cogs_total,
                (float) $r->excess_amount,
                optional($r->payroll?->period_month)->format('Y-m'),
                $r->note,
            ]], null, 'A' . $row);
            $row++;
        }

        $periodSheet = $spreadsheet->createSheet();
        $periodSheet->setTitle('Rekap Harian Bulanan');
        $periodSheet->fromArray(
            [['Tipe', 'Periode', 'Omzet', 'Refund', 'COGS', 'Laba Kotor', 'Pengeluaran Total', 'Kas Penjualan', 'Kas Kecil', 'Beban Makan', 'Laba Bersih', 'Payroll', 'Laba Setelah Payroll']],
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
                $r['direct_expense_total'],
                $r['petty_cash_expense_total'],
                $r['employee_meal_expense_total'],
                $r['net_profit'],
                $r['payroll_total'],
                $r['net_after_payroll'],
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
                $r['direct_expense_total'],
                $r['petty_cash_expense_total'],
                $r['employee_meal_expense_total'],
                $r['net_profit'],
                $r['payroll_total'],
                $r['net_after_payroll'],
            ]], null, 'A' . $row);
            $row++;
        }

        $menuSheet = $spreadsheet->createSheet();
        $menuSheet->setTitle('Analisis Menu');
        $menuSheet->fromArray(
            [['Menu', 'Qty', 'Omzet Kotor', 'Diskon Alokasi', 'Pajak Alokasi', 'Refund Alokasi', 'Omzet Netto', 'COGS Estimasi', 'Profit Estimasi', 'Margin Profit %']],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['menuAnalysisRows'] as $r) {
            $menuSheet->fromArray([[
                $r['menu'],
                $r['qty'],
                $r['gross_omzet'],
                $r['discount_allocated'],
                $r['tax_allocated'],
                $r['refund_allocated'],
                $r['net_omzet'],
                $r['cogs_estimated'],
                $r['profit_estimated'],
                $r['profit_margin_pct'] / 100,
            ]], null, 'A' . $row);
            $row++;
        }

        $payrollSheet = $spreadsheet->createSheet();
        $payrollSheet->setTitle('Ringkasan Payroll');
        $payrollSheet->fromArray(
            [['Periode', 'Petugas', 'Gaji Pokok', 'Lembur', 'Bonus', 'Pot. Manual', 'Pot. Makan', 'Net', 'Status', 'Approver', 'Payer', 'Paid At']],
            null,
            'A1'
        );
        $row = 2;
        foreach ($data['payrollRows'] as $r) {
            $payrollSheet->fromArray([[
                optional($r->period_month)->format('Y-m'),
                $r->employee_display_name,
                (float) $r->base_salary,
                (float) $r->overtime_amount,
                (float) $r->bonus_amount,
                (float) $r->deduction_amount,
                (float) $r->meal_deduction_amount,
                (float) $r->net_amount,
                $r->status,
                optional($r->approver)->name,
                optional($r->payer)->name,
                optional($r->paid_at)->format('Y-m-d H:i:s'),
            ]], null, 'A' . $row);
            $row++;
        }

        foreach ([$summarySheet, $trxSheet, $itemSheet, $expenseSheet, $expenseCategorySheet, $pettyCashSheet, $mealSheet, $payrollSheet, $periodSheet, $menuSheet] as $sheet) {
            foreach (range('A', 'M') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        $this->applyHeaderStyle($summarySheet, 'A1:B29');
        $this->applyHeaderStyle($summarySheet, 'A31:B31');
        $this->applyHeaderStyle($trxSheet, 'A1:L1');
        $this->applyHeaderStyle($itemSheet, 'A1:M1');
        $this->applyHeaderStyle($expenseSheet, 'A1:L1');
        $this->applyHeaderStyle($expenseCategorySheet, 'A1:E1');
        $this->applyHeaderStyle($pettyCashSheet, 'A1:H1');
        $this->applyHeaderStyle($mealSheet, 'A1:I1');
        $this->applyHeaderStyle($payrollSheet, 'A1:L1');
        $this->applyHeaderStyle($periodSheet, 'A1:M1');
        $this->applyHeaderStyle($menuSheet, 'A1:J1');

        $this->applyProfitColorBySign($summarySheet, 'B26'); // Laba Bersih Setelah Payroll
        $this->applyProfitColorBySign($trxSheet, 'K2:K9999'); // Laba Kotor transaksi
        $this->applyProfitColorBySign($itemSheet, 'M2:M9999'); // Profit estimasi item
        $this->applyProfitColorBySign($periodSheet, 'I2:I9999'); // Laba bersih sebelum payroll
        $this->applyProfitColorBySign($periodSheet, 'K2:K9999'); // Laba bersih setelah payroll
        $this->applyProfitColorBySign($menuSheet, 'I2:I9999'); // Profit estimasi menu

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
