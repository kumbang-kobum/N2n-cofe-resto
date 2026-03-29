@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Expired Disposal</h1>

<div class="mb-4 flex items-center justify-between gap-2">
  <div class="text-sm text-gray-600">
    Batch expired yang masih punya stok aktif dan siap dibuang dari sistem.
  </div>
  <div class="rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700">
    {{ $expiredBatches->total() }} batch
  </div>
</div>

<form method="GET" class="mb-4">
  <div class="flex items-center gap-2">
    <input name="q"
           value="{{ $q }}"
           class="w-full max-w-sm rounded border px-3 py-2 text-sm"
           placeholder="Cari bahan...">
    <button class="rounded bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Cari</button>
    @if(!empty($q))
      <a href="{{ route('admin.expired.index') }}" class="rounded border px-3 py-2 text-sm hover:bg-gray-50">Reset</a>
    @endif
  </div>
</form>

<div class="rounded-lg border bg-white">
  <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs uppercase text-gray-500">
        <tr>
          <th class="text-left px-3 py-2 sticky top-0 z-10 bg-gray-50">Bahan</th>
          <th class="text-left px-3 py-2 sticky top-0 z-10 bg-gray-50">Expired</th>
          <th class="text-right px-3 py-2 sticky top-0 z-10 bg-gray-50">Qty (base)</th>
          <th class="text-right px-3 py-2 sticky top-0 z-10 bg-gray-50">Cost / base</th>
          <th class="text-right px-3 py-2 sticky top-0 z-10 bg-gray-50">Nilai Batch</th>
          <th class="text-right px-3 py-2 sticky top-0 z-10 bg-gray-50">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($expiredBatches as $batch)
          <tr class="border-t">
            <td class="px-3 py-2">
              <div class="font-medium text-slate-800">{{ $batch->item->name }}</div>
              <div class="text-xs text-gray-500">Batch #{{ $batch->id }}</div>
            </td>
            <td class="px-3 py-2">{{ \Carbon\Carbon::parse($batch->expired_at)->format('d M Y') }}</td>
            <td class="px-3 py-2 text-right">{{ number_format((float) $batch->qty_on_hand_base, 3, ',', '.') }}</td>
            <td class="px-3 py-2 text-right">Rp {{ number_format((float) $batch->unit_cost_base, 4, ',', '.') }}</td>
            <td class="px-3 py-2 text-right">Rp {{ number_format((float) $batch->qty_on_hand_base * (float) $batch->unit_cost_base, 2, ',', '.') }}</td>
            <td class="px-3 py-2 text-right">
              <form method="POST" action="{{ route('admin.expired.dispose', $batch->id) }}" class="inline-flex items-center gap-2">
                @csrf
                <input name="note" class="hidden rounded border px-2 py-1 text-xs lg:block" placeholder="Catatan (opsional)">
                <button onclick="return confirm('Buang batch expired ini? Stok akan menjadi 0 dan tercatat di ledger.')"
                        class="rounded bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">
                  Buang
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr class="border-t">
            <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">Tidak ada batch expired yang memiliki stok.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="p-3">
    {{ $expiredBatches->links() }}
  </div>
</div>
@endsection
