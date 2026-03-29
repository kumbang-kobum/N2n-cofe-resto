@extends('layouts.dashboard')

@section('content')
@php
    $isManager = request()->routeIs('manager.*');
    $indexRoute = $isManager ? 'manager.reports.audit_logs' : 'admin.reports.audit_logs';
    $destroyRoute = $isManager ? null : 'admin.reports.audit_logs.destroy';
    $destroyFilteredRoute = $isManager ? null : 'admin.reports.audit_logs.destroy_filtered';
@endphp

<div class="mb-4 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">Audit Log</h1>
        <p class="text-sm text-gray-600">Riwayat perubahan sistem, stok, payroll, dan aktivitas penting lainnya.</p>
    </div>
    @can('action.audit_logs.delete')
        <form method="POST" action="{{ route($destroyFilteredRoute) }}" onsubmit="return confirm('Hapus semua audit log sesuai filter saat ini? Tindakan ini tidak bisa dibatalkan.')">
            @csrf
            @method('DELETE')
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">
            <input type="hidden" name="action" value="{{ $action }}">
            <button type="submit" class="inline-flex items-center rounded border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                Hapus Hasil Filter
            </button>
        </form>
    @endcan
</div>

@if (session('status'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('status') }}
    </div>
@endif

<div class="mb-4 rounded-lg border bg-white p-4">
    <form method="GET" action="{{ route($indexRoute) }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $from }}" class="w-full rounded border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ $to }}" class="w-full rounded border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Action</label>
            <select name="action" class="w-full rounded border px-3 py-2 text-sm">
                <option value="">Semua</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" @selected($action === $a)>{{ Illuminate\Support\Str::of($a)->replace('_', ' ')->lower()->title() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="inline-flex flex-1 items-center justify-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Tampilkan
            </button>
            <a href="{{ route($indexRoute) }}" class="inline-flex items-center justify-center rounded border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="rounded-lg border bg-white">
    <div class="border-b px-4 py-3 flex items-center justify-between gap-2">
        <div>
            <div class="font-semibold">Riwayat Audit</div>
            <div class="text-xs text-gray-500">Setiap log menampilkan pelaku, objek, waktu, dan detail perubahan.</div>
        </div>
        <div class="text-xs text-gray-500">{{ $logs->total() }} log</div>
    </div>

    <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
        <div class="divide-y divide-slate-100">
            @forelse($logs as $log)
                <div class="p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $log->actionLabel() }}</span>
                                <span class="text-xs text-gray-500">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</span>
                            </div>
                            <div class="text-sm text-gray-700">{{ $log->summaryText() }}</div>
                            <div class="grid gap-1 text-sm text-gray-700 md:grid-cols-2 md:gap-4">
                                <div><span class="font-medium">Pelaku:</span> {{ $log->actorLabel() }}</div>
                                <div><span class="font-medium">Objek:</span> {{ $log->auditableLabel() }}</div>
                            </div>
                        </div>
                        @can('action.audit_logs.delete')
                            <form method="POST" action="{{ route($destroyRoute, $log) }}" onsubmit="return confirm('Hapus audit log ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center rounded border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-100">
                                    Hapus
                                </button>
                            </form>
                        @endcan
                    </div>

                    @php
                        $metaRows = $log->formattedMeta();
                    @endphp
                    @if ($metaRows)
                        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200">
                            <div class="bg-slate-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Detail Perubahan</div>
                            <div class="divide-y divide-slate-100">
                                @foreach($metaRows as $row)
                                    <div class="grid gap-1 px-3 py-2 md:grid-cols-[220px,1fr] md:gap-3">
                                        <div class="text-xs font-medium text-slate-500">{{ $row['label'] }}</div>
                                        <div class="text-sm text-slate-800 whitespace-pre-wrap break-words">{{ $row['value'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mt-3 text-sm text-gray-500">Tidak ada detail tambahan.</div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-500">Belum ada audit log pada filter ini.</div>
            @endforelse
        </div>
    </div>

    <div class="border-t p-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
