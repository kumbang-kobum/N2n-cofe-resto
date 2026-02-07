<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    .h1 { font-size: 18px; font-weight: bold; margin-bottom: 6px; }
    .meta { color:#444; margin-bottom: 12px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ddd; padding:6px; }
    th { background:#f5f5f5; text-align:left; }
    .right { text-align:right; }
    .green { color: #0a7; }
    .red { color: #c00; }
  </style>
</head>
<body>

<div class="h1">Stock Opname</div>
<div class="meta">
  <div><b>Kode:</b> {{ $opname->code }}</div>
  <div><b>Tanggal:</b> {{ \Carbon\Carbon::parse($opname->counted_at)->format('d M Y') }}</div>
  <div><b>Status:</b> {{ $opname->status }}</div>
  @if($opname->note)<div><b>Catatan:</b> {{ $opname->note }}</div>@endif
</div>

<table>
  <thead>
    <tr>
      <th>Bahan</th>
      <th class="right">Sistem</th>
      <th class="right">Fisik</th>
      <th class="right">Selisih</th>
      <th>Expired (jika +)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($opname->lines as $l)
      <tr>
        <td>{{ $l->item->name }}</td>
        <td class="right">{{ number_format($l->system_qty_base, 3, ',', '.') }}</td>
        <td class="right">{{ number_format($l->physical_qty_base, 3, ',', '.') }}</td>
        <td class="right {{ $l->diff_qty_base < 0 ? 'red' : ($l->diff_qty_base > 0 ? 'green' : '') }}">
          {{ number_format($l->diff_qty_base, 3, ',', '.') }}
        </td>
        <td>{{ $l->expired_at ? \Carbon\Carbon::parse($l->expired_at)->format('d M Y') : '-' }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<br>
<div>Dicetak: {{ now()->format('d M Y H:i') }}</div>
</body>
</html>