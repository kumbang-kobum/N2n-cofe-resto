<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceShift;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $employees = Employee::query()
            ->with(['user', 'defaultShift'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', '%' . $q . '%')
                        ->orWhere('employee_code', 'like', '%' . $q . '%')
                        ->orWhere('position', 'like', '%' . $q . '%')
                        ->orWhere('department', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.employees.index', compact('employees', 'q'));
    }

    public function create()
    {
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $shifts = AttendanceShift::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.employees.create', compact('users', 'shifts'));
    }

    public function store(Request $request)
    {
        $this->normalizeNumericInputs($request, ['meal_allowance_monthly']);

        $data = $request->validate([
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:employees,employee_code'],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'default_shift_id' => ['nullable', 'exists:attendance_shifts,id'],
            'meal_allowance_monthly' => ['nullable', 'numeric', 'min:0'],
            'uses_app' => ['nullable', 'boolean'],
            'user_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
            'face_reference' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $faceReferencePath = null;
        if ($request->hasFile('face_reference')) {
            $faceReferencePath = $request->file('face_reference')->store('employee_faces', 'public');
        }

        Employee::create([
            'employee_code' => $data['employee_code'] ?? null,
            'name' => $data['name'],
            'position' => $data['position'] ?? null,
            'department' => $data['department'] ?? null,
            'default_shift_id' => $data['default_shift_id'] ?? null,
            'face_reference_path' => $faceReferencePath,
            'meal_allowance_monthly' => $data['meal_allowance_monthly'] ?? null,
            'uses_app' => $request->boolean('uses_app'),
            'user_id' => $request->boolean('uses_app') ? ($data['user_id'] ?? null) : null,
            'is_active' => $request->boolean('is_active', true),
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route($this->routePrefix() . '.employees.index')->with('status', 'Master karyawan ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $shifts = AttendanceShift::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.employees.edit', compact('employee', 'users', 'shifts'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->normalizeNumericInputs($request, ['meal_allowance_monthly']);

        $data = $request->validate([
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('employees', 'employee_code')->ignore($employee->id)],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'default_shift_id' => ['nullable', 'exists:attendance_shifts,id'],
            'meal_allowance_monthly' => ['nullable', 'numeric', 'min:0'],
            'uses_app' => ['nullable', 'boolean'],
            'user_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
            'face_reference' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_face_reference' => ['nullable', 'boolean'],
        ]);

        $faceReferencePath = $employee->face_reference_path;
        if ($request->boolean('remove_face_reference') && $faceReferencePath) {
            Storage::disk('public')->delete($faceReferencePath);
            $faceReferencePath = null;
        }
        if ($request->hasFile('face_reference')) {
            if ($faceReferencePath) {
                Storage::disk('public')->delete($faceReferencePath);
            }
            $faceReferencePath = $request->file('face_reference')->store('employee_faces', 'public');
        }

        $employee->update([
            'employee_code' => $data['employee_code'] ?? null,
            'name' => $data['name'],
            'position' => $data['position'] ?? null,
            'department' => $data['department'] ?? null,
            'default_shift_id' => $data['default_shift_id'] ?? null,
            'face_reference_path' => $faceReferencePath,
            'meal_allowance_monthly' => $data['meal_allowance_monthly'] ?? null,
            'uses_app' => $request->boolean('uses_app'),
            'user_id' => $request->boolean('uses_app') ? ($data['user_id'] ?? null) : null,
            'is_active' => $request->boolean('is_active', true),
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route($this->routePrefix() . '.employees.index')->with('status', 'Master karyawan diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route($this->routePrefix() . '.employees.index')->with('status', 'Master karyawan dihapus.');
    }

    protected function routePrefix(): string
    {
        return request()->routeIs('manager.*') ? 'manager' : 'admin';
    }
}
