<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', ''));
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());

        $leaves = LeaveRequest::query()
            ->with(['employee', 'approver'])
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from)
            ->when(in_array($status, ['PENDING', 'APPROVED', 'REJECTED'], true), fn ($query) => $query->where('status', $status))
            ->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.attendance.leaves.index', compact('leaves', 'employees', 'status', 'from', 'to'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', 'in:LEAVE,SICK,PERMISSION'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        LeaveRequest::create($data + ['status' => 'PENDING']);

        return back()->with('status', 'Pengajuan izin/cuti/sakit berhasil dibuat.');
    }

    public function approve(Request $request, LeaveRequest $leave_request)
    {
        $validated = $request->validate([
            'approval_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $leave_request->update([
            'status' => 'APPROVED',
            'approval_note' => $validated['approval_note'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('status', 'Pengajuan disetujui.');
    }

    public function reject(Request $request, LeaveRequest $leave_request)
    {
        $validated = $request->validate([
            'approval_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $leave_request->update([
            'status' => 'REJECTED',
            'approval_note' => $validated['approval_note'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('status', 'Pengajuan ditolak.');
    }
}
