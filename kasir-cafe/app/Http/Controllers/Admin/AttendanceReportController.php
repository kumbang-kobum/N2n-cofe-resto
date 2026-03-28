<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $employeeId = $request->query('employee_id');
        $employeeId = $employeeId !== null && $employeeId !== '' ? (int) $employeeId : null;

        $periodStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $employees = Employee::query()
            ->where('is_active', true)
            ->when($employeeId, fn ($query) => $query->where('id', $employeeId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $attendanceMap = Attendance::query()
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
            ->whereDate('attendance_date', '>=', $periodStart->toDateString())
            ->whereDate('attendance_date', '<=', $periodEnd->toDateString())
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $leaveMap = $this->leaveRecapForEmployees($employees->pluck('id')->all(), $periodStart, $periodEnd);

        $employeeRecaps = $employees->map(function (Employee $employee) use ($attendanceMap, $leaveMap) {
            $attendance = $attendanceMap->get($employee->id);
            $leave = $leaveMap[$employee->id] ?? ['leave_days' => 0, 'sick_days' => 0, 'permission_days' => 0];

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

        $dailyRecaps = Attendance::query()
            ->select([
                DB::raw('DATE(attendance_date) as attendance_day'),
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN status IN ('PRESENT','LATE') THEN 1 ELSE 0 END) as total_present"),
                DB::raw("SUM(CASE WHEN status = 'LATE' THEN 1 ELSE 0 END) as total_late"),
                DB::raw("SUM(CASE WHEN status = 'INCOMPLETE' THEN 1 ELSE 0 END) as total_incomplete"),
                DB::raw('SUM(overtime_minutes) as overtime_minutes'),
                DB::raw('SUM(late_deduction_amount) as late_deduction_amount'),
            ])
            ->whereDate('attendance_date', '>=', $periodStart->toDateString())
            ->whereDate('attendance_date', '<=', $periodEnd->toDateString())
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->groupBy(DB::raw('DATE(attendance_date)'))
            ->orderBy(DB::raw('DATE(attendance_date)'))
            ->get();

        return view('admin.reports.attendance', compact('month', 'employeeId', 'employees', 'employeeRecaps', 'dailyRecaps'));
    }

    public function export(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $employeeId = $request->query('employee_id');
        $employeeId = $employeeId !== null && $employeeId !== '' ? (int) $employeeId : null;

        $periodStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $employees = Employee::query()
            ->where('is_active', true)
            ->when($employeeId, fn ($query) => $query->where('id', $employeeId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $attendanceMap = Attendance::query()
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
            ->whereDate('attendance_date', '>=', $periodStart->toDateString())
            ->whereDate('attendance_date', '<=', $periodEnd->toDateString())
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $leaveMap = $this->leaveRecapForEmployees($employees->pluck('id')->all(), $periodStart, $periodEnd);

        $employeeRecaps = $employees->map(function (Employee $employee) use ($attendanceMap, $leaveMap) {
            $attendance = $attendanceMap->get($employee->id);
            $leave = $leaveMap[$employee->id] ?? ['leave_days' => 0, 'sick_days' => 0, 'permission_days' => 0];

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
        })->values();

        $dailyRecaps = Attendance::query()
            ->select([
                DB::raw('DATE(attendance_date) as attendance_day'),
                DB::raw('COUNT(*) as total_records'),
                DB::raw("SUM(CASE WHEN status IN ('PRESENT','LATE') THEN 1 ELSE 0 END) as total_present"),
                DB::raw("SUM(CASE WHEN status = 'LATE' THEN 1 ELSE 0 END) as total_late"),
                DB::raw("SUM(CASE WHEN status = 'INCOMPLETE' THEN 1 ELSE 0 END) as total_incomplete"),
                DB::raw('SUM(overtime_minutes) as overtime_minutes'),
                DB::raw('SUM(late_deduction_amount) as late_deduction_amount'),
            ])
            ->whereDate('attendance_date', '>=', $periodStart->toDateString())
            ->whereDate('attendance_date', '<=', $periodEnd->toDateString())
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->groupBy(DB::raw('DATE(attendance_date)'))
            ->orderBy(DB::raw('DATE(attendance_date)'))
            ->get();

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Karyawan');
        $headers = ['Periode', 'Karyawan', 'Record', 'Hadir', 'Telat', 'Belum Lengkap', 'Lembur (m)', 'Face Verified', 'Perlu Review', 'Cuti', 'Sakit', 'Izin', 'Pot. Telat'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '1', $header);
        }

        $row = 2;
        foreach ($employeeRecaps as $recap) {
            $sheet->setCellValue('A' . $row, $month);
            $sheet->setCellValue('B' . $row, $recap['employee_name']);
            $sheet->setCellValue('C' . $row, $recap['total_records']);
            $sheet->setCellValue('D' . $row, $recap['total_present']);
            $sheet->setCellValue('E' . $row, $recap['total_late']);
            $sheet->setCellValue('F' . $row, $recap['total_incomplete']);
            $sheet->setCellValue('G' . $row, $recap['overtime_minutes']);
            $sheet->setCellValue('H' . $row, $recap['total_face_verified']);
            $sheet->setCellValue('I' . $row, $recap['total_review_required']);
            $sheet->setCellValue('J' . $row, $recap['leave_days']);
            $sheet->setCellValue('K' . $row, $recap['sick_days']);
            $sheet->setCellValue('L' . $row, $recap['permission_days']);
            $sheet->setCellValue('M' . $row, $recap['late_deduction_amount']);
            $row++;
        }
        $sheet->getStyle('M2:M' . max(2, $row - 1))->getNumberFormat()->setFormatCode('#,##0');
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $dailySheet = $spreadsheet->createSheet();
        $dailySheet->setTitle('Rekap Harian');
        $dailyHeaders = ['Tanggal', 'Record', 'Hadir', 'Telat', 'Belum Lengkap', 'Lembur (m)', 'Pot. Telat'];
        foreach ($dailyHeaders as $index => $header) {
            $dailySheet->setCellValue(chr(65 + $index) . '1', $header);
        }
        $row = 2;
        foreach ($dailyRecaps as $daily) {
            $dailySheet->setCellValue('A' . $row, Carbon::parse($daily->attendance_day)->format('Y-m-d'));
            $dailySheet->setCellValue('B' . $row, $daily->total_records);
            $dailySheet->setCellValue('C' . $row, $daily->total_present);
            $dailySheet->setCellValue('D' . $row, $daily->total_late);
            $dailySheet->setCellValue('E' . $row, $daily->total_incomplete);
            $dailySheet->setCellValue('F' . $row, $daily->overtime_minutes);
            $dailySheet->setCellValue('G' . $row, $daily->late_deduction_amount);
            $row++;
        }
        $dailySheet->getStyle('G2:G' . max(2, $row - 1))->getNumberFormat()->setFormatCode('#,##0');
        foreach (range('A', 'G') as $column) {
            $dailySheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'rekap_absensi_bulanan_laporan_' . $month . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function leaveRecapForEmployees(array $employeeIds, Carbon $periodStart, Carbon $periodEnd): array
    {
        if (empty($employeeIds)) {
            return [];
        }

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
