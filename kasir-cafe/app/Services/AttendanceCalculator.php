<?php

namespace App\Services;

use App\Models\AttendanceLateRule;
use App\Models\AttendanceShift;
use Carbon\Carbon;

class AttendanceCalculator
{
    public function calculate(?AttendanceShift $shift, ?Carbon $clockInAt, ?Carbon $clockOutAt): array
    {
        $lateMinutes = 0;
        $earlyLeaveMinutes = 0;
        $overtimeMinutes = 0;

        if ($shift && $clockInAt) {
            $shiftStart = Carbon::parse($clockInAt->format('Y-m-d') . ' ' . $shift->start_time);
            $lateMinutes = max(0, $shiftStart->diffInMinutes($clockInAt, false));
            $lateMinutes = max(0, $lateMinutes - (int) $shift->late_tolerance_minutes);
        }

        if ($shift && $clockOutAt) {
            $shiftEnd = Carbon::parse($clockOutAt->format('Y-m-d') . ' ' . $shift->end_time);

            if ($clockOutAt->lt($shiftEnd)) {
                $earlyLeaveMinutes = $clockOutAt->diffInMinutes($shiftEnd);
            }

            $overtimeStart = $shiftEnd->copy()->addMinutes((int) $shift->overtime_after_minutes);
            if ($clockOutAt->gt($overtimeStart)) {
                $overtimeMinutes = $overtimeStart->diffInMinutes($clockOutAt);
            }
        }

        return [
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'late_deduction_amount' => $this->lateDeduction($lateMinutes),
            'status' => $this->resolveStatus($lateMinutes, $clockInAt, $clockOutAt),
        ];
    }

    public function lateDeduction(int $lateMinutes): float
    {
        if ($lateMinutes <= 0) {
            return 0;
        }

        $rule = AttendanceLateRule::query()
            ->where('is_active', true)
            ->where('min_minutes', '<=', $lateMinutes)
            ->where(function ($query) use ($lateMinutes) {
                $query->whereNull('max_minutes')
                    ->orWhere('max_minutes', '>=', $lateMinutes);
            })
            ->orderBy('sort_order')
            ->orderBy('min_minutes')
            ->first();

        return (float) ($rule->deduction_amount ?? 0);
    }

    protected function resolveStatus(int $lateMinutes, ?Carbon $clockInAt, ?Carbon $clockOutAt): string
    {
        if (! $clockInAt && ! $clockOutAt) {
            return 'ABSENT';
        }

        if ($lateMinutes > 0) {
            return 'LATE';
        }

        if ($clockInAt && $clockOutAt) {
            return 'PRESENT';
        }

        return 'INCOMPLETE';
    }
}
