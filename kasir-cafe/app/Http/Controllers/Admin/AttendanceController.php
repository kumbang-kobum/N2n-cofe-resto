<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSchedule;
use App\Models\Employee;
use App\Services\AttendanceCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceCalculator $calculator)
    {
    }

    public function index(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $employeeId = $request->query('employee_id');
        $employeeId = $employeeId !== null && $employeeId !== '' ? (int) $employeeId : null;

        $attendances = Attendance::query()
            ->with(['employee.defaultShift', 'shift', 'payroll'])
            ->whereDate('attendance_date', '>=', $from)
            ->whereDate('attendance_date', '<=', $to)
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->orderByDesc('attendance_date')
            ->orderBy('employee_id')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $summaryQuery = Attendance::query()
            ->whereDate('attendance_date', '>=', $from)
            ->whereDate('attendance_date', '<=', $to)
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId));

        $summary = [
            'present' => (clone $summaryQuery)->where('status', 'PRESENT')->count(),
            'late' => (clone $summaryQuery)->where('status', 'LATE')->count(),
            'incomplete' => (clone $summaryQuery)->where('status', 'INCOMPLETE')->count(),
            'late_deduction_amount' => (float) (clone $summaryQuery)->sum('late_deduction_amount'),
            'overtime_minutes' => (int) (clone $summaryQuery)->sum('overtime_minutes'),
        ];

        return view('admin.attendance.index', compact('attendances', 'employees', 'summary', 'from', 'to', 'employeeId'));
    }

    public function reviewQueue(Request $request)
    {
        $status = $request->query('status', 'REVIEW_REQUIRED');

        $attendances = Attendance::query()
            ->with(['employee.defaultShift', 'shift', 'reviewer'])
            ->where(function ($query) {
                $query->whereNotNull('clock_in_photo_path')
                    ->orWhereNotNull('clock_out_photo_path');
            })
            ->when($status !== 'ALL', fn ($query) => $query->where('verification_status', $status))
            ->orderByDesc('attendance_date')
            ->orderByDesc('updated_at')
            ->paginate(18)
            ->withQueryString();

        return view('admin.attendance.review', compact('attendances', 'status'));
    }

    public function reviewUpdate(Request $request, Attendance $attendance)
    {
        $data = $request->validate([
            'verification_status' => ['required', 'in:PHOTO_ONLY,FACE_VERIFIED,REVIEW_REQUIRED'],
            'verification_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        $attendance->verification_status = $data['verification_status'];
        $attendance->verification_score = isset($data['verification_score']) ? round((float) $data['verification_score'], 2) : $attendance->verification_score;
        $attendance->reviewed_by = auth()->id();
        $attendance->reviewed_at = now();
        $attendance->review_note = $data['review_note'] ?? $attendance->review_note;
        $attendance->note = $this->mergeAttendanceNote(
            null,
            $data['review_note'] ? 'Review: ' . $data['review_note'] : null,
            $attendance->note
        );
        $attendance->save();

        return back()->with('status', 'Status review wajah untuk ' . ($attendance->employee?->name ?? 'karyawan') . ' berhasil diperbarui.');
    }

    public function kiosk()
    {
        return $this->renderKioskView(false);
    }

    public function publicKiosk()
    {
        return $this->renderKioskView(true);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_date' => ['required', 'date'],
            'clock_in_at' => ['nullable', 'date'],
            'clock_out_at' => ['nullable', 'date'],
            'verification_status' => ['nullable', 'in:MANUAL,PHOTO_ONLY,FACE_VERIFIED,REVIEW_REQUIRED'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = Employee::with('defaultShift')->findOrFail((int) $data['employee_id']);
        $schedule = AttendanceSchedule::query()
            ->with('shift')
            ->where('employee_id', $employee->id)
            ->whereDate('schedule_date', $data['attendance_date'])
            ->first();

        $shift = $schedule?->is_day_off ? null : ($schedule?->shift ?? $employee->defaultShift);
        $clockInAt = ! empty($data['clock_in_at']) ? Carbon::parse($data['clock_in_at']) : null;
        $clockOutAt = ! empty($data['clock_out_at']) ? Carbon::parse($data['clock_out_at']) : null;
        $calc = $this->calculator->calculate($shift, $clockInAt, $clockOutAt);

        Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'attendance_date' => $data['attendance_date'],
            ],
            [
                'scheduled_shift_id' => $shift?->id,
                'clock_in_at' => $clockInAt,
                'clock_out_at' => $clockOutAt,
                'status' => $schedule?->is_day_off ? 'DAY_OFF' : $calc['status'],
                'late_minutes' => $calc['late_minutes'],
                'early_leave_minutes' => $calc['early_leave_minutes'],
                'overtime_minutes' => $calc['overtime_minutes'],
                'late_deduction_amount' => $schedule?->is_day_off ? 0 : $calc['late_deduction_amount'],
                'verification_status' => $data['verification_status'] ?? 'MANUAL',
                'note' => $data['note'] ?? null,
            ]
        );

        return back()->with('status', 'Absensi tersimpan.');
    }

    public function kioskStore(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'action_type' => ['required', 'in:CLOCK_IN,CLOCK_OUT'],
            'selfie_image' => ['required', 'string'],
            'verification_status' => ['nullable', 'in:PHOTO_ONLY,FACE_VERIFIED,REVIEW_REQUIRED'],
            'verification_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'verification_note' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = Employee::with('defaultShift')->findOrFail((int) $data['employee_id']);
        $today = now()->toDateString();
        $now = now();
        $schedule = AttendanceSchedule::query()
            ->with('shift')
            ->where('employee_id', $employee->id)
            ->whereDate('schedule_date', $today)
            ->first();

        $shift = $schedule?->is_day_off ? null : ($schedule?->shift ?? $employee->defaultShift);
        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'attendance_date' => $today,
        ]);

        if (! $attendance->exists) {
            $attendance->scheduled_shift_id = $shift?->id;
        }

        if ($data['action_type'] === 'CLOCK_IN') {
            $attendance->clock_in_at = $now;
            $attendance->clock_in_photo_path = $this->storeBase64Image($data['selfie_image'], 'attendance/clock-in');
        } else {
            $attendance->clock_out_at = $now;
            $attendance->clock_out_photo_path = $this->storeBase64Image($data['selfie_image'], 'attendance/clock-out');
        }

        $calc = $this->calculator->calculate(
            $shift,
            $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at) : null,
            $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at) : null,
        );

        $attendance->scheduled_shift_id = $shift?->id;
        $attendance->status = $schedule?->is_day_off ? 'DAY_OFF' : $calc['status'];
        $attendance->late_minutes = $schedule?->is_day_off ? 0 : $calc['late_minutes'];
        $attendance->early_leave_minutes = $schedule?->is_day_off ? 0 : $calc['early_leave_minutes'];
        $attendance->overtime_minutes = $schedule?->is_day_off ? 0 : $calc['overtime_minutes'];
        $attendance->late_deduction_amount = $schedule?->is_day_off ? 0 : $calc['late_deduction_amount'];
        $attendance->verification_status = $data['verification_status'] ?? 'PHOTO_ONLY';
        $attendance->verification_score = isset($data['verification_score']) ? round((float) $data['verification_score'], 2) : null;
        $attendance->note = $this->mergeAttendanceNote(
            $data['note'] ?? null,
            $data['verification_note'] ?? null,
            $attendance->note
        );
        $attendance->save();

        $actionLabel = $data['action_type'] === 'CLOCK_IN' ? 'masuk' : 'pulang';

        return back()->with('status', 'Absensi ' . $actionLabel . ' untuk ' . $employee->name . ' berhasil direkam.');
    }

    protected function renderKioskView(bool $publicMode)
    {
        $employees = Employee::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_code', 'position', 'face_reference_path'])
            ->map(function (Employee $employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_code' => $employee->employee_code,
                    'position' => $employee->position,
                    'face_reference_url' => $employee->face_reference_path
                        ? asset('storage/' . $employee->face_reference_path)
                        : null,
                ];
            });

        $layout = $publicMode ? 'layouts.public-kiosk' : 'layouts.dashboard';
        $storeRoute = $publicMode ? route('attendance.public_kiosk.store') : route('attendance.kiosk.store');

        return view('admin.attendance.kiosk', compact('employees', 'layout', 'publicMode', 'storeRoute'));
    }

    protected function mergeAttendanceNote(?string $note, ?string $verificationNote, ?string $existing): ?string
    {
        $parts = array_filter([
            $note ? trim($note) : null,
            $verificationNote ? trim($verificationNote) : null,
            $existing ? trim($existing) : null,
        ]);

        return empty($parts) ? null : implode(' | ', array_unique($parts));
    }

    protected function storeBase64Image(string $value, string $directory): ?string
    {
        if (! preg_match('/^data:image\\/(png|jpe?g|webp);base64,/', $value, $matches)) {
            return null;
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $decoded = base64_decode(substr($value, strpos($value, ',') + 1), true);

        if ($decoded === false) {
            return null;
        }

        $path = $directory . '/' . now()->format('Y/m') . '/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($path, $decoded);

        return $path;
    }
}
