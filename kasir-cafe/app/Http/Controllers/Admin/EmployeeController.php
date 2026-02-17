<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $employees = Employee::query()
            ->with('user')
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
        return view('admin.employees.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:employees,employee_code'],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'uses_app' => ['nullable', 'boolean'],
            'user_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        Employee::create([
            'employee_code' => $data['employee_code'] ?? null,
            'name' => $data['name'],
            'position' => $data['position'] ?? null,
            'department' => $data['department'] ?? null,
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
        return view('admin.employees.edit', compact('employee', 'users'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('employees', 'employee_code')->ignore($employee->id)],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'uses_app' => ['nullable', 'boolean'],
            'user_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee->update([
            'employee_code' => $data['employee_code'] ?? null,
            'name' => $data['name'],
            'position' => $data['position'] ?? null,
            'department' => $data['department'] ?? null,
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
