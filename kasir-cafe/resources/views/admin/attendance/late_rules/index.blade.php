@extends('layouts.dashboard')
@section('content')
@php
    $routePrefix = request()->routeIs('manager.*') ? 'manager' : 'admin';
@endphp

<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold text-slate-900">Rule Keterlambatan</h1>
        <p class="text-sm text-slate-600">Atur potongan telat yang fleksibel sesuai keputusan perusahaan.</p>
    </div>
    <a href="{{ route($routePrefix . '.attendance_late_rules.create') }}" class="btn-primary">+ Tambah Rule</a>
</div>
@if(session('status'))
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
@endif

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
    <div class="stat-card">
        <div class="stat-label">Total Rule</div>
        <div class="stat-value">{{ number_format($rules->total(), 0, ',', '.') }}</div>
        <div class="stat-meta">Rule aktif dan nonaktif.</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Rule Aktif</div>
        <div class="stat-value">{{ number_format($rules->getCollection()->where('is_active', true)->count(), 0, ',', '.') }}</div>
        <div class="stat-meta">Dipakai saat hitung potongan telat.</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Urutan</div>
        <div class="stat-value">{{ number_format($rules->getCollection()->max('sort_order') ?? 0, 0, ',', '.') }}</div>
        <div class="stat-meta">Rule dibaca dari urutan paling kecil.</div>
    </div>
</div>

<div class="table-shell">
    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
        <table class="w-full text-sm">
            <thead class="table-head">
                <tr>
                    <th class="px-3 py-2 text-left">Rule</th>
                    <th class="px-3 py-2 text-left">Range</th>
                    <th class="px-3 py-2 text-right">Potongan</th>
                    <th class="px-3 py-2 text-right">Urutan</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                    <tr class="border-t">
                        <td class="px-3 py-2 font-medium text-slate-900">{{ $rule->name }}</td>
                        <td class="px-3 py-2">{{ $rule->min_minutes }} menit - {{ $rule->max_minutes ? $rule->max_minutes . ' menit' : 'seterusnya' }}</td>
                        <td class="px-3 py-2 text-right">Rp {{ number_format($rule->deduction_amount, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right">{{ $rule->sort_order }}</td>
                        <td class="px-3 py-2 text-center"><span class="rounded px-2 py-1 text-xs {{ $rule->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route($routePrefix . '.attendance_late_rules.edit', $rule) }}" class="text-xs font-medium text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route($routePrefix . '.attendance_late_rules.destroy', $rule) }}" class="inline" onsubmit="return confirm('Hapus rule ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 text-xs font-medium text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada rule keterlambatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $rules->links() }}</div>
@endsection
