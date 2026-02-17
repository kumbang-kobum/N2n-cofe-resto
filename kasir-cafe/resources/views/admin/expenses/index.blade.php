@extends('layouts.dashboard')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold">Pengeluaran Harian Kasir</h1>
            <p class="text-sm text-gray-600">Catat pengeluaran operasional yang diambil dari kas.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
        <div class="xl:col-span-1 bg-white border rounded-lg p-4">
            <h2 class="font-semibold mb-3">Input Pengeluaran</h2>
            <form method="POST" action="{{ route(request()->routeIs('cashier.*') ? 'cashier.expenses.store' : (request()->routeIs('manager.*') ? 'manager.expenses.store' : 'admin.expenses.store')) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Tanggal & Jam</label>
                    <input type="datetime-local" name="expense_at" value="{{ old('expense_at', now()->format('Y-m-d\TH:i')) }}" class="w-full border rounded px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Kategori</label>
                    <input type="text" name="category" value="{{ old('category') }}" class="w-full border rounded px-3 py-2 text-sm" placeholder="Contoh: Belanja sayur, parkir, gas" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Nominal (Rp)</label>
                    <input type="number" min="1" step="1" name="amount" value="{{ old('amount') }}" class="w-full border rounded px-3 py-2 text-sm" required>
                </div>
                @if(!auth()->user()->hasRole('cashier'))
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Kasir</label>
                        <select name="cashier_id" class="w-full border rounded px-3 py-2 text-sm">
                            @foreach($cashiers as $cashier)
                                <option value="{{ $cashier->id }}" @selected(old('cashier_id', auth()->id()) == $cashier->id)>{{ $cashier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Catatan</label>
                    <textarea name="note" rows="3" class="w-full border rounded px-3 py-2 text-sm" placeholder="Opsional">{{ old('note') }}</textarea>
                </div>
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded px-3 py-2 font-medium text-sm">Simpan Pengeluaran</button>
            </form>
        </div>

        <div class="xl:col-span-2 bg-white border rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">Daftar Pengeluaran</h2>
                <div class="text-sm text-gray-600">Total periode: <span class="font-semibold">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span></div>
            </div>

            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>
                @if(!auth()->user()->hasRole('cashier'))
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Kasir</label>
                        <select name="cashier_id" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">Semua Kasir</option>
                            @foreach($cashiers as $cashier)
                                <option value="{{ $cashier->id }}" @selected($cashierId === $cashier->id)>{{ $cashier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="PENDING" @selected(($status ?? '') === 'PENDING')>PENDING</option>
                        <option value="APPROVED" @selected(($status ?? '') === 'APPROVED')>APPROVED</option>
                        <option value="REJECTED" @selected(($status ?? '') === 'REJECTED')>REJECTED</option>
                    </select>
                </div>
                <div class="{{ auth()->user()->hasRole('cashier') ? 'md:col-span-2' : '' }}">
                    <label class="block text-xs text-gray-600 mb-1">Cari</label>
                    <input type="text" name="q" value="{{ $search }}" class="w-full border rounded px-3 py-2 text-sm" placeholder="Kategori / catatan">
                </div>
                <div class="flex items-end">
                    <button class="w-full bg-slate-700 hover:bg-slate-800 text-white rounded px-3 py-2 text-sm">Filter</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-2 border-b">Waktu</th>
                            <th class="text-left p-2 border-b">Kategori</th>
                            <th class="text-left p-2 border-b">Kasir</th>
                            <th class="text-left p-2 border-b">Status</th>
                            <th class="text-left p-2 border-b">Approval</th>
                            <th class="text-left p-2 border-b">Catatan</th>
                            <th class="text-right p-2 border-b">Nominal</th>
                            <th class="text-center p-2 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr class="border-b">
                                <td class="p-2">{{ optional($expense->expense_at)->format('d/m/Y H:i') }}</td>
                                <td class="p-2">{{ $expense->category }}</td>
                                <td class="p-2">{{ optional($expense->cashier)->name ?? '-' }}</td>
                                <td class="p-2">
                                    <span class="text-xs px-2 py-1 rounded {{ $expense->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : ($expense->status === 'REJECTED' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $expense->status }}
                                    </span>
                                </td>
                                <td class="p-2 text-xs text-gray-600">
                                    @if($expense->approved_by)
                                        {{ optional($expense->approver)->name ?? '-' }}<br>
                                        {{ optional($expense->approved_at)->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="p-2">{{ $expense->note ?: '-' }}</td>
                                <td class="p-2 text-right font-medium">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                <td class="p-2 text-center">
                                    @if(auth()->user()->hasAnyRole(['admin','manager']))
                                        <div class="flex items-center justify-center gap-2">
                                            @if($expense->status !== 'APPROVED')
                                                <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.expenses.approve' : 'admin.expenses.approve', $expense) }}">
                                                    @csrf
                                                    <button class="text-xs text-emerald-600 hover:underline">Approve</button>
                                                </form>
                                            @endif
                                            @if($expense->status !== 'REJECTED')
                                                <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.expenses.reject' : 'admin.expenses.reject', $expense) }}">
                                                    @csrf
                                                    <button class="text-xs text-amber-600 hover:underline">Reject</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route(request()->routeIs('manager.*') ? 'manager.expenses.destroy' : 'admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-xs text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-gray-500 p-4">Belum ada data pengeluaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
@endsection
