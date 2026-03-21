<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\EmployeeMeal;
use App\Models\EmployeeMealLine;
use App\Models\Item;
use App\Models\Product;
use App\Services\ProductConsumptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeMealController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $employeeId = $request->query('employee_id');
        $employeeId = $employeeId !== null && $employeeId !== '' ? (int) $employeeId : null;

        $query = EmployeeMeal::query()
            ->with(['employee', 'cashier', 'payroll', 'lines.product'])
            ->whereDate('consumed_at', '>=', $from)
            ->whereDate('consumed_at', '<=', $to);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if (auth()->user()->hasRole('cashier')) {
            $query->where('cashier_id', auth()->id());
        }

        $meals = (clone $query)
            ->orderByDesc('consumed_at')
            ->paginate(20)
            ->withQueryString();

        $summaryRows = (clone $query)->get();

        $summary = [
            'trx_count' => (int) $summaryRows->count(),
            'meal_count' => (int) $summaryRows->count(),
            'total_amount' => (float) $summaryRows->sum('total_amount'),
            'cogs_total' => (float) $summaryRows->sum('cogs_total'),
            'expense_cogs_total' => (float) $summaryRows->sum('expense_cogs_total'),
            'company_covered_amount' => (float) $summaryRows->sum('company_covered_amount'),
            'excess_amount' => (float) $summaryRows->sum('excess_amount'),
            'pending_payroll_deduction' => (float) $summaryRows->whereNull('payroll_id')->sum('excess_amount'),
        ];

        $employees = Employee::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'meal_allowance_monthly']);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price_default']);

        return view('admin.employee_meals.index', compact(
            'meals',
            'summary',
            'employees',
            'products',
            'from',
            'to',
            'employeeId',
        ));
    }

    public function store(Request $request, ProductConsumptionService $consumptionService)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'consumed_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'exists:products,id'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
        ]);

        $employee = Employee::query()->where('is_active', true)->findOrFail((int) $validated['employee_id']);
        $consumedAt = Carbon::parse($validated['consumed_at']);

        $productIds = collect($validated['lines'])
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values();

        $products = Product::query()
            ->with(['recipe.lines.item'])
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $linePayloads = collect($validated['lines'])
            ->map(function (array $line) use ($products) {
                $product = $products->get((int) $line['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'lines' => 'Produk tidak valid atau tidak aktif.',
                    ]);
                }

                return (object) [
                    'product' => $product,
                    'qty' => (float) $line['qty'],
                    'price' => (float) $product->price_default,
                    'line_total' => (float) $product->price_default * (float) $line['qty'],
                ];
            })
            ->values();

        try {
            $meal = DB::transaction(function () use ($employee, $consumedAt, $validated, $linePayloads, $consumptionService) {
                $meal = EmployeeMeal::create([
                    'employee_id' => $employee->id,
                    'cashier_id' => auth()->id(),
                    'consumed_at' => $consumedAt,
                    'note' => $validated['note'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                $consumption = $consumptionService->consumeProductLines(
                    $linePayloads,
                    'employee_meal',
                    (int) $meal->id,
                    'MEAL #' . $meal->id
                );

                $totalAmount = (float) $linePayloads->sum('line_total');
                $cogsTotal = (float) $consumption['cogs'];

                $usedCoveredAmountBefore = (float) EmployeeMeal::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('consumed_at', '>=', $consumedAt->copy()->startOfMonth()->toDateString())
                    ->whereDate('consumed_at', '<=', $consumedAt->copy()->endOfMonth()->toDateString())
                    ->where('id', '!=', $meal->id)
                    ->sum('company_covered_amount');

                $allowance = $employee->meal_allowance_monthly;
                $remainingAllowance = $allowance === null
                    ? null
                    : max(0, (float) $allowance - $usedCoveredAmountBefore);
                $companyCoveredAmount = $remainingAllowance === null
                    ? $totalAmount
                    : min($totalAmount, $remainingAllowance);
                $excessAmount = max(0, $totalAmount - $companyCoveredAmount);
                $expenseRatio = $totalAmount > 0 ? ($companyCoveredAmount / $totalAmount) : 0;
                $expenseCogsTotal = round($cogsTotal * $expenseRatio, 2);
                $isOverAllowance = $excessAmount > 0.000001;

                foreach ($linePayloads as $line) {
                    EmployeeMealLine::create([
                        'employee_meal_id' => $meal->id,
                        'product_id' => $line->product->id,
                        'qty' => $line->qty,
                        'price' => $line->price,
                        'line_total' => $line->line_total,
                    ]);
                }

                $meal->update([
                    'total_amount' => $totalAmount,
                    'cogs_total' => $cogsTotal,
                    'expense_cogs_total' => $expenseCogsTotal,
                    'company_covered_amount' => $companyCoveredAmount,
                    'excess_amount' => $excessAmount,
                    'is_over_allowance' => $isOverAllowance,
                ]);

                AuditLog::log(auth()->id(), 'EMPLOYEE_MEAL_CREATED', $meal, [
                    'employee_id' => $employee->id,
                    'consumed_at' => $consumedAt->format('Y-m-d H:i:s'),
                    'total_amount' => $totalAmount,
                    'cogs_total' => $cogsTotal,
                    'expense_cogs_total' => $expenseCogsTotal,
                    'company_covered_amount' => $companyCoveredAmount,
                    'excess_amount' => $excessAmount,
                    'used_covered_amount_before' => $usedCoveredAmountBefore,
                    'meal_allowance_monthly' => $allowance,
                    'remaining_allowance_before' => $remainingAllowance,
                    'is_over_allowance' => $isOverAllowance,
                ]);

                return $meal;
            });
        } catch (InsufficientStockException $e) {
            $item = Item::with('baseUnit')->find($e->itemId);
            $unitName = $item?->baseUnit?->name ?? 'unit';
            $itemName = $item?->name ?? ('Item #' . $e->itemId);
            $shortage = number_format((float) $e->shortageBase, 2, ',', '.');

            throw ValidationException::withMessages([
                'stock' => "Stok {$itemName} tidak cukup. Kurang {$shortage} {$unitName}.",
            ]);
        }

        return redirect()
            ->route($this->routePrefix() . '.employee_meals.index')
            ->with('status', 'Transaksi makan karyawan berhasil dicatat.');
    }

    protected function routePrefix(): string
    {
        if (request()->routeIs('manager.*')) {
            return 'manager';
        }

        if (request()->routeIs('cashier.*')) {
            return 'cashier';
        }

        return 'admin';
    }
}
