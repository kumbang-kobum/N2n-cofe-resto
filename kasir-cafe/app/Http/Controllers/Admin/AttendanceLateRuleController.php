<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLateRule;
use Illuminate\Http\Request;

class AttendanceLateRuleController extends Controller
{
    public function index()
    {
        $rules = AttendanceLateRule::query()
            ->orderBy('sort_order')
            ->orderBy('min_minutes')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance.late_rules.index', compact('rules'));
    }

    public function create()
    {
        return view('admin.attendance.late_rules.create', [
            'rule' => new AttendanceLateRule(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function store(Request $request)
    {
        AttendanceLateRule::create($this->validated($request));

        return redirect()->route($this->routePrefix() . '.attendance_late_rules.index')->with('status', 'Rule keterlambatan ditambahkan.');
    }

    public function edit(AttendanceLateRule $attendance_late_rule)
    {
        return view('admin.attendance.late_rules.edit', [
            'rule' => $attendance_late_rule,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function update(Request $request, AttendanceLateRule $attendance_late_rule)
    {
        $attendance_late_rule->update($this->validated($request));

        return redirect()->route($this->routePrefix() . '.attendance_late_rules.index')->with('status', 'Rule keterlambatan diperbarui.');
    }

    public function destroy(AttendanceLateRule $attendance_late_rule)
    {
        $attendance_late_rule->delete();

        return redirect()->route($this->routePrefix() . '.attendance_late_rules.index')->with('status', 'Rule keterlambatan dihapus.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'min_minutes' => ['required', 'integer', 'min:1'],
            'max_minutes' => ['nullable', 'integer', 'min:1'],
            'deduction_amount' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    protected function routePrefix(): string
    {
        return request()->routeIs('manager.*') ? 'manager' : 'admin';
    }
}
