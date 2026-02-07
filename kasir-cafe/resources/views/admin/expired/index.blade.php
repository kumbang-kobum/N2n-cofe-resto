@extends('layouts.dashboard')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
  <div>
    <h1 class="text-xl font-semibold">Expired Disposal</h1>
    <div class="text-sm text-gray-600">Batch expired yang masih punya stok (base unit).</div>
  </div>

  <form class="flex gap-2">
    <input name="q" value="{{ $q }}" class="rounded border p-2 text-sm" placeholder="Cari bahan...">
    <button class="px-3 py-2 rounded bg-gray-900 text-white text-sm">Cari</button>
  </form>
</div>

<div class="bg-white border rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-3">Bahan</th>
          <th class="text-left p-3">Expired</th>
          <th class="text-right p-3">Qty (base)</th>
          <th class="text-right p-3">Cost/base</th>
          <th class="text-right p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($expiredBatches as $b)
          <tr class="border-t">
            <td class="p-3">
              <div class="font-medium">{{ $b->item->name }}</div>
              <div class="text-xs text-gray-500">Batch #{{ $b->id }}</div>
            </td>
            <td class="p-3">{{ \Carbon\Carbon::parse($b->expired_at)->format('d M Y') }}</td>
            <td class="p-3 text-right">{{ number_format($b->qty_on_hand_base, 3, ',', '.') }}</td>
            <td class="p-3 text-right">{{ number_format($b->unit_cost_base, 4, ',', '.') }}</td>
            <td class="p-3 text-right">
              <form method="POST" action="{{ route('admin.expired.dispose', $b->id) }}" class="inline-flex items-center gap-2">
                @csrf
                <input name="note" class="hidden md:block rounded border p-2 text-xs" placeholder="Catatan (opsional)">
                <button
                  onclick="return confirm('Buang batch expired ini? Stok akan menjadi 0 dan tercatat di ledger.')"
                  class="px-3 py-2 rounded bg-red-600 text-white text-sm hover:bg-red-700">
                  Buang
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr class="border-t">
            <td class="p-3 text-gray-600" colspan="5">Tidak ada batch expired yang memiliki stok.</td>
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