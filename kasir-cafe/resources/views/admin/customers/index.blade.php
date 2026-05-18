@extends('layouts.dashboard')

@section('content')
    <div class="mb-4">
        <h1 class="text-xl font-semibold">Data Pelanggan</h1>
        <p class="text-sm text-gray-600">Daftar pelanggan yang pernah bertransaksi beserta nomor HP dan riwayat kunjungan.</p>
    </div>

    {{-- Search --}}
    <div class="bg-white border rounded-lg p-4 mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs text-gray-600 mb-1">Cari Nama / No. HP</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Ketik nama atau nomor HP..."
                    class="w-full border rounded px-3 py-2 text-sm"
                >
            </div>
            <button class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium">Cari</button>
            @if($search)
                <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 rounded bg-gray-100 text-gray-700 text-sm font-medium">Reset</a>
            @endif
        </form>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Total Pelanggan Ditemukan</div>
            <div class="text-sm font-semibold">{{ number_format($customers->total(), 0, ',', '.') }} pelanggan</div>
        </div>
        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-gray-500 mb-1">Halaman</div>
            <div class="text-sm font-semibold">{{ $customers->currentPage() }} / {{ $customers->lastPage() }}</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border rounded-lg overflow-hidden">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <div>
                <div class="font-semibold">Daftar Pelanggan</div>
                <div class="text-xs text-gray-500">Diurutkan berdasarkan kunjungan terakhir.</div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left p-3 border-b">No.</th>
                        <th class="text-left p-3 border-b">Nama Pelanggan</th>
                        <th class="text-left p-3 border-b">No. HP</th>
                        <th class="text-right p-3 border-b">Kunjungan</th>
                        <th class="text-right p-3 border-b">Total Belanja</th>
                        <th class="text-right p-3 border-b">Kunjungan Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $i => $c)
                        <tr class="border-b hover:bg-slate-50/60">
                            <td class="p-3 text-gray-400 text-xs">{{ $customers->firstItem() + $i }}</td>
                            <td class="p-3 font-medium text-slate-800">{{ $c->customer_name }}</td>
                            <td class="p-3">
                                @if($c->customer_phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', ltrim($c->customer_phone, '0')) }}" target="_blank" class="text-green-600 hover:underline font-medium">
                                        {{ $c->customer_phone }}
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="p-3 text-right">{{ number_format((int) $c->visit_count, 0, ',', '.') }}x</td>
                            <td class="p-3 text-right">Rp {{ number_format((float) $c->total_spending, 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-gray-600">
                                {{ $c->last_visit ? \Carbon\Carbon::parse($c->last_visit)->translatedFormat('d M Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">
                                @if($search)
                                    Tidak ada pelanggan yang cocok dengan pencarian "<strong>{{ $search }}</strong>".
                                @else
                                    Belum ada data pelanggan.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="px-4 py-3 border-t">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
@endsection
