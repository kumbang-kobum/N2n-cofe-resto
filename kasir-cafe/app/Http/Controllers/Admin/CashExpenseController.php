<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CashExpense;
use App\Models\User;
use Illuminate\Http\Request;

class CashExpenseController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from', now()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $search = trim((string) $request->query('q', ''));
        $cashierId = $request->query('cashier_id');
        $cashierId = $cashierId !== null && $cashierId !== '' ? (int) $cashierId : null;

        if (auth()->user()->hasRole('cashier')) {
            $cashierId = auth()->id();
        }

        $query = CashExpense::query()
            ->with('cashier')
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

        $expenses = (clone $query)
            ->orderByDesc('expense_at')
            ->paginate(20)
            ->withQueryString();

        $totalAmount = (float) (clone $query)->sum('amount');

        $cashiers = User::role('cashier')->orderBy('name')->get();

        return view('admin.expenses.index', compact(
            'expenses',
            'totalAmount',
            'from',
            'to',
            'search',
            'cashiers',
            'cashierId',
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_at' => ['required', 'date'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
            'cashier_id' => ['nullable', 'exists:users,id'],
        ]);

        $cashierId = auth()->user()->hasRole('cashier')
            ? auth()->id()
            : (int) ($validated['cashier_id'] ?? auth()->id());

        $expense = CashExpense::create([
            'expense_at' => $validated['expense_at'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'note' => $validated['note'] ?? null,
            'cashier_id' => $cashierId,
        ]);

        AuditLog::log(auth()->id(), 'CASH_EXPENSE_CREATED', $expense, [
            'expense_at' => $expense->expense_at?->format('Y-m-d H:i:s'),
            'category' => $expense->category,
            'amount' => $expense->amount,
            'cashier_id' => $expense->cashier_id,
        ]);

        return back()->with('status', 'Pengeluaran kas berhasil ditambahkan.');
    }

    public function destroy(CashExpense $cashExpense)
    {
        if (auth()->user()->hasRole('cashier') && $cashExpense->cashier_id !== auth()->id()) {
            abort(403);
        }

        AuditLog::log(auth()->id(), 'CASH_EXPENSE_DELETED', $cashExpense, [
            'expense_at' => $cashExpense->expense_at?->format('Y-m-d H:i:s'),
            'category' => $cashExpense->category,
            'amount' => $cashExpense->amount,
            'cashier_id' => $cashExpense->cashier_id,
        ]);

        $cashExpense->delete();

        return back()->with('status', 'Pengeluaran kas dihapus.');
    }
}
