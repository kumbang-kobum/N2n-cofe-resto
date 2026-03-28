@extends('layouts.dashboard')
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-semibold">Rule Keterlambatan</h1>
        <p class="text-sm text-gray-600">Bisa diubah sewaktu-waktu sesuai keputusan perusahaan.</p>
    </div>
    <a href="{{ route(request()->routeIs('manager.*') ? 'manager.attendance_late_rules.create' : 'admin.attendance_late_rules.create') }}" class="rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Tambah Rule</a>
</div>
@if(session('status'))<div class="mb-3 rounded bg-green-100 px-3 py-2 text-sm text-green-700">{{ session('status') }}</div>@endif
<div class="overflow-x-auto rounded-lg border bg-white">
<table class="w-full text-sm">
<thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-3 py-2 text-left">Rule</th><th class="px-3 py-2 text-left">Range</th><th class="px-3 py-2 text-right">Potongan</th><th class="px-3 py-2 text-center">Status</th><th class="px-3 py-2 text-right">Aksi</th></tr></thead>
<tbody>
@forelse($rules as $rule)
<tr class="border-t">
<td class="px-3 py-2 font-medium">{{ $rule->name }}</td>
<td class="px-3 py-2">{{ $rule->min_minutes }} menit - {{ $rule->max_minutes ? $rule->max_minutes . ' menit' : 'seterusnya' }}</td>
<td class="px-3 py-2 text-right">Rp {{ number_format($rule->deduction_amount, 0, ',', '.') }}</td>
<td class="px-3 py-2 text-center"><span class="rounded px-2 py-1 text-xs {{ $rule->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
<td class="px-3 py-2 text-right"><a href="{{ route(request()->routeIs('manager.*') ? 'manager.attendance_late_rules.edit' : 'admin.attendance_late_rules.edit', $rule) }}" class="text-xs text-blue-600 hover:underline">Edit</a><form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.attendance_late_rules.destroy' : 'admin.attendance_late_rules.destroy', $rule) }}" class="inline" onsubmit="return confirm('Hapus rule ini?')">@csrf @method('DELETE')<button type="submit" class="ml-2 text-xs text-red-600 hover:underline">Hapus</button></form></td>
</tr>
@empty
<tr><td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada rule keterlambatan.</td></tr>
@endforelse
</tbody></table></div>
@endsection
