@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-semibold">Buat Stock Opname</h1>
        <p class="text-sm text-gray-600">Siapkan dokumen opname dengan pilihan bahan, qty fisik, dan harga estimasi jika ada selisih plus.</p>
    </div>
    <a href="{{ route('admin.stock_opname.index') }}" class="rounded border px-3 py-2 text-sm hover:bg-gray-50">
        Kembali
    </a>
</div>

@if ($errors->any())
    <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <div class="font-semibold mb-1">Gagal simpan:</div>
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('status'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
        {{ session('status') }}
    </div>
@endif

<div class="mb-4 rounded border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
    Untuk opname seluruh bahan dengan data sangat banyak, sistem sekarang sudah dibuat lebih ringan. Jika item atau batch sangat besar, tetap disarankan opname bertahap per kategori agar proses simpan lebih stabil.
</div>

<form method="POST" action="{{ route('admin.stock_opname.store') }}" class="space-y-4">
    @csrf

    <div class="rounded-lg border bg-white p-4 grid md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal Opname</label>
            <input type="date" name="counted_at"
                   value="{{ old('counted_at', now()->format('Y-m-d')) }}"
                   class="w-full rounded border px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Catatan (opsional)</label>
            <textarea name="note" rows="2" class="w-full rounded border px-3 py-2 text-sm">{{ old('note') }}</textarea>
        </div>
    </div>

    <div class="rounded-lg border bg-white overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-3">
            <div>
                <div class="font-semibold">Detail Opname</div>
                <div class="text-xs text-gray-500">Stok sistem diambil dari batch aktif dalam base unit.</div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" id="opname-search" placeholder="Cari item..." class="rounded border px-3 py-2 text-sm w-52">
                <label class="text-xs text-gray-600 flex items-center gap-1">
                    <input type="checkbox" id="opname-check-all" class="rounded border-gray-300">
                    Pilih semua
                </label>
            </div>
        </div>

        <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left p-2 sticky top-0 z-10 bg-gray-50">Pilih</th>
                        <th class="text-left p-2 sticky top-0 z-10 bg-gray-50">Bahan</th>
                        <th class="text-right p-2 sticky top-0 z-10 bg-gray-50">Stok Sistem (base)</th>
                        <th class="text-right p-2 sticky top-0 z-10 bg-gray-50">Qty Fisik</th>
                        <th class="text-left p-2 sticky top-0 z-10 bg-gray-50">Satuan</th>
                        <th class="text-left p-2 sticky top-0 z-10 bg-gray-50">Expired (jika +)</th>
                        <th class="text-right p-2 sticky top-0 z-10 bg-gray-50">Harga / Satuan Input</th>
                    </tr>
                </thead>
                <tbody>
                    @php($oldLines = old('lines', []))

                    @forelse($items as $idx => $item)
                        @php
                            $systemQtyBase = (float) ($systemQtyMap[$item->id] ?? 0);
                            $oldLine = $oldLines[$idx] ?? [];
                        @endphp
                        <tr class="border-t opname-row" data-name="{{ \Illuminate\Support\Str::lower($item->name) }}">
                            <td class="p-2 align-top">
                                <input type="checkbox" name="lines[{{ $idx }}][include]" value="1" @checked(isset($oldLine['include']) && $oldLine['include'])>
                            </td>
                            <td class="p-2 align-top">
                                <div class="font-medium text-slate-800">{{ $item->name }}</div>
                                <div class="text-xs text-gray-500">Base: {{ $item->baseUnit->symbol ?? '-' }}</div>
                                <input type="hidden" name="lines[{{ $idx }}][item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="p-2 text-right align-top">{{ number_format($systemQtyBase, 3, ',', '.') }}</td>
                            <td class="p-2 text-right align-top">
                                <input type="number" step="0.001" min="0" name="lines[{{ $idx }}][physical_qty]" value="{{ $oldLine['physical_qty'] ?? 0 }}" class="w-full rounded border px-2 py-1 text-sm text-right">
                            </td>
                            <td class="p-2 align-top">
                                <select name="lines[{{ $idx }}][unit_id]" class="w-full rounded border px-2 py-1 text-sm">
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" @selected(($oldLine['unit_id'] ?? $item->base_unit_id) == $u->id)>{{ $u->symbol }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-2 align-top">
                                <input type="date" name="lines[{{ $idx }}][expired_at]" value="{{ $oldLine['expired_at'] ?? '' }}" class="w-full rounded border px-2 py-1 text-sm">
                            </td>
                            <td class="p-2 text-right align-top">
                                <input type="number" step="0.01" min="0" name="lines[{{ $idx }}][unit_cost]" value="{{ $oldLine['unit_cost'] ?? '' }}" class="w-full rounded border px-2 py-1 text-sm text-right" placeholder="opsional">
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t">
                            <td colspan="7" class="p-3 text-center text-gray-600">Belum ada item bahan baku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Simpan Dokumen
        </button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const search = document.getElementById('opname-search');
        const rows = Array.from(document.querySelectorAll('.opname-row'));
        const checkAll = document.getElementById('opname-check-all');

        if (search) {
            search.addEventListener('input', function () {
                const q = this.value.toLowerCase();
                rows.forEach(row => {
                    const name = row.dataset.name || '';
                    row.style.display = name.includes(q) ? '' : 'none';
                });
            });
        }

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                const checked = this.checked;
                rows.forEach(row => {
                    if (row.style.display === 'none') return;
                    const cb = row.querySelector('input[type="checkbox"]');
                    if (cb) cb.checked = checked;
                });
            });
        }
    });
</script>
@endpush
@endsection
