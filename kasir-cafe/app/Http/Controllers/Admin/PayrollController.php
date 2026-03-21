<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\EmployeeMeal;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', now()->format('Y-m'));
        $status = trim((string) $request->query('status', ''));
        $employeeMasterId = $request->query('employee_master_id');
        $employeeMasterId = $employeeMasterId !== null && $employeeMasterId !== '' ? (int) $employeeMasterId : null;

        $query = Payroll::query()
            ->with(['employee', 'employeeMaster', 'approver', 'payer'])
            ->whereDate('period_month', '>=', $period . '-01')
            ->whereDate('period_month', '<=', $period . '-31');

        if ($employeeMasterId) {
            $query->where('employee_master_id', $employeeMasterId);
        }
        if (in_array($status, ['DRAFT', 'APPROVED', 'PAID'], true)) {
            $query->where('status', $status);
        }

        $payrolls = $query->orderByDesc('period_month')->orderBy('employee_id')->paginate(20)->withQueryString();

        $summary = [
            'draft' => (float) (clone $query)->where('status', 'DRAFT')->sum('net_amount'),
            'approved' => (float) (clone $query)->where('status', 'APPROVED')->sum('net_amount'),
            'paid' => (float) (clone $query)->where('status', 'PAID')->sum('net_amount'),
        ];

        $employees = Employee::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.payroll.index', compact('payrolls', 'summary', 'period', 'status', 'employees', 'employeeMasterId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_month' => ['required', 'date_format:Y-m'],
            'employee_master_id' => ['required', 'exists:employees,id'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'overtime_amount' => ['nullable', 'numeric', 'min:0'],
            'bonus_amount' => ['nullable', 'numeric', 'min:0'],
            'deduction_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $periodMonth = $validated['period_month'] . '-01';

        $exists = Payroll::whereDate('period_month', $periodMonth)
            ->where('employee_master_id', $validated['employee_master_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'employee_master_id' => 'Payroll pegawai untuk bulan ini sudah ada.',
            ])->withInput();
        }

        $base = (float) $validated['base_salary'];
        $overtime = (float) ($validated['overtime_amount'] ?? 0);
        $bonus = (float) ($validated['bonus_amount'] ?? 0);
        $manualDeduction = (float) ($validated['deduction_amount'] ?? 0);
        $mealDeduction = $this->pendingMealDeduction((int) $validated['employee_master_id'], $periodMonth);
        $totalDeduction = $manualDeduction + $mealDeduction;
        $net = $base + $overtime + $bonus - $totalDeduction;
        $employee = Employee::findOrFail((int) $validated['employee_master_id']);

        $payroll = Payroll::create([
            'period_month' => $periodMonth,
            'employee_master_id' => (int) $validated['employee_master_id'],
            'employee_id' => $employee->user_id,
            'base_salary' => $base,
            'overtime_amount' => $overtime,
            'bonus_amount' => $bonus,
            'deduction_amount' => $manualDeduction,
            'meal_deduction_amount' => $mealDeduction,
            'net_amount' => $net,
            'status' => 'DRAFT',
            'note' => $validated['note'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if ($mealDeduction > 0) {
            EmployeeMeal::query()
                ->where('employee_id', (int) $validated['employee_master_id'])
                ->whereNull('payroll_id')
                ->where('excess_amount', '>', 0)
                ->whereDate('consumed_at', '>=', $periodMonth)
                ->whereDate('consumed_at', '<=', date('Y-m-t', strtotime($periodMonth)))
                ->update(['payroll_id' => $payroll->id]);
        }

        AuditLog::log(auth()->id(), 'PAYROLL_CREATED', $payroll, [
            'period_month' => $payroll->period_month?->format('Y-m'),
            'employee_master_id' => $payroll->employee_master_id,
            'net_amount' => $payroll->net_amount,
            'meal_deduction_amount' => $payroll->meal_deduction_amount,
            'status' => $payroll->status,
        ]);

        return back()->with('status', 'Payroll berhasil dibuat.');
    }

    public function mealDeductionPreview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period_month' => ['required', 'date_format:Y-m'],
            'employee_master_id' => ['required', 'exists:employees,id'],
        ]);

        $periodMonth = $validated['period_month'] . '-01';
        $amount = $this->pendingMealDeduction((int) $validated['employee_master_id'], $periodMonth);

        return response()->json([
            'meal_deduction_amount' => $amount,
        ]);
    }

    public function approve(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'approval_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($payroll->status === 'PAID') {
            return back()->withErrors(['status' => 'Payroll yang sudah PAID tidak bisa diubah.']);
        }

        $payroll->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_note' => $validated['approval_note'] ?? null,
        ]);

        AuditLog::log(auth()->id(), 'PAYROLL_APPROVED', $payroll, [
            'status' => 'APPROVED',
            'net_amount' => $payroll->net_amount,
        ]);

        return back()->with('status', 'Payroll di-approve.');
    }

    public function markPaid(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'paid_at' => ['nullable', 'date'],
        ]);

        if (! in_array($payroll->status, ['APPROVED', 'PAID'], true)) {
            return back()->withErrors(['status' => 'Payroll harus APPROVED sebelum dibayar.']);
        }

        $payroll->update([
            'status' => 'PAID',
            'paid_by' => auth()->id(),
            'paid_at' => $validated['paid_at'] ?? now(),
        ]);

        AuditLog::log(auth()->id(), 'PAYROLL_PAID', $payroll, [
            'status' => 'PAID',
            'net_amount' => $payroll->net_amount,
            'paid_at' => optional($payroll->paid_at)->format('Y-m-d H:i:s'),
        ]);

        return back()->with('status', 'Payroll ditandai sudah dibayar.');
    }

    public function slip(Payroll $payroll)
    {
        $payroll->load(['employeeMaster', 'employee', 'approver', 'payer']);

        return view('admin.payroll.slip', compact('payroll'));
    }

    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'PAID') {
            return back()->withErrors(['status' => 'Payroll status PAID tidak boleh dihapus.']);
        }

        AuditLog::log(auth()->id(), 'PAYROLL_DELETED', $payroll, [
            'period_month' => $payroll->period_month?->format('Y-m'),
            'employee_master_id' => $payroll->employee_master_id,
            'net_amount' => $payroll->net_amount,
            'status' => $payroll->status,
        ]);

        EmployeeMeal::query()
            ->where('payroll_id', $payroll->id)
            ->update(['payroll_id' => null]);

        $payroll->delete();

        return back()->with('status', 'Payroll dihapus.');
    }

    protected function pendingMealDeduction(int $employeeMasterId, string $periodMonth): float
    {
        return (float) EmployeeMeal::query()
            ->where('employee_id', $employeeMasterId)
            ->whereNull('payroll_id')
            ->where('excess_amount', '>', 0)
            ->whereDate('consumed_at', '>=', $periodMonth)
            ->whereDate('consumed_at', '<=', date('Y-m-t', strtotime($periodMonth)))
            ->sum('excess_amount');
    }
}
