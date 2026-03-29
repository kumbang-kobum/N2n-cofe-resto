@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-semibold">Stock Opname</h1>
        <p class="text-sm text-gray-600">Daftar dokumen opname bahan baku dan status penyesuaiannya.</p>
    </div>

    <a href="{{ route('admin.stock_opname.create') }}"
       class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
        + Buat Opname
    </a>
</div>

@if(session('status'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
        {{ session('status') }}
    </div>
@endif

<div class="rounded-lg border bg-white">
    <div class="flex items-center justify-between border-b px-4 py-3">
        <div>
            <div class="font-semibold">Dokumen Stock Opname</div>
            <div class="text-xs text-gray-500">Pantau draft, dokumen yang sudah diposting, dan nilai estimasi penyesuaian.</div>
        </div>
        <div class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
            {{ $opnames->total() }} dokumen
        </div>
    </div>

    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-3 py-2 text-left sticky top-0 z-10 bg-gray-50">Tanggal</th>
                    <th class="px-3 py-2 text-left sticky top-0 z-10 bg-gray-50">Kode</th>
                    <th class="px-3 py-2 text-left sticky top-0 z-10 bg-gray-50">Status</th>
                    <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50"># Line</th>
                    <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Avg Harga/Base</th>
                    <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Nilai Estimasi</th>
                    <th class="px-3 py-2 text-left sticky top-0 z-10 bg-gray-50">Catatan</th>
                    <th class="px-3 py-2 text-left sticky top-0 z-10 bg-gray-50">Dibuat</th>
                    <th class="px-3 py-2 text-left sticky top-0 z-10 bg-gray-50">Posted</th>
                    <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($opnames as $opname)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $opname->counted_at ? $opname->counted_at->format('d M Y') : '-' }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $opname->code }}</td>
                        <td class="px-3 py-2">
                            @php
                                $badgeClass = match($opname->status) {
                                    'DRAFT'     => 'bg-yellow-100 text-yellow-800',
                                    'POSTED'    => 'bg-green-100 text-green-800',
                                    'CANCELLED' => 'bg-red-100 text-red-800',
                                    default     => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="rounded-full px-2 py-1 text-xs {{ $badgeClass }}">{{ $opname->status }}</span>
                        </td>
                        <td class="px-3 py-2 text-right">{{ $opname->lines_count }}</td>
                        <td class="px-3 py-2 text-right">Rp {{ number_format((float) ($opname->avg_unit_cost_base ?? 0), 2, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right">Rp {{ number_format((float) ($opname->estimated_value_total ?? 0), 2, ',', '.') }}</td>
                        <td class="px-3 py-2 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($opname->note, 50) ?: '-' }}</td>
                        <td class="px-3 py-2 text-xs">
                            {{ optional($opname->creator)->name ?? '-' }}<br>
                            <span class="text-gray-500">{{ $opname->created_at ? $opname->created_at->format('d M Y H:i') : '-' }}</span>
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @if($opname->posted_at)
                                {{ $opname->posted_at->format('d M Y H:i') }}<br>
                                <span class="text-gray-500">{{ optional($opname->poster)->name ?? '-' }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div class="flex flex-wrap justify-end gap-2 text-xs">
                                <a href="{{ route('admin.stock_opname.show', $opname->id) }}" class="rounded border px-2 py-1 hover:bg-gray-50">Detail</a>
                                <a href="{{ route('admin.stock_opname.pdf', $opname->id) }}" target="_blank" class="rounded border px-2 py-1 hover:bg-gray-50">PDF</a>
                                @if($opname->status === 'DRAFT')
                                    <a href="{{ route('admin.stock_opname.edit', $opname->id) }}" class="rounded border px-2 py-1 hover:bg-gray-50">Edit</a>
                                    <form action="{{ route('admin.stock_opname.post', $opname->id) }}" method="POST" onsubmit="return confirm('POST opname ini? Stok akan disesuaikan dan tidak bisa di-undo.');">
                                        @csrf
                                        <button type="submit" class="rounded bg-green-600 px-2 py-1 text-white hover:bg-green-700">POST</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-3 py-4 text-center text-gray-500">Belum ada dokumen stock opname.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $opnames->links() }}
</div>
@endsection
