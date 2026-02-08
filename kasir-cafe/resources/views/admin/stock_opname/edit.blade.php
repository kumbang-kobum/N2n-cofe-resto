@extends('layouts.dashboard')

@section('title', 'Edit Stock Opname')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-800">
            Edit Stock Opname
        </h1>

        <a href="{{ route('admin.stock_opname.index') }}"
           class="inline-flex items-center px-3 py-1.5 text-sm border rounded-md text-gray-700 border-gray-300 hover:bg-gray-50">
            &larr; Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
            <div class="font-semibold mb-1">Gagal menyimpan. Periksa kembali input:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-md shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-gray-700 space-y-0.5">
                <div>
                    <span class="font-semibold">Kode:</span>
                    <span class="ml-1 font-mono">{{ $opname->code }}</span>
                </div>
                <div>
                    <span class="font-semibold">Tanggal hitung:</span>
                    <span class="ml-1">
                        {{ optional($opname->counted_at)->format('d/m/Y') }}
                    </span>
                </div>
                <div>
                    <span class="font-semibold">Status:</span>
                    <span class="ml-1">
                        <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full
                            @if($opname->status === 'DRAFT') bg-yellow-50 text-yellow-800 border border-yellow-200
                            @elseif($opname->status === 'POSTED') bg-green-50 text-green-800 border border-green-200
                            @elseif($opname->status === 'CANCELLED') bg-red-50 text-red-800 border border-red-200
                            @else bg-gray-50 text-gray-700 border border-gray-200 @endif">
                            {{ $opname->status }}
                        </span>
                    </span>
                </div>
            </div>

            <div class="text-xs text-gray-500">
                Hanya status <span class="font-semibold">DRAFT</span> yang dapat diedit.
            </div>
        </div>

        <form method="POST" action="{{ route('admin.stock_opname.update', $opname->id) }}">
            @csrf
            @method('PUT') {{-- <== INI YANG NGEBENERIN MethodNotAllowed --}}

            <div class="px-4 py-3 border-b border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="note">
                    Catatan
                </label>
                <textarea id="note"
                          name="note"
                          rows="2"
                          class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Catatan tambahan (opsional)">{{ old('note', $opname->note) }}</textarea>
            </div>

            <div class="px-4 py-3 overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 border-b border-gray-200 text-left font-semibold text-gray-700">
                                Item
                            </th>
                            <th class="px-3 py-2 border-b border-gray-200 text-right font-semibold text-gray-700">
                                Sistem (base)
                            </th>
                            <th class="px-3 py-2 border-b border-gray-200 text-right font-semibold text-gray-700">
                                Fisik (base)
                            </th>
                            <th class="px-3 py-2 border-b border-gray-200 text-right font-semibold text-gray-700">
                                Selisih (base)
                            </th>
                            <th class="px-3 py-2 border-b border-gray-200 text-center font-semibold text-gray-700">
                                Expired <br><span class="text-xs font-normal text-gray-500">(wajib jika selisih +)</span>
                            </th>
                            <th class="px-3 py-2 border-b border-gray-200 text-right font-semibold text-gray-700">
                                Cost base <br><span class="text-xs font-normal text-gray-500">(wajib jika selisih +)</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($opname->lines as $idx => $line)
                            @php
                                $baseUnit = $line->item->baseUnit->symbol ?? '';
                                $diff = $line->diff_qty_base;
                                $diffClass = $diff > 0 ? 'text-green-600' : ($diff < 0 ? 'text-red-600' : 'text-gray-600');
                            @endphp
                            <tr class="hover:bg-gray-50 align-top">
                                <td class="px-3 py-2 border-t border-gray-200">
                                    <input type="hidden"
                                           name="lines[{{ $idx }}][id]"
                                           value="{{ $line->id }}">

                                    <div class="font-semibold text-gray-800">
                                        {{ $line->item->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $line->item->sku ?? '' }}
                                    </div>
                                </td>

                                <td class="px-3 py-2 border-t border-gray-200 text-right text-gray-700">
                                    {{ number_format($line->system_qty_base, 4) }}
                                    <span class="text-xs text-gray-500">{{ $baseUnit }}</span>
                                </td>

                                <td class="px-3 py-2 border-t border-gray-200 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <input
                                            type="number"
                                            step="0.0001"
                                            min="0"
                                            name="lines[{{ $idx }}][physical_qty_base]"
                                            value="{{ old('lines.'.$idx.'.physical_qty_base', $line->physical_qty_base) }}"
                                            class="w-28 text-right border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <span class="text-xs text-gray-500">{{ $baseUnit }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        Ubah jika hasil hitung fisik berubah.
                                    </div>
                                </td>

                                <td class="px-3 py-2 border-t border-gray-200 text-right">
                                    <span class="font-semibold {{ $diffClass }}">
                                        {{ number_format($diff, 4) }}
                                        <span class="text-xs">{{ $baseUnit }}</span>
                                    </span>
                                </td>

                                <td class="px-3 py-2 border-t border-gray-200 text-center">
                                    <input
                                        type="date"
                                        name="lines[{{ $idx }}][expired_at]"
                                        value="{{ old('lines.'.$idx.'.expired_at', optional($line->expired_at)->format('Y-m-d')) }}"
                                        class="w-36 text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        Wajib diisi jika selisih plus.
                                    </div>
                                </td>

                                <td class="px-3 py-2 border-t border-gray-200 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <input
                                            type="number"
                                            step="0.0001"
                                            min="0"
                                            name="lines[{{ $idx }}][unit_cost_base]"
                                            value="{{ old('lines.'.$idx.'.unit_cost_base', $line->unit_cost_base) }}"
                                            class="w-28 text-right border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <span class="text-xs text-gray-500">/ {{ $baseUnit }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        Dipakai untuk membuat batch baru jika selisih plus.
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        @if($opname->lines->isEmpty())
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500 border-t border-gray-200">
                                    Tidak ada detail opname.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('admin.stock_opname.show', $opname->id) }}"
                   class="inline-flex items-center px-3 py-1.5 text-sm border rounded-md text-gray-700 border-gray-300 hover:bg-gray-50">
                    Batal
                </a>

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection