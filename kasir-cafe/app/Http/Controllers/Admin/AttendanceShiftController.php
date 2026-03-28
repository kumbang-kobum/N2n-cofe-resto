<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceShift;
use Illuminate\Http\Request;

class AttendanceShiftController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $shifts = AttendanceShift::query()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%' . $q . '%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance.shifts.index', compact('shifts', 'q'));
    }

    public function create()
    {
        return view('admin.attendance.shifts.create', [
            'shift' => new AttendanceShift(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        AttendanceShift::create($data);

        return redirect()->route($this->routePrefix() . '.attendance_shifts.index')->with('status', 'Shift berhasil ditambahkan.');
    }

    public function edit(AttendanceShift $attendance_shift)
    {
        return view('admin.attendance.shifts.edit', [
            'shift' => $attendance_shift,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function update(Request $request, AttendanceShift $attendance_shift)
    {
        $attendance_shift->update($this->validated($request));

        return redirect()->route($this->routePrefix() . '.attendance_shifts.index')->with('status', 'Shift berhasil diperbarui.');
    }

    public function destroy(AttendanceShift $attendance_shift)
    {
        $attendance_shift->delete();

        return redirect()->route($this->routePrefix() . '.attendance_shifts.index')->with('status', 'Shift berhasil dihapus.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'late_tolerance_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_after_minutes' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['late_tolerance_minutes'] = (int) ($data['late_tolerance_minutes'] ?? 0);
        $data['overtime_after_minutes'] = (int) ($data['overtime_after_minutes'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    protected function routePrefix(): string
    {
        return request()->routeIs('manager.*') ? 'manager' : 'admin';
    }
}
