@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Admin Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">Omzet ({{ $from }} s/d {{ $to }})</div>
    <div class="text-lg font-semibold">Rp {{ number_format($summary['omzet'],0,',','.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">COGS</div>
    <div class="text-lg font-semibold">Rp {{ number_format($summary['cogs'],0,',','.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">Profit Kotor</div>
    <div class="text-lg font-semibold">Rp {{ number_format($summary['profit'],0,',','.') }}</div>
  </div>
  <div class="bg-white border rounded-lg p-4">
    <div class="text-sm text-gray-600">Transaksi</div>
    <div class="text-lg font-semibold">{{ $summary['trx'] }}</div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
  <div class="bg-white border rounded-lg overflow-hidden lg:col-span-2">
    <div class="p-4 border-b">
      <div class="font-semibold">Top 10 Menu Terlaris (Qty)</div>
      <div class="text-xs text-gray-500">Periode {{ $from }} s/d {{ $to }}</div>
    </div>
    <div class="p-4">
      <canvas id="topProductBarChart" height="110"></canvas>
    </div>
  </div>

  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="p-4 font-semibold">Top 10 Menu Terlaris</div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Menu</th>
            <th class="text-right p-2">Qty</th>
            <th class="text-right p-2">Omzet</th>
            <th class="text-right p-2">Trx</th>
          </tr>
        </thead>
        <tbody>
          @forelse($topProducts as $row)
            <tr class="border-t">
              <td class="p-2">{{ $row->product_name }}</td>
              <td class="p-2 text-right">{{ number_format((float) $row->qty_total, 0, ',', '.') }}</td>
              <td class="p-2 text-right">Rp {{ number_format((float) $row->omzet_total, 0, ',', '.') }}</td>
              <td class="p-2 text-right">{{ number_format((float) $row->trx_total, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr class="border-t"><td class="p-2 text-gray-600" colspan="4">Belum ada data penjualan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="p-4 font-semibold">Stok Menipis</div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Bahan</th>
            <th class="text-right p-2">Stok</th>
            <th class="text-right p-2">Min</th>
          </tr>
        </thead>
        <tbody>
          @forelse($lowStock as $it)
            <tr class="border-t">
              <td class="p-2">{{ $it->name }} <span class="text-gray-500">({{ $it->baseUnit->symbol }})</span></td>
              <td class="p-2 text-right">{{ number_format($it->stock_base, 3, ',', '.') }}</td>
              <td class="p-2 text-right">{{ number_format((float)$it->min_stock, 3, ',', '.') }}</td>
            </tr>
          @empty
            <tr class="border-t"><td class="p-2 text-gray-600" colspan="3">Aman.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="p-4 font-semibold">Mendekati Expired (≤ 7 hari)</div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Bahan</th>
            <th class="text-right p-2">Qty</th>
            <th class="text-left p-2">Expired</th>
          </tr>
        </thead>
        <tbody>
          @forelse($expSoon as $b)
            <tr class="border-t">
              <td class="p-2">{{ $b->item->name }}</td>
              <td class="p-2 text-right">{{ number_format($b->qty_on_hand_base, 3, ',', '.') }}</td>
              <td class="p-2">{{ \Carbon\Carbon::parse($b->expired_at)->format('d M Y') }}</td>
            </tr>
          @empty
            <tr class="border-t"><td class="p-2 text-gray-600" colspan="3">Tidak ada.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
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
          borderWidth: 1,
          borderRadius: 6,
          backgroundColor: '#2563eb'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { precision: 0 }
          }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  }
</script>
@endpush
