@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-semibold">Stock Opname</h1>
        <p class="text-sm text-gray-600">Daftar dokumen stock opname.</p>
    </div>

    <a href="{{ route('admin.stock_opname.create') }}"
       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded">
        + Buat Opname
    </a>
</div>

@if(session('status'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-sm">
        {{ session('status') }}
    </div>
@endif

<div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">Tanggal</th>
                    <th class="px-3 py-2 text-left">Kode</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-right"># Line</th>
                    <th class="px-3 py-2 text-left">Catatan</th>
                    <th class="px-3 py-2 text-left">Dibuat</th>
                    <th class="px-3 py-2 text-left">Posted</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($opnames as $opname)
                    <tr class="border-t">
                        <td class="px-3 py-2 text-sm">
                            {{ $opname->counted_at ? $opname->counted_at->format('d M Y') : '-' }}
                        </td>
                        <td class="px-3 py-2 text-sm font-mono">
                            {{ $opname->code }}
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @php
                                $badgeClass = match($opname->status) {
                                    'DRAFT'     => 'bg-yellow-100 text-yellow-800',
                                    'POSTED'    => 'bg-green-100 text-green-800',
                                    'CANCELLED' => 'bg-red-100 text-red-800',
                                    default     => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-full {{ $badgeClass }}">
                                {{ $opname->status }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right text-sm">
                            {{ $opname->lines_count }}
                        </td>
                        <td class="px-3 py-2 text-sm truncate max-w-xs">
                            {{ \Illuminate\Support\Str::limit($opname->note, 40) }}
                        </td>
                        <td class="px-3 py-2 text-xs">
                            {{ optional($opname->creator)->name ?? '-' }}<br>
                            <span class="text-gray-500">
                                {{ $opname->created_at ? $opname->created_at->format('d M Y H:i') : '-' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @if($opname->posted_at)
                                {{ $opname->posted_at->format('d M Y H:i') }}<br>
                                <span class="text-gray-500">
                                    {{ optional($opname->poster)->name ?? '-' }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right text-xs space-x-1">
                            <a href="{{ route('admin.stock_opname.show', $opname->id) }}"
                               class="inline-block px-2 py-1 border rounded">
                                Detail
                            </a>

                            <a href="{{ route('admin.stock_opname.pdf', $opname->id) }}"
                               target="_blank"
                               class="inline-block px-2 py-1 border rounded">
                                PDF
                            </a>

                            @if($opname->status === 'DRAFT')
                                <a href="{{ route('admin.stock_opname.edit', $opname->id) }}"
                                   class="inline-block px-2 py-1 border rounded">
                                    Edit
                                </a>

                                <form action="{{ route('admin.stock_opname.post', $opname->id) }}"
                                      method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('POST opname ini? Stok akan disesuaikan dan tidak bisa di-undo.');">
                                    @csrf
                                    <button type="submit"
                                            class="px-2 py-1 rounded bg-green-600 text-white">
                                        POST
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-4 text-center text-gray-500">
                            Belum ada dokumen stock opname.
                        </td>
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