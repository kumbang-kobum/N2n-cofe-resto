@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Buat Stock Opname</h1>
    <a href="{{ route('admin.stock_opname.index') }}" class="px-3 py-2 border rounded text-sm">
        Kembali
    </a>
</div>

@if ($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm">
        <div class="font-semibold mb-1">Gagal simpan:</div>
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('status'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-sm">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('admin.stock_opname.store') }}">
    @csrf

    <div class="grid md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal Opname</label>
            <input type="date" name="counted_at"
                   value="{{ old('counted_at', now()->format('Y-m-d')) }}"
                   class="w-full border rounded px-2 py-1 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Catatan (opsional)</label>
            <textarea name="note" rows="2"
                      class="w-full border rounded px-2 py-1 text-sm">{{ old('note') }}</textarea>
        </div>
    </div>

    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="p-3 border-b flex items-center justify-between">
            <div class="font-semibold text-sm">Detail Opname</div>
            <div class="text-xs text-gray-500">
                Stok sistem diambil dari batch aktif (base unit).
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-2">Bahan</th>
                        <th class="text-right p-2">Stok Sistem (base)</th>
                        <th class="text-right p-2">Qty Fisik</th>
                        <th class="text-left p-2">Satuan</th>
                        <th class="text-right p-2">Expired (jika +)</th>
                        <th class="text-right p-2">Harga / Satuan Input (opsional)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $oldLines = old('lines', []);
                    @endphp

                    @forelse($items as $idx => $item)
                        @php
                            // stok sistem base (sekadar info di form, boleh simple)
                            $systemQtyBase = \App\Models\ItemBatch::query()
                                ->where('item_id', $item->id)
                                ->where('status', 'ACTIVE')
                                ->sum('qty_on_hand_base');

                            $oldLine = $oldLines[$idx] ?? [];
                        @endphp
                        <tr class="border-t">
                            <td class="p-2 align-top">
                                <div class="font-medium">{{ $item->name }}</div>
                                <div class="text-xs text-gray-500">
                                    Base: {{ $item->baseUnit->symbol ?? '-' }}
                                </div>

                                {{-- kirim item_id --}}
                                <input type="hidden"
                                       name="lines[{{ $idx }}][item_id]"
                                       value="{{ $item->id }}">
                            </td>

                            <td class="p-2 text-right align-top">
                                {{ number_format($systemQtyBase, 3, ',', '.') }}
                            </td>

                            <td class="p-2 text-right align-top">
                                <input type="number" step="0.001" min="0"
                                       name="lines[{{ $idx }}][physical_qty]"
                                       value="{{ $oldLine['physical_qty'] ?? 0 }}"
                                       class="w-full border rounded px-2 py-1 text-sm text-right">
                            </td>

                            <td class="p-2 align-top">
                                <select name="lines[{{ $idx }}][unit_id]"
                                        class="w-full border rounded px-2 py-1 text-sm">
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}"
                                            @selected(($oldLine['unit_id'] ?? $item->base_unit_id) == $u->id)>
                                            {{ $u->symbol }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td class="p-2 text-right align-top">
                                <input type="date"
                                       name="lines[{{ $idx }}][expired_at]"
                                       value="{{ $oldLine['expired_at'] ?? '' }}"
                                       class="border rounded px-2 py-1 text-sm w-full">
                            </td>

                            <td class="p-2 text-right align-top">
                                <input type="number" step="0.01" min="0"
                                       name="lines[{{ $idx }}][unit_cost]"
                                       value="{{ $oldLine['unit_cost'] ?? '' }}"
                                       class="w-full border rounded px-2 py-1 text-sm text-right"
                                       placeholder="harga per satuan input">
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t">
                            <td colspan="6" class="p-3 text-center text-gray-600">
                                Belum ada item bahan baku.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 flex justify-end">
        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            Simpan Dokumen
        </button>
    </div>
</form>
@endsection