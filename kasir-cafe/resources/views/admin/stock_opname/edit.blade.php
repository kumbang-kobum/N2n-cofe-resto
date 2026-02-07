@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Edit Opname: {{ $opname->code }}</h1>

<form method="POST" action="{{ route('admin.stock_opname.update', $opname->id) }}" class="space-y-4">
  @csrf

  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Item</th>
            <th class="text-right p-2">Selisih (base)</th>
            <th class="text-left p-2">Expired (wajib jika +)</th>
            <th class="text-right p-2">Unit Cost Base</th>
          </tr>
        </thead>
        <tbody>
          @foreach($opname->lines as $i => $l)
            <tr class="border-t">
              <td class="p-2">
                {{ $l->item->name }}
                <input type="hidden" name="lines[{{ $i }}][id]" value="{{ $l->id }}">
              </td>
              <td class="p-2 text-right">
                <span class="{{ $l->diff_qty_base < 0 ? 'text-red-600' : ($l->diff_qty_base > 0 ? 'text-green-600' : '') }}">
                  {{ number_format($l->diff_qty_base, 3, ',', '.') }}
                </span>
              </td>
              <td class="p-2">
                <input type="date"
                       name="lines[{{ $i }}][expired_at]"
                       value="{{ $l->expired_at }}"
                       class="border rounded p-2 w-full"
                       @if($l->diff_qty_base <= 0) disabled @endif>
                @if($l->diff_qty_base <= 0)
                  <div class="text-xs text-gray-500 mt-1">Hanya untuk selisih plus.</div>
                @endif
              </td>
              <td class="p-2 text-right">
                <input type="number" step="0.000001"
                       name="lines[{{ $i }}][unit_cost_base]"
                       value="{{ $l->unit_cost_base }}"
                       class="border rounded p-2 w-40 text-right">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded p-3 text-sm text-red-700">
      <ul class="list-disc pl-5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="flex gap-2">
    <button class="px-4 py-2 rounded bg-gray-900 text-white">Simpan</button>
    <a href="{{ route('admin.stock_opname.show', $opname->id) }}" class="px-4 py-2 rounded border">Kembali</a>
  </div>
</form>
@endsection