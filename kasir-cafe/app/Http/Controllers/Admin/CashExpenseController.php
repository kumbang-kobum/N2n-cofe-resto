<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CashExpense;
use App\Models\ExpenseCategory;
use App\Models\PettyCashFund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CashExpenseController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from', now()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $search = trim((string) $request->query('q', ''));
        $cashierId = $request->query('cashier_id');
        $cashierId = $cashierId !== null && $cashierId !== '' ? (int) $cashierId : null;
        $status = trim((string) $request->query('status', ''));
        $fundingSource = trim((string) $request->query('funding_source', ''));
        $pettyCashFundId = $request->query('petty_cash_fund_id');
        $pettyCashFundId = $pettyCashFundId !== null && $pettyCashFundId !== '' ? (int) $pettyCashFundId : null;
        $limitStatus = trim((string) $request->query('limit_status', ''));

        if (auth()->user()->hasAnyRole(['cashier', 'manager'])) {
            $cashierId = auth()->id();
        }

        $query = CashExpense::query()
            ->with(['cashier', 'approver', 'pettyCashFund', 'expenseCategory'])
            ->whereDate('expense_at', '>=', $from)
            ->whereDate('expense_at', '<=', $to);

        if ($cashierId) {
            $query->where('cashier_id', $cashierId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%');
            });
        }

        if (in_array($status, ['PENDING', 'APPROVED', 'REJECTED'], true)) {
            $query->where('status', $status);
        }

        if (in_array($fundingSource, ['DIRECT_CASH', 'PETTY_CASH'], true)) {
            $query->where('funding_source', $fundingSource);
        }

        if ($pettyCashFundId) {
            $query->where('petty_cash_fund_id', $pettyCashFundId);
        }

        if (in_array($limitStatus, ['EXCEEDED', 'WITHIN_LIMIT'], true)) {
            $query->where('exceeds_approval_limit', $limitStatus === 'EXCEEDED');
        }

        $expenses = (clone $query)
            ->orderByDesc('expense_at')
            ->paginate(20)
            ->withQueryString();

        $totalAmount = (float) (clone $query)->sum('amount');

        $requesters = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['cashier', 'manager']))
            ->orderBy('name')
            ->get();

        $pettyCashFunds = PettyCashFund::query()
            ->withSum(['expenses as approved_used_total' => fn ($query) => $query->where('status', 'APPROVED')], 'amount')
            ->orderByDesc('period_start')
            ->get();

        $openPettyCashFunds = $pettyCashFunds->where('status', 'OPEN')->values();
        $activePettyCashFund = $openPettyCashFunds->first();
        $expenseCategories = ExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.expenses.index', compact(
            'expenses',
            'totalAmount',
            'from',
            'to',
            'search',
            'requesters',
            'cashierId',
            'status',
            'fundingSource',
            'pettyCashFundId',
            'pettyCashFunds',
            'openPettyCashFunds',
            'activePettyCashFund',
            'expenseCategories',
            'limitStatus',
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_at' => ['required', 'date'],
            'expense_category_id' => ['nullable', 'exists:expense_categories,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'funding_source' => ['required', 'in:DIRECT_CASH,PETTY_CASH'],
            'note' => ['nullable', 'string', 'max:1000'],
            'cashier_id' => ['nullable', 'exists:users,id'],
            'petty_cash_fund_id' => ['nullable', 'exists:petty_cash_funds,id'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ], [
            'receipt.mimes' => 'Bukti belanja hanya mendukung JPG, PNG, WebP, atau PDF.',
            'receipt.max' => 'Ukuran bukti belanja terlalu besar. Maksimal 10 MB.',
        ]);

        $selectedCategory = null;
        if (! empty($validated['expense_category_id'])) {
            $selectedCategory = ExpenseCategory::find($validated['expense_category_id']);
        }

        $categoryName = $selectedCategory?->name ?? trim((string) ($validated['category'] ?? ''));

        if ($categoryName === '') {
            return back()
                ->withErrors(['expense_category_id' => 'Pilih kategori pengeluaran atau isi kategori manual.'])
                ->withInput();
        }

        $approvalLimitSnapshot = $selectedCategory?->approval_limit_amount;
        $exceedsApprovalLimit = $approvalLimitSnapshot !== null
            ? (float) $validated['amount'] > (float) $approvalLimitSnapshot
            : false;

        $requesterId = auth()->user()->hasAnyRole(['cashier', 'manager'])
            ? auth()->id()
            : (int) ($validated['cashier_id'] ?? auth()->id());

        $fundingSource = $validated['funding_source'];
        $pettyCashFundId = null;

        if ($fundingSource === 'PETTY_CASH') {
            $fund = null;

            if (! empty($validated['petty_cash_fund_id'])) {
                $fund = PettyCashFund::find($validated['petty_cash_fund_id']);
            }

            if (! $fund) {
                $fund = PettyCashFund::query()
                    ->where('status', 'OPEN')
                    ->orderByDesc('period_start')
                    ->first();
            }

            if (! $fund || $fund->status !== 'OPEN') {
                return back()
                    ->withErrors(['petty_cash_fund_id' => 'Tidak ada kas kecil aktif yang bisa dipakai.'])
                    ->withInput();
            }

            $pettyCashFundId = $fund->id;
        }

        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('expense-receipts', 'public')
            : null;

        $expense = CashExpense::create([
            'expense_at' => $validated['expense_at'],
            'category' => $categoryName,
            'expense_category_id' => $selectedCategory?->id,
            'approval_limit_amount_snapshot' => $approvalLimitSnapshot,
            'exceeds_approval_limit' => $exceedsApprovalLimit,
            'amount' => $validated['amount'],
            'funding_source' => $fundingSource,
            'note' => $validated['note'] ?? null,
            'cashier_id' => $requesterId,
            'petty_cash_fund_id' => $pettyCashFundId,
            'receipt_path' => $receiptPath,
            'status' => 'PENDING',
        ]);

        AuditLog::log(auth()->id(), 'CASH_EXPENSE_CREATED', $expense, [
            'expense_at' => $expense->expense_at?->format('Y-m-d H:i:s'),
            'category' => $expense->category,
            'expense_category_id' => $expense->expense_category_id,
            'approval_limit_amount_snapshot' => $expense->approval_limit_amount_snapshot,
            'exceeds_approval_limit' => $expense->exceeds_approval_limit,
            'amount' => $expense->amount,
            'funding_source' => $expense->funding_source,
            'requester_id' => $expense->cashier_id,
            'petty_cash_fund_id' => $expense->petty_cash_fund_id,
            'receipt_uploaded' => (bool) $expense->receipt_path,
        ]);

        return back()->with('status', 'Pengajuan pengeluaran berhasil dikirim dan menunggu approval admin.');
    }

    public function approve(Request $request, CashExpense $cashExpense)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $approvalNoteRules = $cashExpense->exceeds_approval_limit
            ? ['required', 'string', 'max:500']
            : ['nullable', 'string', 'max:500'];

        $validated = $request->validate([
            'approval_note' => $approvalNoteRules,
        ], [
            'approval_note.required' => 'Catatan approval wajib diisi untuk pengajuan yang melebihi limit kategori.',
        ]);

        $cashExpense->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_note' => $validated['approval_note'] ?? null,
        ]);

        AuditLog::log(auth()->id(), 'CASH_EXPENSE_APPROVED', $cashExpense, [
            'status' => 'APPROVED',
            'amount' => $cashExpense->amount,
            'requester_id' => $cashExpense->cashier_id,
            'funding_source' => $cashExpense->funding_source,
            'petty_cash_fund_id' => $cashExpense->petty_cash_fund_id,
        ]);

        return back()->with('status', 'Pengeluaran berhasil di-approve.');
    }

    public function reject(Request $request, CashExpense $cashExpense)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $approvalNoteRules = $cashExpense->exceeds_approval_limit
            ? ['required', 'string', 'max:500']
            : ['nullable', 'string', 'max:500'];

        $validated = $request->validate([
            'approval_note' => $approvalNoteRules,
        ], [
            'approval_note.required' => 'Catatan approval wajib diisi untuk pengajuan yang melebihi limit kategori.',
        ]);

        $cashExpense->update([
            'status' => 'REJECTED',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_note' => $validated['approval_note'] ?? null,
        ]);

        AuditLog::log(auth()->id(), 'CASH_EXPENSE_REJECTED', $cashExpense, [
            'status' => 'REJECTED',
            'amount' => $cashExpense->amount,
            'requester_id' => $cashExpense->cashier_id,
            'funding_source' => $cashExpense->funding_source,
            'petty_cash_fund_id' => $cashExpense->petty_cash_fund_id,
        ]);

        return back()->with('status', 'Pengeluaran ditolak.');
    }

    public function destroy(CashExpense $cashExpense)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        AuditLog::log(auth()->id(), 'CASH_EXPENSE_DELETED', $cashExpense, [
            'expense_at' => $cashExpense->expense_at?->format('Y-m-d H:i:s'),
            'category' => $cashExpense->category,
            'amount' => $cashExpense->amount,
            'requester_id' => $cashExpense->cashier_id,
            'funding_source' => $cashExpense->funding_source,
            'petty_cash_fund_id' => $cashExpense->petty_cash_fund_id,
        ]);

        if ($cashExpense->receipt_path) {
            Storage::disk('public')->delete($cashExpense->receipt_path);
        }

        $cashExpense->delete();

        return back()->with('status', 'Pengeluaran kas dihapus.');
    }
}
