<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CashExpense;
use App\Models\PettyCashFund;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PettyCashFundController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());
        $status = trim((string) $request->query('status', ''));

        $funds = $this->baseQuery($from, $to, $status)
            ->with(['creator', 'closer', 'expenses.expenseCategory'])
            ->withSum(['expenses as approved_used_total' => fn ($query) => $query->where('status', 'APPROVED')], 'amount')
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.petty_cash.index', compact('funds', 'from', 'to', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'opening_balance' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $fund = PettyCashFund::create([
            'name' => $validated['name'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'opening_balance' => $validated['opening_balance'],
            'note' => $validated['note'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'OPEN',
        ]);

        AuditLog::log(auth()->id(), 'PETTY_CASH_CREATED', $fund, [
            'name' => $fund->name,
            'period_start' => $fund->period_start?->format('Y-m-d'),
            'period_end' => $fund->period_end?->format('Y-m-d'),
            'opening_balance' => $fund->opening_balance,
        ]);

        return back()->with('status', 'Kas kecil berhasil dibuka.');
    }

    public function close(Request $request, PettyCashFund $pettyCashFund)
    {
        $validated = $request->validate([
            'returned_amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_if($pettyCashFund->status === 'CLOSED', 422, 'Kas kecil ini sudah ditutup.');

        $pettyCashFund->loadSum(['expenses as approved_used_total' => fn ($query) => $query->where('status', 'APPROVED')], 'amount');
        $maxReturn = max(0, $pettyCashFund->opening_balance - $pettyCashFund->approved_used_total);

        if ((float) $validated['returned_amount'] > $maxReturn) {
            return back()
                ->withErrors(['returned_amount' => 'Nilai pengembalian melebihi sisa kas kecil yang tersedia.'])
                ->withInput();
        }

        $note = trim(implode("\n", array_filter([
            $pettyCashFund->note,
            $validated['note'] ?? null,
        ])));

        $pettyCashFund->update([
            'returned_amount' => $validated['returned_amount'],
            'note' => $note !== '' ? $note : null,
            'status' => 'CLOSED',
            'closed_by' => auth()->id(),
            'closed_at' => now(),
        ]);

        AuditLog::log(auth()->id(), 'PETTY_CASH_CLOSED', $pettyCashFund, [
            'returned_amount' => $pettyCashFund->returned_amount,
            'approved_used_total' => $pettyCashFund->approved_used_total,
            'remaining_balance' => $pettyCashFund->remaining_balance,
        ]);

        return back()->with('status', 'Kas kecil berhasil ditutup.');
    }

    public function export(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->endOfMonth()->toDateString());
        $status = trim((string) $request->query('status', ''));

        $funds = $this->baseQuery($from, $to, $status)
            ->with(['creator', 'closer'])
            ->withSum(['expenses as approved_used_total' => fn ($query) => $query->where('status', 'APPROVED')], 'amount')
            ->orderBy('period_start')
            ->orderBy('id')
            ->get();

        $fundIds = $funds->pluck('id');
        $expenses = CashExpense::query()
            ->with(['cashier', 'approver', 'pettyCashFund', 'expenseCategory'])
            ->whereIn('petty_cash_fund_id', $fundIds)
            ->orderBy('expense_at')
            ->get();

        $categorySummaryRows = $expenses
            ->where('status', 'APPROVED')
            ->groupBy(fn ($expense) => $expense->expenseCategory?->name ?: $expense->category ?: 'Tanpa Kategori')
            ->map(function ($rows, $category) {
                return [
                    'category' => $category,
                    'total' => (float) $rows->sum('amount'),
                    'count' => (int) $rows->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $spreadsheet = new Spreadsheet();
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Kas Kecil');
        $summarySheet->fromArray([[
            'Nama Dana',
            'Periode Mulai',
            'Periode Selesai',
            'Status',
            'Dana Awal',
            'Terpakai Approved',
            'Dikembalikan',
            'Saldo',
            'Dibuat Oleh',
            'Ditutup Oleh',
            'Ditutup Pada',
            'Catatan',
        ]], null, 'A1');

        $row = 2;
        foreach ($funds as $fund) {
            $used = (float) ($fund->approved_used_total ?? 0);
            $remaining = (float) $fund->opening_balance - $used - (float) $fund->returned_amount;
            $summarySheet->fromArray([[
                $fund->name,
                optional($fund->period_start)->format('Y-m-d'),
                optional($fund->period_end)->format('Y-m-d'),
                $fund->status,
                (float) $fund->opening_balance,
                $used,
                (float) $fund->returned_amount,
                $remaining,
                optional($fund->creator)->name,
                optional($fund->closer)->name,
                optional($fund->closed_at)->format('Y-m-d H:i:s'),
                $fund->note,
            ]], null, 'A' . $row);
            $row++;
        }

        $expenseSheet = $spreadsheet->createSheet();
        $expenseSheet->setTitle('Detail Pengeluaran');
        $expenseSheet->fromArray([[
            'Nama Dana',
            'Waktu',
            'Kategori',
            'Nominal',
            'Status',
            'Diajukan Oleh',
            'Approver',
            'Waktu Approval',
            'Bukti',
            'Catatan',
            'Catatan Approval',
        ]], null, 'A1');

        $row = 2;
        foreach ($expenses as $expense) {
            $expenseSheet->fromArray([[
                optional($expense->pettyCashFund)->name,
                optional($expense->expense_at)->format('Y-m-d H:i:s'),
                $expense->expenseCategory?->name ?: $expense->category,
                (float) $expense->amount,
                $expense->status,
                optional($expense->cashier)->name,
                optional($expense->approver)->name,
                optional($expense->approved_at)->format('Y-m-d H:i:s'),
                $expense->receipt_path ? asset('storage/' . $expense->receipt_path) : null,
                $expense->note,
                $expense->approval_note,
            ]], null, 'A' . $row);
            $row++;
        }

        $categorySheet = $spreadsheet->createSheet();
        $categorySheet->setTitle('Per Kategori');
        $categorySheet->fromArray([[
            'Kategori',
            'Jumlah Transaksi',
            'Total Approved',
        ]], null, 'A1');

        $row = 2;
        foreach ($categorySummaryRows as $categoryRow) {
            $categorySheet->fromArray([[
                $categoryRow['category'],
                $categoryRow['count'],
                $categoryRow['total'],
            ]], null, 'A' . $row);
            $row++;
        }

        foreach ([$summarySheet, $expenseSheet, $categorySheet] as $sheet) {
            foreach (range('A', 'L') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        $this->applyHeaderStyle($summarySheet, 'A1:L1');
        $this->applyHeaderStyle($expenseSheet, 'A1:K1');
        $this->applyHeaderStyle($categorySheet, 'A1:C1');

        $filename = 'laporan_kas_kecil_' . $from . '_to_' . $to . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function baseQuery(string $from, string $to, string $status)
    {
        $query = PettyCashFund::query()
            ->whereDate('period_start', '<=', $to)
            ->whereDate('period_end', '>=', $from);

        if (in_array($status, ['OPEN', 'CLOSED'], true)) {
            $query->where('status', $status);
        }

        return $query;
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
}
