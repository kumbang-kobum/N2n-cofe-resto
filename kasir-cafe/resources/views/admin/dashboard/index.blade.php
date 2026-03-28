@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
  <section class="panel-section">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <div class="section-kicker">Executive Overview</div>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Admin Dashboard</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
          Ringkasan performa penjualan, stok kritis, dan pergerakan menu terlaris untuk periode
          <span class="font-semibold text-slate-700">{{ $from }}</span> sampai
          <span class="font-semibold text-slate-700">{{ $to }}</span>.
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <span class="dashboard-badge">Periode aktif</span>
        <span class="dashboard-badge">{{ $summary['trx'] }} transaksi</span>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="stat-card">
      <div class="stat-label">Omzet</div>
      <div class="stat-value">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</div>
      <div class="stat-meta">{{ $from }} s/d {{ $to }}</div>
    </article>
    <article class="stat-card">
      <div class="stat-label">COGS</div>
      <div class="stat-value">Rp {{ number_format($summary['cogs'], 0, ',', '.') }}</div>
      <div class="stat-meta">Biaya bahan terpakai</div>
    </article>
    <article class="stat-card">
      <div class="stat-label">Profit Kotor</div>
      <div class="stat-value">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</div>
      <div class="stat-meta">Sebelum biaya operasional</div>
    </article>
    <article class="stat-card">
      <div class="stat-label">Transaksi</div>
      <div class="stat-value">{{ number_format($summary['trx'], 0, ',', '.') }}</div>
      <div class="stat-meta">Total bill selesai</div>
    </article>
  </section>

  <section class="grid grid-cols-1 gap-4 xl:grid-cols-12">
    <div class="panel-section border-rose-200 bg-[linear-gradient(135deg,rgba(255,241,242,0.92),rgba(255,255,255,1))] xl:col-span-4">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="section-kicker text-rose-600">Approval Alert</div>
          <h2 class="mt-2 text-xl font-semibold tracking-tight text-slate-950">Pengeluaran Melebihi Limit</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">
            Pengajuan operasional yang masih pending dan butuh perhatian admin karena nominalnya melewati batas kategori.
          </p>
        </div>
        <a href="{{ route('admin.expenses.index', ['status' => 'PENDING', 'limit_status' => 'EXCEEDED']) }}"
           class="dashboard-badge border-rose-200 bg-white text-rose-700">
          Buka daftar
        </a>
      </div>

      <div class="mt-5 grid grid-cols-2 gap-3">
        <div class="rounded-2xl border border-rose-200 bg-white/80 p-4">
          <div class="text-xs font-semibold uppercase tracking-[0.16em] text-rose-500">Pending</div>
          <div class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($pendingExpenseAlerts['over_limit_count'] ?? 0, 0, ',', '.') }}</div>
          <div class="mt-1 text-xs text-slate-500">Pengajuan melewati limit</div>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-white/80 p-4">
          <div class="text-xs font-semibold uppercase tracking-[0.16em] text-rose-500">Nominal</div>
          <div class="mt-2 text-lg font-semibold text-slate-950">Rp {{ number_format($pendingExpenseAlerts['over_limit_amount_total'] ?? 0, 0, ',', '.') }}</div>
          <div class="mt-1 text-xs text-slate-500">Akumulasi yang perlu ditinjau</div>
        </div>
      </div>

      <div class="mt-4 rounded-2xl border border-white/70 bg-white/80 p-4">
        <div class="text-xs text-slate-500">Semua pengajuan pending</div>
        <div class="mt-1 text-sm font-medium text-slate-800">
          {{ number_format($pendingExpenseAlerts['pending_total'] ?? 0, 0, ',', '.') }} pengajuan ·
          Rp {{ number_format($pendingExpenseAlerts['pending_amount_total'] ?? 0, 0, ',', '.') }}
        </div>
      </div>
    </div>

    <div class="table-shell xl:col-span-8">
      <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">
        <div>
          <div class="section-title text-lg">Pending Over-Limit Terbaru</div>
          <p class="muted-copy mt-1">Lima pengajuan terakhir yang melebihi limit approval kategori.</p>
        </div>
        <a href="{{ route('admin.expenses.index', ['status' => 'PENDING', 'limit_status' => 'EXCEEDED']) }}"
           class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
          Lihat semua
        </a>
      </div>
      <div class="overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="table-head">
            <tr>
              <th class="px-4 py-3 text-left">Waktu</th>
              <th class="px-4 py-3 text-left">Pengaju</th>
              <th class="px-4 py-3 text-left">Kategori</th>
              <th class="px-4 py-3 text-right">Limit</th>
              <th class="px-4 py-3 text-right">Nominal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($overLimitExpenseItems as $expense)
              <tr class="hover:bg-slate-50/70">
                <td class="px-4 py-3 text-slate-600">{{ optional($expense->expense_at)->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3 font-medium text-slate-800">{{ optional($expense->cashier)->name ?? '-' }}</td>
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800">{{ $expense->expenseCategory?->name ?: $expense->category }}</div>
                  @if($expense->note)
                    <div class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($expense->note, 80) }}</div>
                  @endif
                </td>
                <td class="px-4 py-3 text-right text-slate-500">
                  Rp {{ number_format($expense->approval_limit_amount_snapshot ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-right font-semibold text-rose-600">
                  Rp {{ number_format($expense->amount, 0, ',', '.') }}
                </td>
              </tr>
            @empty
              <tr>
                <td class="px-4 py-4 text-slate-500" colspan="5">Belum ada pengajuan pending yang melebihi limit. Ini kondisi yang sehat.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">
    <div class="panel-section xl:col-span-8">
      <div class="flex items-start justify-between gap-3 border-b border-slate-200 pb-4">
        <div>
          <div class="section-title">Top 10 Menu Terlaris</div>
          <p class="muted-copy mt-1">Distribusi jumlah item terjual selama periode aktif.</p>
        </div>
        <span class="dashboard-badge">Qty</span>
      </div>
      <div class="mt-5 h-[360px]">
        <canvas id="topProductBarChart"></canvas>
      </div>
    </div>

    <div class="space-y-5 xl:col-span-4">
      <div class="table-shell">
        <div class="border-b border-slate-200 px-5 py-4">
          <div class="section-title text-lg">Stok Menipis</div>
          <p class="muted-copy mt-1">Item bahan yang mendekati batas minimum.</p>
        </div>
        <div class="max-h-[320px] overflow-auto">
          <table class="min-w-full text-sm">
            <thead class="table-head sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left">Bahan</th>
                <th class="px-4 py-3 text-right">Stok</th>
                <th class="px-4 py-3 text-right">Min</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @forelse($lowStock as $it)
                <tr class="hover:bg-slate-50/70">
                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-800">{{ $it->name }}</div>
                    <div class="text-xs text-slate-500">{{ $it->baseUnit->symbol }}</div>
                  </td>
                  <td class="px-4 py-3 text-right text-slate-700">{{ number_format($it->stock_base, 3, ',', '.') }}</td>
                  <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $it->min_stock, 3, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td class="px-4 py-4 text-slate-500" colspan="3">Tidak ada item kritis.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="table-shell">
        <div class="border-b border-slate-200 px-5 py-4">
          <div class="section-title text-lg">Mendekati Expired</div>
          <p class="muted-copy mt-1">Batch dengan masa simpan kurang dari 7 hari.</p>
        </div>
        <div class="max-h-[320px] overflow-auto">
          <table class="min-w-full text-sm">
            <thead class="table-head sticky top-0">
              <tr>
                <th class="px-4 py-3 text-left">Bahan</th>
                <th class="px-4 py-3 text-right">Qty</th>
                <th class="px-4 py-3 text-left">Expired</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @forelse($expSoon as $b)
                <tr class="hover:bg-slate-50/70">
                  <td class="px-4 py-3 font-medium text-slate-800">{{ $b->item->name }}</td>
                  <td class="px-4 py-3 text-right text-slate-700">{{ number_format($b->qty_on_hand_base, 3, ',', '.') }}</td>
                  <td class="px-4 py-3 text-slate-500">{{ \Carbon\Carbon::parse($b->expired_at)->format('d M Y') }}</td>
                </tr>
              @empty
                <tr>
                  <td class="px-4 py-4 text-slate-500" colspan="3">Tidak ada batch mendekati expired.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <section class="table-shell">
    <div class="border-b border-slate-200 px-5 py-4">
      <div class="section-title text-lg">Top 10 Menu Terlaris</div>
      <p class="muted-copy mt-1">Ringkasan qty, omzet, dan jumlah transaksi per menu.</p>
    </div>
    <div class="overflow-auto">
      <table class="min-w-full text-sm">
        <thead class="table-head">
          <tr>
            <th class="px-4 py-3 text-left">Menu</th>
            <th class="px-4 py-3 text-right">Qty</th>
            <th class="px-4 py-3 text-right">Omzet</th>
            <th class="px-4 py-3 text-right">Trx</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($topProducts as $row)
            <tr class="hover:bg-slate-50/70">
              <td class="px-4 py-3 font-medium text-slate-800">{{ $row->product_name }}</td>
              <td class="px-4 py-3 text-right text-slate-700">{{ number_format((float) $row->qty_total, 0, ',', '.') }}</td>
              <td class="px-4 py-3 text-right text-slate-700">Rp {{ number_format((float) $row->omzet_total, 0, ',', '.') }}</td>
              <td class="px-4 py-3 text-right text-slate-500">{{ number_format((float) $row->trx_total, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr>
              <td class="px-4 py-4 text-slate-500" colspan="4">Belum ada data penjualan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const topLabels = @json($topProductChart['labels'] ?? []);
  const topQty = @json($topProductChart['qty'] ?? []);
  const chartEl = document.getElementById('topProductBarChart');

  if (chartEl && topLabels.length > 0) {
    new Chart(chartEl, {
      type: 'bar',
      data: {
        labels: topLabels,
        datasets: [{
          label: 'Qty Terjual',
          data: topQty,
          borderWidth: 0,
          borderRadius: 12,
          backgroundColor: [
            '#2557be',
            '#3269db',
            '#457ae5',
            '#5d8ceb',
            '#75a0f1',
            '#8db4f5',
            '#9fc1f8',
            '#b1cdfa',
            '#c3dafc',
            '#d8e8ff'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#667085', font: { size: 11 } }
          },
          y: {
            beginAtZero: true,
            ticks: { precision: 0, color: '#667085' },
            grid: { color: 'rgba(148, 163, 184, 0.18)' }
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0f172a',
            cornerRadius: 10,
            padding: 12
          }
        }
      }
    });
  }
</script>
@endpush
