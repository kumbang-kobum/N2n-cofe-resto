@extends('layouts.dashboard')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Kas Kecil Operasional</h1>
            <p class="mt-1 text-sm text-slate-600">Buka dana operasional bulanan, pantau pemakaian, lalu tutup dan catat sisa pengembalian di akhir periode.</p>
        </div>
        <a href="{{ route('admin.petty_cash.export', request()->query()) }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Export Excel
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-1">
            <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Buka Kas Kecil</h2>
            <form method="POST" action="{{ route('admin.petty_cash.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Nama / Periode</label>
                    <input type="text" name="name" value="{{ old('name', 'Kas Kecil ' . now()->translatedFormat('F Y')) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Mulai</label>
                        <input type="date" name="period_start" value="{{ old('period_start', now()->startOfMonth()->toDateString()) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Selesai</label>
                        <input type="date" name="period_end" value="{{ old('period_end', now()->endOfMonth()->toDateString()) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Dana Awal (Rp)</label>
                    <input type="number" min="1" step="1" name="opening_balance" value="{{ old('opening_balance', 5000000) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Catatan</label>
                    <textarea name="note" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Opsional">{{ old('note') }}</textarea>
                </div>
                <button class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Simpan Dana Kas Kecil
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Daftar Kas Kecil</h2>
                    <p class="text-sm text-slate-500">Status dana operasional, pemakaian approved, saldo berjalan, dan sisa pengembalian.</p>
                </div>
            </div>

            <form method="GET" class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Dari</label>
                    <input type="date" name="from" value="{{ $from }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Status</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="OPEN" @selected(($status ?? '') === 'OPEN')>OPEN</option>
                        <option value="CLOSED" @selected(($status ?? '') === 'CLOSED')>CLOSED</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-xl bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900">Filter</button>
                </div>
            </form>

            <div class="space-y-4">
                @forelse($funds as $fund)
                    @php
                        $approvedUsed = (float) ($fund->approved_used_total ?? 0);
                        $remaining = (float) $fund->opening_balance - $approvedUsed - (float) $fund->returned_amount;
                    @endphp
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-slate-900">{{ $fund->name }}</h3>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $fund->status === 'OPEN' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $fund->status === 'OPEN' ? 'AKTIF' : 'DITUTUP' }}
                                    </span>
                                </div>
                                <div class="mt-1 text-sm text-slate-500">
                                    {{ optional($fund->period_start)->format('d/m/Y') }} - {{ optional($fund->period_end)->format('d/m/Y') }}
                                    · Dibuat oleh {{ optional($fund->creator)->name ?? '-' }}
                                </div>
                            </div>
                            @if($fund->status === 'OPEN')
                                <form method="POST" action="{{ route('admin.petty_cash.close', $fund) }}" class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    @csrf
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Sisa Dikembalikan</label>
                                        <input type="number" min="0" step="1" name="returned_amount" value="{{ max(0, (int) round($remaining)) }}" class="w-40 rounded-lg border border-slate-200 px-3 py-2 text-sm" required>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Catatan Penutupan</label>
                                        <input type="text" name="note" class="w-56 rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Opsional">
                                    </div>
                                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                                        Tutup Kas Kecil
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <div class="text-xs text-slate-500">Dana Awal</div>
                                <div class="mt-1 text-base font-semibold text-slate-900">Rp {{ number_format($fund->opening_balance, 0, ',', '.') }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <div class="text-xs text-slate-500">Terpakai Approved</div>
                                <div class="mt-1 text-base font-semibold text-amber-700">Rp {{ number_format($approvedUsed, 0, ',', '.') }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <div class="text-xs text-slate-500">Sudah Dikembalikan</div>
                                <div class="mt-1 text-base font-semibold text-blue-700">Rp {{ number_format($fund->returned_amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <div class="text-xs text-slate-500">Saldo Berjalan</div>
                                <div class="mt-1 text-base font-semibold {{ $remaining >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($remaining, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        @if($fund->note)
                            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                {{ $fund->note }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-500">
                        Belum ada dana kas kecil. Buka dana operasional pertama agar pengeluaran manager/kasir bisa memakai kas kecil.
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $funds->links() }}
            </div>
        </div>
    </div>
@endsection
