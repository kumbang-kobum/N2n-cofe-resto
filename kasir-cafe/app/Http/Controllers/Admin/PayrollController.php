<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeMeal;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        $attendanceRecapMap = Attendance::query()
            ->select([
                'employee_id',
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN status IN ('PRESENT','LATE') THEN 1 ELSE 0 END) as total_present"),
                DB::raw("SUM(CASE WHEN status = 'LATE' THEN 1 ELSE 0 END) as total_late"),
                DB::raw("SUM(CASE WHEN status = 'INCOMPLETE' THEN 1 ELSE 0 END) as total_incomplete"),
                DB::raw("SUM(CASE WHEN verification_status = 'REVIEW_REQUIRED' THEN 1 ELSE 0 END) as total_review_required"),
                DB::raw("SUM(CASE WHEN verification_status = 'FACE_VERIFIED' THEN 1 ELSE 0 END) as total_face_verified"),
                DB::raw('SUM(overtime_minutes) as overtime_minutes'),
                DB::raw('SUM(late_deduction_amount) as late_deduction_amount'),
            ])
            ->whereDate('attendance_date', '>=', $period . '-01')
            ->whereDate('attendance_date', '<=', date('Y-m-t', strtotime($period . '-01')))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $leaveRecapMap = $this->leaveRecapForEmployees($employees->pluck('id')->all(), $period . '-01');

        $attendanceRecaps = $employees->map(function (Employee $employee) use ($attendanceRecapMap) {
            $recap = $attendanceRecapMap->get($employee->id);

            return [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'total_records' => (int) ($recap->total_records ?? 0),
                'total_present' => (int) ($recap->total_present ?? 0),
                'total_late' => (int) ($recap->total_late ?? 0),
                'total_incomplete' => (int) ($recap->total_incomplete ?? 0),
                'total_review_required' => (int) ($recap->total_review_required ?? 0),
                'total_face_verified' => (int) ($recap->total_face_verified ?? 0),
                'overtime_minutes' => (int) ($recap->overtime_minutes ?? 0),
                'late_deduction_amount' => (float) ($recap->late_deduction_amount ?? 0),
            ];
        })->map(function (array $recap) use ($leaveRecapMap) {
            $leave = $leaveRecapMap[$recap['employee_id']] ?? [
                'leave_days' => 0,
                'sick_days' => 0,
                'permission_days' => 0,
            ];

            return array_merge($recap, $leave);
        })->values();

        return view('admin.payroll.index', compact('payrolls', 'summary', 'period', 'status', 'employees', 'employeeMasterId', 'attendanceRecaps'));
    }

    public function store(Request $request)
    {
        $this->normalizeNumericInputs($request, [
            'base_salary',
            'overtime_amount',
            'bonus_amount',
            'deduction_amount',
        ]);

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
        $lateDeduction = $this->pendingLateDeduction((int) $validated['employee_master_id'], $periodMonth);
        $mealDeduction = $this->pendingMealDeduction((int) $validated['employee_master_id'], $periodMonth);
        $totalDeduction = $manualDeduction + $lateDeduction + $mealDeduction;
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
            'late_deduction_amount' => $lateDeduction,
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

        if ($lateDeduction > 0) {
            Attendance::query()
                ->where('employee_id', (int) $validated['employee_master_id'])
                ->whereNull('payroll_id')
                ->where('late_deduction_amount', '>', 0)
                ->whereDate('attendance_date', '>=', $periodMonth)
                ->whereDate('attendance_date', '<=', date('Y-m-t', strtotime($periodMonth)))
                ->update(['payroll_id' => $payroll->id]);
        }

        AuditLog::log(auth()->id(), 'PAYROLL_CREATED', $payroll, [
            'period_month' => $payroll->period_month?->format('Y-m'),
            'employee_master_id' => $payroll->employee_master_id,
            'net_amount' => $payroll->net_amount,
            'late_deduction_amount' => $payroll->late_deduction_amount,
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
            'late_deduction_amount' => $this->pendingLateDeduction((int) $validated['employee_master_id'], $periodMonth),
            'meal_deduction_amount' => $amount,
            'attendance_summary' => array_merge(
                $this->attendanceRecap((int) $validated['employee_master_id'], $periodMonth),
                $this->leaveRecapForEmployee((int) $validated['employee_master_id'], $periodMonth),
            ),
        ]);
    }

    public function exportAttendanceRecap(Request $request)
    {
        $period = $request->query('period', now()->format('Y-m'));
        $employeeMasterId = $request->query('employee_master_id');
        $employeeMasterId = $employeeMasterId !== null && $employeeMasterId !== '' ? (int) $employeeMasterId : null;

        $employeesQuery = Employee::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($employeeMasterId) {
            $employeesQuery->where('id', $employeeMasterId);
        }

        $employees = $employeesQuery->get(['id', 'name']);

        $attendanceRecapMap = Attendance::query()
            ->select([
                'employee_id',
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN status IN ('PRESENT','LATE') THEN 1 ELSE 0 END) as total_present"),
                DB::raw("SUM(CASE WHEN status = 'LATE' THEN 1 ELSE 0 END) as total_late"),
                DB::raw("SUM(CASE WHEN status = 'INCOMPLETE' THEN 1 ELSE 0 END) as total_incomplete"),
                DB::raw("SUM(CASE WHEN verification_status = 'REVIEW_REQUIRED' THEN 1 ELSE 0 END) as total_review_required"),
                DB::raw("SUM(CASE WHEN verification_status = 'FACE_VERIFIED' THEN 1 ELSE 0 END) as total_face_verified"),
                DB::raw('SUM(overtime_minutes) as overtime_minutes'),
                DB::raw('SUM(late_deduction_amount) as late_deduction_amount'),
            ])
            ->whereDate('attendance_date', '>=', $period . '-01')
            ->whereDate('attendance_date', '<=', date('Y-m-t', strtotime($period . '-01')))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $leaveRecapMap = $this->leaveRecapForEmployees($employees->pluck('id')->all(), $period . '-01');

        $rows = $employees->map(function (Employee $employee) use ($attendanceRecapMap, $leaveRecapMap) {
            $attendance = $attendanceRecapMap->get($employee->id);
            $leave = $leaveRecapMap[$employee->id] ?? ['leave_days' => 0, 'sick_days' => 0, 'permission_days' => 0];

            return [
                'employee_name' => $employee->name,
                'total_records' => (int) ($attendance->total_records ?? 0),
                'total_present' => (int) ($attendance->total_present ?? 0),
                'total_late' => (int) ($attendance->total_late ?? 0),
                'total_incomplete' => (int) ($attendance->total_incomplete ?? 0),
                'overtime_minutes' => (int) ($attendance->overtime_minutes ?? 0),
                'total_face_verified' => (int) ($attendance->total_face_verified ?? 0),
                'total_review_required' => (int) ($attendance->total_review_required ?? 0),
                'leave_days' => (int) ($leave['leave_days'] ?? 0),
                'sick_days' => (int) ($leave['sick_days'] ?? 0),
                'permission_days' => (int) ($leave['permission_days'] ?? 0),
                'late_deduction_amount' => (float) ($attendance->late_deduction_amount ?? 0),
            ];
        });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Absensi Payroll');

        $headers = ['Periode', 'Karyawan', 'Record', 'Hadir', 'Telat', 'Belum Lengkap', 'Lembur (m)', 'Face Verified', 'Perlu Review', 'Cuti', 'Sakit', 'Izin', 'Pot. Telat'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '1', $header);
        }

        $rowNumber = 2;
        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowNumber, $period);
            $sheet->setCellValue('B' . $rowNumber, $row['employee_name']);
            $sheet->setCellValue('C' . $rowNumber, $row['total_records']);
            $sheet->setCellValue('D' . $rowNumber, $row['total_present']);
            $sheet->setCellValue('E' . $rowNumber, $row['total_late']);
            $sheet->setCellValue('F' . $rowNumber, $row['total_incomplete']);
            $sheet->setCellValue('G' . $rowNumber, $row['overtime_minutes']);
            $sheet->setCellValue('H' . $rowNumber, $row['total_face_verified']);
            $sheet->setCellValue('I' . $rowNumber, $row['total_review_required']);
            $sheet->setCellValue('J' . $rowNumber, $row['leave_days']);
            $sheet->setCellValue('K' . $rowNumber, $row['sick_days']);
            $sheet->setCellValue('L' . $rowNumber, $row['permission_days']);
            $sheet->setCellValue('M' . $rowNumber, $row['late_deduction_amount']);
            $rowNumber++;
        }

        $lastRow = max(2, $rowNumber - 1);
        $sheet->getStyle('M2:M' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'rekap_absensi_bulanan_' . $period . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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

        Attendance::query()
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

    protected function pendingLateDeduction(int $employeeMasterId, string $periodMonth): float
    {
        return (float) Attendance::query()
            ->where('employee_id', $employeeMasterId)
            ->whereNull('payroll_id')
            ->where('late_deduction_amount', '>', 0)
            ->whereDate('attendance_date', '>=', $periodMonth)
            ->whereDate('attendance_date', '<=', date('Y-m-t', strtotime($periodMonth)))
            ->sum('late_deduction_amount');
    }

    protected function attendanceRecap(int $employeeMasterId, string $periodMonth): array
    {
        $recap = Attendance::query()
            ->select([
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN status IN ('PRESENT','LATE') THEN 1 ELSE 0 END) as total_present"),
                DB::raw("SUM(CASE WHEN status = 'LATE' THEN 1 ELSE 0 END) as total_late"),
                DB::raw("SUM(CASE WHEN status = 'INCOMPLETE' THEN 1 ELSE 0 END) as total_incomplete"),
                DB::raw("SUM(CASE WHEN verification_status = 'REVIEW_REQUIRED' THEN 1 ELSE 0 END) as total_review_required"),
                DB::raw("SUM(CASE WHEN verification_status = 'FACE_VERIFIED' THEN 1 ELSE 0 END) as total_face_verified"),
                DB::raw('SUM(overtime_minutes) as overtime_minutes'),
                DB::raw('SUM(late_deduction_amount) as late_deduction_amount'),
            ])
            ->where('employee_id', $employeeMasterId)
            ->whereDate('attendance_date', '>=', $periodMonth)
            ->whereDate('attendance_date', '<=', date('Y-m-t', strtotime($periodMonth)))
            ->first();

        return [
            'total_records' => (int) ($recap->total_records ?? 0),
            'total_present' => (int) ($recap->total_present ?? 0),
            'total_late' => (int) ($recap->total_late ?? 0),
            'total_incomplete' => (int) ($recap->total_incomplete ?? 0),
            'total_review_required' => (int) ($recap->total_review_required ?? 0),
            'total_face_verified' => (int) ($recap->total_face_verified ?? 0),
            'overtime_minutes' => (int) ($recap->overtime_minutes ?? 0),
            'late_deduction_amount' => (float) ($recap->late_deduction_amount ?? 0),
        ];
    }

    protected function leaveRecapForEmployee(int $employeeMasterId, string $periodMonth): array
    {
        $map = $this->leaveRecapForEmployees([$employeeMasterId], $periodMonth);

        return $map[$employeeMasterId] ?? [
            'leave_days' => 0,
            'sick_days' => 0,
            'permission_days' => 0,
        ];
    }

    protected function leaveRecapForEmployees(array $employeeIds, string $periodMonth): array
    {
        if (empty($employeeIds)) {
            return [];
        }

        $periodStart = Carbon::parse($periodMonth)->startOfMonth();
        $periodEnd = Carbon::parse($periodMonth)->endOfMonth();

        $requests = LeaveRequest::query()
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'APPROVED')
            ->whereDate('start_date', '<=', $periodEnd->toDateString())
            ->whereDate('end_date', '>=', $periodStart->toDateString())
            ->get(['employee_id', 'type', 'start_date', 'end_date']);

        $result = [];

        foreach ($requests as $request) {
            $start = Carbon::parse($request->start_date)->greaterThan($periodStart)
                ? Carbon::parse($request->start_date)
                : $periodStart->copy();
            $end = Carbon::parse($request->end_date)->lessThan($periodEnd)
                ? Carbon::parse($request->end_date)
                : $periodEnd->copy();

            $days = $start->diffInDays($end) + 1;

            $result[$request->employee_id] ??= [
                'leave_days' => 0,
                'sick_days' => 0,
                'permission_days' => 0,
            ];

            $type = strtoupper((string) $request->type);
            if ($type === 'LEAVE' || $type === 'CUTI') {
                $result[$request->employee_id]['leave_days'] += $days;
            } elseif ($type === 'SICK' || $type === 'SAKIT') {
                $result[$request->employee_id]['sick_days'] += $days;
            } else {
                $result[$request->employee_id]['permission_days'] += $days;
            }
        }

        return $result;
    }
}
