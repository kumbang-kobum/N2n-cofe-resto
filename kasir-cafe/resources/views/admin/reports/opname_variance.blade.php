@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Laporan Selisih Opname</h1>

<form class="mb-4 flex flex-wrap gap-2 items-end" method="GET">
  <div>
    <label class="text-sm text-gray-600">Dari</label>
    <input type="date" name="from" value="{{ $from }}" class="border rounded p-2">
  </div>
  <div>
    <label class="text-sm text-gray-600">Sampai</label>
    <input type="date" name="to" value="{{ $to }}" class="border rounded p-2">
  </div>
  <button class="px-3 py-2 rounded bg-gray-900 text-white text-sm">Filter</button>
</form>

<div class="bg-white border rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-2">Tanggal</th>
          <th class="text-left p-2">Kode</th>
          <th class="text-left p-2">Status</th>
          <th class="text-left p-2">Bahan</th>
          <th class="text-right p-2">Sistem</th>
          <th class="text-right p-2">Fisik</th>
          <th class="text-right p-2">Selisih</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr class="border-t">
            <td class="p-2">{{ \Carbon\Carbon::parse($r->counted_at)->format('d M Y') }}</td>
            <td class="p-2">
              <a class="text-blue-600" href="{{ route('admin.stock_opname.show', $r->stock_opname_id) }}">{{ $r->opname_code }}</a>
            </td>
            <td class="p-2">{{ $r->opname_status }}</td>
            <td class="p-2">{{ $r->item_name }}</td>
            <td class="p-2 text-right">{{ number_format($r->system_qty_base, 3, ',', '.') }}</td>
            <td class="p-2 text-right">{{ number_format($r->physical_qty_base, 3, ',', '.') }}</td>
            <td class="p-2 text-right {{ $r->diff_qty_base < 0 ? 'text-red-600' : 'text-green-600' }}">
              {{ number_format($r->diff_qty_base, 3, ',', '.') }}
            </td>
          </tr>
        @empty
          <tr class="border-t"><td colspan="7" class="p-3 text-gray-600">Tidak ada selisih.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $rows->links() }}</div>
</div>
@endsection