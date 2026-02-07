<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Laporan Selisih Opname</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    .muted { color: #666; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background: #f4f4f4; text-align: left; }
    .right { text-align: right; }
  </style>
</head>
<body>
  <h2 style="margin:0;">Laporan Selisih Stock Opname</h2>
  <div class="muted" style="margin:4px 0 10px;">
    Periode: <b>{{ $filters['from'] }}</b> s/d <b>{{ $filters['to'] }}</b>
    • Status: <b>{{ $filters['status'] }}</b>
    @if(!empty($filters['qItem'])) • Item: <b>{{ $filters['qItem'] }}</b> @endif
  </div>

  <table style="margin-bottom:10px;">
    <tr>
      <td><b>Total Plus Qty</b><br>{{ number_format($summary['plus_qty'], 3, ',', '.') }}</td>
      <td><b>Total Plus Nilai</b><br>Rp {{ number_format($summary['plus_val'], 0, ',', '.') }}</td>
      <td><b>Total Minus Qty</b><br>{{ number_format($summary['minus_qty'], 3, ',', '.') }}</td>
      <td><b>Total Minus Nilai</b><br>Rp {{ number_format($summary['minus_val'], 0, ',', '.') }}</td>
      <td><b>Net Nilai</b><br>Rp {{ number_format($summary['net_val'], 0, ',', '.') }}</td>
    </tr>
  </table>

  <table>
    <thead>
      <tr>
        <th>Opname</th>
        <th>Tanggal</th>
        <th>Status</th>
        <th>Item</th>
        <th class="right">Sistem</th>
        <th class="right">Fisik</th>
        <th class="right">Selisih</th>
        <th class="right">Cost</th>
        <th class="right">Nilai</th>
      </tr>
    </thead>
    <tbody>
      @foreach($lines as $l)
        @php
          $diff = (float)$l->diff_qty_base;
          $cost = (float)($l->unit_cost_base ?? 0);
          $val  = $diff * $cost;
        @endphp
        <tr>
          <td>{{ $l->opname->code ?? ('#'.$l->stock_opname_id) }}</td>
          <td>{{ optional($l->opname)->counted_at }}</td>
          <td>{{ optional($l->opname)->status }}</td>
          <td>{{ $l->item->name ?? '-' }} ({{ $l->item->baseUnit->symbol ?? '-' }})</td>
          <td class="right">{{ number_format((float)$l->system_qty_base, 3, ',', '.') }}</td>
          <td class="right">{{ number_format((float)$l->physical_qty_base, 3, ',', '.') }}</td>
          <td class="right">{{ number_format($diff, 3, ',', '.') }}</td>
          <td class="right">{{ number_format($cost, 3, ',', '.') }}</td>
          <td class="right">Rp {{ number_format($val, 0, ',', '.') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="muted" style="margin-top:10px;">
    Dicetak: {{ now()->format('d M Y H:i') }}
  </div>
</body>
</html>