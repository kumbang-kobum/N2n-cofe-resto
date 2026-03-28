<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceShift;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceScheduleController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $employeeId = $request->query('employee_id');
        $employeeId = $employeeId !== null && $employeeId !== '' ? (int) $employeeId : null;
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $schedules = AttendanceSchedule::query()
            ->with(['employee', 'shift'])
            ->whereDate('schedule_date', '>=', $monthStart->toDateString())
            ->whereDate('schedule_date', '<=', $monthEnd->toDateString())
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->orderBy('employee_id')
            ->orderBy('schedule_date')
            ->get();

        $employees = Employee::query()
            ->with('defaultShift:id,name')
            ->where('is_active', true)
            ->when($employeeId, fn ($query) => $query->where('id', $employeeId))
            ->orderBy('name')
            ->get(['id', 'name', 'default_shift_id']);

        $shifts = AttendanceShift::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'start_time', 'end_time']);
        $days = collect(CarbonPeriod::create($monthStart, $monthEnd))->map(fn (Carbon $date) => $date->copy())->values();

        $scheduleMap = $schedules->keyBy(fn ($schedule) => $schedule->employee_id . '|' . $schedule->schedule_date?->format('Y-m-d'));

        $leaveRequests = LeaveRequest::query()
            ->where('status', 'APPROVED')
            ->whereDate('start_date', '<=', $monthEnd->toDateString())
            ->whereDate('end_date', '>=', $monthStart->toDateString())
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->get();

        $leaveMap = [];
        foreach ($leaveRequests as $leaveRequest) {
            $start = Carbon::parse($leaveRequest->start_date)->greaterThan($monthStart)
                ? Carbon::parse($leaveRequest->start_date)
                : $monthStart->copy();
            $end = Carbon::parse($leaveRequest->end_date)->lessThan($monthEnd)
                ? Carbon::parse($leaveRequest->end_date)
                : $monthEnd->copy();

            foreach (CarbonPeriod::create($start, $end) as $date) {
                $leaveMap[$leaveRequest->employee_id . '|' . $date->format('Y-m-d')] = [
                    'type' => strtoupper((string) $leaveRequest->type),
                    'reason' => $leaveRequest->reason,
                ];
            }
        }

        return view('admin.attendance.schedules.index', compact('schedules', 'employees', 'shifts', 'employeeId', 'month', 'monthStart', 'monthEnd', 'days', 'scheduleMap', 'leaveMap'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'shift_id' => ['nullable', 'exists:attendance_shifts,id'],
            'schedule_date' => ['required', 'date'],
            'is_day_off' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($this->hasApprovedLeave((int) $data['employee_id'], $data['schedule_date'])) {
            return back()->withErrors([
                'schedule_date' => 'Tanggal ini sudah dikunci oleh cuti / sakit / izin yang disetujui.',
            ])->withInput();
        }

        AttendanceSchedule::updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'schedule_date' => $data['schedule_date'],
            ],
            [
                'shift_id' => $request->boolean('is_day_off') ? null : ($data['shift_id'] ?? null),
                'is_day_off' => $request->boolean('is_day_off'),
                'note' => $data['note'] ?? null,
            ]
        );

        return back()->with('status', 'Jadwal kerja tersimpan.');
    }

    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['required', 'exists:employees,id'],
            'shift_id' => ['nullable', 'exists:attendance_shifts,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_day_off' => ['nullable', 'boolean'],
            'overwrite_existing' => ['nullable', 'boolean'],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $employeeIds = collect($data['employee_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->startOfDay();
        $weekdays = collect($data['weekdays'] ?? [1, 2, 3, 4, 5, 6, 7])->map(fn ($day) => (int) $day)->all();
        $isDayOff = $request->boolean('is_day_off');
        $overwrite = $request->boolean('overwrite_existing');

        $saved = 0;
        $skipped = 0;

        foreach ($employeeIds as $employeeId) {
            foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
                if (! in_array($date->dayOfWeekIso, $weekdays, true)) {
                    continue;
                }

                if ($this->hasApprovedLeave($employeeId, $date->toDateString())) {
                    $skipped++;
                    continue;
                }

                $attributes = [
                    'employee_id' => $employeeId,
                    'schedule_date' => $date->toDateString(),
                ];

                if ($overwrite) {
                    AttendanceSchedule::updateOrCreate($attributes, [
                        'shift_id' => $isDayOff ? null : ($data['shift_id'] ?? null),
                        'is_day_off' => $isDayOff,
                        'note' => $data['note'] ?? null,
                    ]);
                    $saved++;
                    continue;
                }

                $existing = AttendanceSchedule::query()
                    ->where($attributes)
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                AttendanceSchedule::create([
                    'employee_id' => $employeeId,
                    'schedule_date' => $date->toDateString(),
                    'shift_id' => $isDayOff ? null : ($data['shift_id'] ?? null),
                    'is_day_off' => $isDayOff,
                    'note' => $data['note'] ?? null,
                ]);
                $saved++;
            }
        }

        $message = $saved . ' jadwal berhasil diterapkan.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' tanggal dilewati karena sudah punya jadwal.';
        }

        return back()->with('status', $message);
    }

    public function copyRoster(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'source_type' => ['required', 'in:previous_week,previous_month'],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['required', 'exists:employees,id'],
            'overwrite_existing' => ['nullable', 'boolean'],
        ]);

        $targetMonthStart = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth();
        $targetMonthEnd = $targetMonthStart->copy()->endOfMonth();
        $overwrite = $request->boolean('overwrite_existing');
        $employeeIds = collect($data['employee_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $sourceSchedules = AttendanceSchedule::query()
            ->when($employeeIds->isNotEmpty(), fn ($query) => $query->whereIn('employee_id', $employeeIds->all()))
            ->where(function ($query) use ($data, $targetMonthStart) {
                if ($data['source_type'] === 'previous_week') {
                    $previousWeekStart = $targetMonthStart->copy()->subWeek()->startOfWeek();
                    $previousWeekEnd = $previousWeekStart->copy()->endOfWeek();
                    $query->whereDate('schedule_date', '>=', $previousWeekStart->toDateString())
                        ->whereDate('schedule_date', '<=', $previousWeekEnd->toDateString());
                    return;
                }

                $previousMonthStart = $targetMonthStart->copy()->subMonthNoOverflow()->startOfMonth();
                $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();
                $query->whereDate('schedule_date', '>=', $previousMonthStart->toDateString())
                    ->whereDate('schedule_date', '<=', $previousMonthEnd->toDateString());
            })
            ->get();

        $saved = 0;
        $skipped = 0;

        foreach (CarbonPeriod::create($targetMonthStart, $targetMonthEnd) as $targetDate) {
            $sourceDate = $data['source_type'] === 'previous_week'
                ? $targetDate->copy()->subWeek()
                : $targetDate->copy()->subMonthNoOverflow();

            foreach ($sourceSchedules->where('schedule_date', $sourceDate) as $sourceSchedule) {
                if ($this->hasApprovedLeave((int) $sourceSchedule->employee_id, $targetDate->toDateString())) {
                    $skipped++;
                    continue;
                }

                $attributes = [
                    'employee_id' => $sourceSchedule->employee_id,
                    'schedule_date' => $targetDate->toDateString(),
                ];

                if ($overwrite) {
                    AttendanceSchedule::updateOrCreate($attributes, [
                        'shift_id' => $sourceSchedule->is_day_off ? null : $sourceSchedule->shift_id,
                        'is_day_off' => (bool) $sourceSchedule->is_day_off,
                        'note' => $sourceSchedule->note,
                    ]);
                    $saved++;
                    continue;
                }

                $existing = AttendanceSchedule::query()->where($attributes)->first();
                if ($existing) {
                    $skipped++;
                    continue;
                }

                AttendanceSchedule::create([
                    'employee_id' => $sourceSchedule->employee_id,
                    'schedule_date' => $targetDate->toDateString(),
                    'shift_id' => $sourceSchedule->is_day_off ? null : $sourceSchedule->shift_id,
                    'is_day_off' => (bool) $sourceSchedule->is_day_off,
                    'note' => $sourceSchedule->note,
                ]);
                $saved++;
            }
        }

        $sourceLabel = $data['source_type'] === 'previous_week' ? 'minggu lalu' : 'bulan lalu';
        $message = "Roster berhasil disalin dari {$sourceLabel}: {$saved} jadwal diterapkan.";
        if ($skipped > 0) {
            $message .= " {$skipped} tanggal dilewati karena sudah ada jadwal atau sedang dikunci leave.";
        }

        return back()->with('status', $message);
    }

    public function destroy(AttendanceSchedule $attendance_schedule)
    {
        if ($this->hasApprovedLeave((int) $attendance_schedule->employee_id, $attendance_schedule->schedule_date?->toDateString())) {
            return back()->withErrors([
                'schedule' => 'Jadwal ini tidak bisa dihapus karena tanggalnya sudah dikunci oleh cuti / sakit / izin yang disetujui.',
            ]);
        }

        $attendance_schedule->delete();

        return back()->with('status', 'Jadwal kerja dihapus.');
    }

    protected function hasApprovedLeave(int $employeeId, string $date): bool
    {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where('status', 'APPROVED')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }
}
