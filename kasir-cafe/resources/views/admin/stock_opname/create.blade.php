@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Buat Stock Opname</h1>

@if ($errors->any())
  <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded">
    <div class="font-semibold mb-1">Gagal simpan:</div>
    <ul class="list-disc pl-5 text-sm">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.stock_opname.store') }}" class="space-y-4">
  @csrf

  <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <div class="bg-white border rounded p-3">
      <label class="text-sm text-gray-600">Tanggal Opname</label>
      <input type="date" name="counted_at" value="{{ old('counted_at', now()->toDateString()) }}" class="w-full border rounded p-2">
      @error('counted_at')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    <div class="bg-white border rounded p-3 md:col-span-2">
      <label class="text-sm text-gray-600">Catatan</label>
      <input type="text" name="note" value="{{ old('note') }}" class="w-full border rounded p-2" placeholder="Misal: opname akhir shift malam">
    </div>
  </div>

  <div class="bg-white border rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2">Bahan</th>
            <th class="text-right p-2">Stok Sistem (base)</th>
            <th class="text-right p-2">Qty Fisik</th>
            <th class="text-left p-2">Unit</th>
            <th class="text-left p-2">Expired (isi jika selisih +)</th>
            <th class="text-right p-2">Harga/Unit (opsional)</th>
          </tr>
        </thead>

        <tbody>
          @forelse($items as $i => $it)
          <tr class="border-t">
            <td class="p-2">
              <div class="font-medium">{{ $it->name }}</div>
              <div class="text-xs text-gray-500">Base: {{ $it->baseUnit->symbol }}</div>
              <input type="hidden" name="lines[{{ $i }}][item_id]" value="{{ $it->id }}">
            </td>

            <td class="p-2 text-right">
              {{ number_format((float)($systemStock[$it->id] ?? 0), 3, ',', '.') }}
            </td>

            <td class="p-2 text-right">
              <input
                name="lines[{{ $i }}][physical_qty]"
                value="{{ old("lines.$i.physical_qty", 0) }}"
                class="w-28 border rounded p-2 text-right"
                inputmode="decimal"
              >
            </td>

            <td class="p-2">
              @php
                $defaultUnitId = old("lines.$i.unit_id", $it->base_unit_id);
              @endphp
              <select name="lines[{{ $i }}][unit_id]" class="border rounded p-2">
                @foreach($units as $u)
                  <option value="{{ $u->id }}" @selected((int)$defaultUnitId === (int)$u->id)>{{ $u->symbol }}</option>
                @endforeach
              </select>
            </td>

            <td class="p-2">
              <input type="date" name="lines[{{ $i }}][expired_at]" value="{{ old("lines.$i.expired_at") }}" class="border rounded p-2">
            </td>

            <td class="p-2 text-right">
              <input
                name="lines[{{ $i }}][unit_cost]"
                value="{{ old("lines.$i.unit_cost") }}"
                class="w-32 border rounded p-2 text-right"
                placeholder="0"
                inputmode="decimal"
              >
            </td>
          </tr>
          @empty
          <tr class="border-t">
            <td colspan="6" class="p-3 text-gray-700">
              <div class="p-3 bg-yellow-50 border border-yellow-200 rounded">
                Belum ada data bahan (Item). Tambahkan master bahan dulu supaya stock opname bisa dibuat.
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <button class="px-4 py-2 rounded bg-gray-900 text-white"
    @disabled($items->isEmpty())>
    Simpan Dokumen
  </button>
</form>
@endsection