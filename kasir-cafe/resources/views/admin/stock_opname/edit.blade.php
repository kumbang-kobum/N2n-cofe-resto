@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Edit Stock Opname</h1>

@if ($errors->any())
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded">
    <ul class="list-disc pl-5 text-sm">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.stock_opname.update', $opname->id) }}" class="space-y-4">
  @csrf

  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Bahan</th>
            <th class="text-right p-2">Selisih (base)</th>
            <th class="text-left p-2">Expired (wajib jika +)</th>
            <th class="text-right p-2">Cost Base</th>
          </tr>
        </thead>
        <tbody>
          @foreach($opname->lines as $i => $l)
            <tr class="border-t">
              <td class="p-2">
                {{ $l->item->name }}
                <input type="hidden" name="lines[{{ $i }}][id]" value="{{ $l->id }}">
              </td>

              <td class="p-2 text-right {{ $l->diff_qty_base < 0 ? 'text-red-600' : ($l->diff_qty_base > 0 ? 'text-green-600' : '') }}">
                {{ number_format($l->diff_qty_base, 3, ',', '.') }}
              </td>

              <td class="p-2">
                <input type="date" name="lines[{{ $i }}][expired_at]" value="{{ $l->expired_at?->toDateString() }}" class="border rounded p-2">
              </td>

              <td class="p-2 text-right">
                <input name="lines[{{ $i }}][unit_cost_base]" value="{{ $l->unit_cost_base }}" class="w-40 border rounded p-2 text-right" inputmode="decimal">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="flex gap-2">
    <a href="{{ route('admin.stock_opname.show', $opname->id) }}" class="px-3 py-2 rounded border">Batal</a>
    <button class="px-3 py-2 rounded bg-gray-900 text-white">Simpan</button>
  </div>
</form>
@endsection