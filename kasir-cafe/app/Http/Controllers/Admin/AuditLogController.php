<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $action = $request->query('action', '');

        $query = $this->filteredQuery($from, $to, $action);

        $logs = $query
            ->with(['actor', 'auditable'])
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->toArray();

        return view('admin.reports.audit_logs', compact('logs', 'from', 'to', 'action', 'actions'));
    }

    public function destroy(AuditLog $auditLog)
    {
        $auditLog->delete();

        return back()->with('status', 'Audit log berhasil dihapus.');
    }

    public function destroyFiltered(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $action = $request->input('action', '');

        $deleted = $this->filteredQuery($from, $to, $action)->delete();

        return back()->with('status', $deleted > 0
            ? "Berhasil menghapus {$deleted} audit log."
            : 'Tidak ada audit log yang dihapus.');
    }

    protected function filteredQuery(string $from, string $to, string $action)
    {
        $query = AuditLog::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if ($action !== '') {
            $query->where('action', $action);
        }

        return $query;
    }
}
