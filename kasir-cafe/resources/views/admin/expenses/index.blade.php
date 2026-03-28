@extends('layouts.dashboard')

@section('content')
    @php
        $storeRoute = route(request()->routeIs('cashier.*') ? 'cashier.expenses.store' : (request()->routeIs('manager.*') ? 'manager.expenses.store' : 'admin.expenses.store'));
        $approveRouteName = request()->routeIs('manager.*') ? 'manager.expenses.approve' : 'admin.expenses.approve';
        $rejectRouteName = request()->routeIs('manager.*') ? 'manager.expenses.reject' : 'admin.expenses.reject';
        $destroyRouteName = request()->routeIs('manager.*') ? 'manager.expenses.destroy' : 'admin.expenses.destroy';
        $currentFundingSource = old('funding_source', $activePettyCashFund ? 'PETTY_CASH' : 'DIRECT_CASH');
        $canApprove = auth()->user()->hasRole('admin');
        $canChooseRequester = auth()->user()->hasRole('admin');
    @endphp

    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Pengeluaran Operasional</h1>
            <p class="text-sm text-slate-600">Manager atau kasir bisa mengajukan pengeluaran beserta bukti belanja. Approval hanya dilakukan admin.</p>
        </div>
        @if(auth()->user()->hasRole('admin'))
            <a href="{{ route('admin.petty_cash.index') }}" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">
                Kelola Kas Kecil
            </a>
        @endif
    </div>

    @if($activePettyCashFund)
        @php
            $activeUsed = (float) ($activePettyCashFund->approved_used_total ?? 0);
            $activeRemaining = (float) $activePettyCashFund->opening_balance - $activeUsed - (float) $activePettyCashFund->returned_amount;
        @endphp
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Kas Kecil Aktif</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ $activePettyCashFund->name }}</div>
                    <div class="text-sm text-slate-600">
                        {{ optional($activePettyCashFund->period_start)->format('d/m/Y') }} - {{ optional($activePettyCashFund->period_end)->format('d/m/Y') }}
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-3">
                    <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
                        <div class="text-slate-500">Dana Awal</div>
                        <div class="mt-1 font-semibold text-slate-900">Rp {{ number_format($activePettyCashFund->opening_balance, 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
                        <div class="text-slate-500">Terpakai Approved</div>
                        <div class="mt-1 font-semibold text-amber-700">Rp {{ number_format($activeUsed, 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-xl bg-white px-4 py-3 shadow-sm">
                        <div class="text-slate-500">Saldo Berjalan</div>
                        <div class="mt-1 font-semibold {{ $activeRemaining >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($activeRemaining, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            Belum ada kas kecil aktif. Pengajuan masih bisa dibuat sebagai pengeluaran langsung dari kas penjualan, tetapi kalau ingin memakai skema dana operasional bulanan, buka dulu menu <span class="font-semibold">Kas Kecil</span>.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-1">
            <h2 class="mb-3 font-semibold text-slate-900">Ajukan Pengeluaran</h2>
            <form method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Tanggal & Jam</label>
                    <input type="datetime-local" name="expense_at" value="{{ old('expense_at', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Kategori</label>
                    <select name="expense_category_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Pilih kategori pengeluaran</option>
                        @foreach($expenseCategories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('expense_category_id') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-1 text-xs text-slate-500">Pilih kategori dari master agar laporan lebih konsisten.</div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Kategori Manual (opsional)</label>
                    <input type="text" name="category" value="{{ old('category') }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Dipakai hanya jika kategori belum ada di master">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Nominal (Rp)</label>
                    <input type="number" min="1" step="1" name="amount" value="{{ old('amount') }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" required>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-medium text-slate-600">Sumber Dana</label>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm">
                            <input type="radio" name="funding_source" value="PETTY_CASH" class="mt-0.5" @checked($currentFundingSource === 'PETTY_CASH')>
                            <span>
                                <span class="font-medium text-slate-900">Kas Kecil</span>
                                <span class="mt-1 block text-xs text-slate-500">Dipakai untuk belanja operasional dari dana bulanan.</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm">
                            <input type="radio" name="funding_source" value="DIRECT_CASH" class="mt-0.5" @checked($currentFundingSource === 'DIRECT_CASH')>
                            <span>
                                <span class="font-medium text-slate-900">Kas Penjualan</span>
                                <span class="mt-1 block text-xs text-slate-500">Dipakai jika pengeluaran memang langsung dari kas harian.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Dana Kas Kecil</label>
                    <select name="petty_cash_fund_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Pilih kas kecil aktif</option>
                        @foreach($openPettyCashFunds as $fund)
                            @php
                                $fundRemaining = (float) $fund->opening_balance - (float) ($fund->approved_used_total ?? 0) - (float) $fund->returned_amount;
                            @endphp
                            <option value="{{ $fund->id }}" @selected((int) old('petty_cash_fund_id', $activePettyCashFund?->id) === $fund->id)>
                                {{ $fund->name }} · Sisa Rp {{ number_format($fundRemaining, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    <div class="mt-1 text-xs text-slate-500">Dipakai jika sumber dana = Kas Kecil.</div>
                </div>

                @if($canChooseRequester)
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Diajukan Oleh</label>
                        <select name="cashier_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @foreach($requesters as $requester)
                                <option value="{{ $requester->id }}" @selected((int) old('cashier_id', auth()->id()) === $requester->id)>{{ $requester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Upload Nota / Bukti Belanja</label>
                    <input type="file" name="receipt" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium">
                    <div class="mt-1 text-xs text-slate-500">Mendukung JPG, PNG, WebP, atau PDF. Maksimal 10 MB.</div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Catatan</label>
                    <textarea name="note" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Contoh: Belanja sayur pagi, beli gas, beli kemasan">{{ old('note') }}</textarea>
                </div>
                <button class="w-full rounded-xl bg-blue-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Kirim Pengajuan</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm xl:col-span-2">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-slate-900">Daftar Pengajuan Pengeluaran</h2>
                    <div class="text-sm text-slate-500">Total periode: <span class="font-semibold text-slate-900">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span></div>
                </div>
            </div>

            <form method="GET" class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-6">
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Dari</label>
                    <input type="date" name="from" value="{{ $from }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Sampai</label>
                    <input type="date" name="to" value="{{ $to }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>
                @if($canChooseRequester)
                    <div>
                        <label class="mb-1 block text-xs text-slate-600">Diajukan Oleh</label>
                        <select name="cashier_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            @foreach($requesters as $requester)
                                <option value="{{ $requester->id }}" @selected($cashierId === $requester->id)>{{ $requester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Status</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="PENDING" @selected(($status ?? '') === 'PENDING')>PENDING</option>
                        <option value="APPROVED" @selected(($status ?? '') === 'APPROVED')>APPROVED</option>
                        <option value="REJECTED" @selected(($status ?? '') === 'REJECTED')>REJECTED</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Sumber Dana</label>
                    <select name="funding_source" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="PETTY_CASH" @selected(($fundingSource ?? '') === 'PETTY_CASH')>Kas Kecil</option>
                        <option value="DIRECT_CASH" @selected(($fundingSource ?? '') === 'DIRECT_CASH')>Kas Penjualan</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Dana Kas Kecil</label>
                    <select name="petty_cash_fund_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Semua Dana</option>
                        @foreach($pettyCashFunds as $fund)
                            <option value="{{ $fund->id }}" @selected($pettyCashFundId === $fund->id)>{{ $fund->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-600">Kontrol Limit</label>
                    <select name="limit_status" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="EXCEEDED" @selected(($limitStatus ?? '') === 'EXCEEDED')>Melebihi Limit</option>
                        <option value="WITHIN_LIMIT" @selected(($limitStatus ?? '') === 'WITHIN_LIMIT')>Dalam Limit</option>
                    </select>
                </div>
                <div class="{{ $canChooseRequester ? 'md:col-span-5' : 'md:col-span-2' }}">
                    <label class="mb-1 block text-xs text-slate-600">Cari</label>
                    <input type="text" name="q" value="{{ $search }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="Kategori / catatan">
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-xl bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900">Filter</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="border-b p-2 text-left">Waktu</th>
                            <th class="border-b p-2 text-left">Kategori</th>
                            <th class="border-b p-2 text-left">Pengaju</th>
                            <th class="border-b p-2 text-left">Sumber</th>
                            <th class="border-b p-2 text-left">Status</th>
                            <th class="border-b p-2 text-left">Approval</th>
                            <th class="border-b p-2 text-left">Bukti</th>
                            <th class="border-b p-2 text-left">Catatan</th>
                            <th class="border-b p-2 text-right">Nominal</th>
                            <th class="border-b p-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr class="border-b border-slate-100 align-top">
                                <td class="p-2">{{ optional($expense->expense_at)->format('d/m/Y H:i') }}</td>
                                <td class="p-2">
                                    <div class="font-medium text-slate-900">{{ $expense->category }}</div>
                                    @if($expense->pettyCashFund)
                                        <div class="mt-1 text-xs text-slate-500">{{ $expense->pettyCashFund->name }}</div>
                                    @endif
                                </td>
                                <td class="p-2">{{ optional($expense->cashier)->name ?? '-' }}</td>
                                <td class="p-2">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $expense->funding_source === 'PETTY_CASH' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $expense->funding_source === 'PETTY_CASH' ? 'Kas Kecil' : 'Kas Penjualan' }}
                                    </span>
                                </td>
                                <td class="p-2">
                                    <div class="flex flex-col gap-1">
                                        @if($expense->exceeds_approval_limit)
                                            <span class="rounded-full bg-rose-100 px-2 py-1 text-[11px] font-semibold text-rose-700">
                                                Melebihi limit
                                                @if($expense->approval_limit_amount_snapshot !== null)
                                                    · Rp {{ number_format($expense->approval_limit_amount_snapshot, 0, ',', '.') }}
                                                @endif
                                            </span>
                                        @elseif($expense->approval_limit_amount_snapshot !== null)
                                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold text-emerald-700">
                                                Dalam limit · Rp {{ number_format($expense->approval_limit_amount_snapshot, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $expense->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-700' : ($expense->status === 'REJECTED' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $expense->status }}
                                    </span>
                                </td>
                                <td class="p-2 text-xs text-slate-500">
                                    @if($expense->approved_by)
                                        <div>{{ optional($expense->approver)->name ?? '-' }}</div>
                                        <div>{{ optional($expense->approved_at)->format('d/m/Y H:i') }}</div>
                                        @if($expense->approval_note)
                                            <div class="mt-1 text-slate-400">{{ $expense->approval_note }}</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="p-2">
                                    @if($expense->receipt_path)
                                        <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="text-xs font-medium text-blue-600 hover:underline">
                                            Lihat Bukti
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="p-2 text-slate-600">{{ $expense->note ?: '-' }}</td>
                                <td class="p-2 text-right font-semibold text-slate-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                <td class="p-2 text-center">
                                    @if($canApprove)
                                        <div class="flex flex-col items-stretch gap-2">
                                            @if($expense->exceeds_approval_limit)
                                                <div class="rounded-lg border border-rose-200 bg-rose-50 p-2 text-left">
                                                    <div class="mb-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-rose-700">Catatan Wajib</div>
                                                    <div class="text-[11px] text-rose-600">Karena pengajuan ini melewati limit, admin wajib mengisi catatan saat approve atau reject.</div>
                                                </div>
                                            @endif

                                            @if($expense->status !== 'APPROVED')
                                                <form method="POST" action="{{ route($approveRouteName, $expense) }}" class="space-y-2">
                                                    @csrf
                                                    <textarea
                                                        name="approval_note"
                                                        rows="{{ $expense->exceeds_approval_limit ? 3 : 2 }}"
                                                        class="w-full rounded-lg border border-slate-200 px-2 py-2 text-xs"
                                                        placeholder="{{ $expense->exceeds_approval_limit ? 'Wajib isi alasan approval untuk nominal di atas limit' : 'Catatan approval (opsional)' }}"
                                                    >{{ old('approval_note') }}</textarea>
                                                    <button class="w-full rounded-lg bg-emerald-600 px-2 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Approve</button>
                                                </form>
                                            @endif

                                            @if($expense->status !== 'REJECTED')
                                                <form method="POST" action="{{ route($rejectRouteName, $expense) }}" class="space-y-2">
                                                    @csrf
                                                    <textarea
                                                        name="approval_note"
                                                        rows="{{ $expense->exceeds_approval_limit ? 3 : 2 }}"
                                                        class="w-full rounded-lg border border-slate-200 px-2 py-2 text-xs"
                                                        placeholder="{{ $expense->exceeds_approval_limit ? 'Wajib isi alasan penolakan untuk nominal di atas limit' : 'Catatan reject (opsional)' }}"
                                                    >{{ old('approval_note') }}</textarea>
                                                    <button class="w-full rounded-lg bg-amber-500 px-2 py-2 text-xs font-semibold text-white hover:bg-amber-600">Reject</button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route($destroyRouteName, $expense) }}" onsubmit="return confirm('Hapus pengajuan pengeluaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="w-full rounded-lg border border-rose-200 px-2 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">Menunggu admin</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-6 text-center text-sm text-slate-500">Belum ada data pengeluaran pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
@endsection
