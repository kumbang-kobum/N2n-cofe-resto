@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
  <div>
    <h1 class="text-xl font-semibold">Terima Stok</h1>
    <div class="text-sm text-gray-600">Input stok masuk per batch dengan supplier, biaya, dan expired.</div>
  </div>
  <a class="rounded border bg-white px-3 py-2 text-sm hover:bg-gray-50" href="{{ route('admin.receivings.index') }}">Kembali</a>
</div>

<form method="POST" action="{{ route('admin.receivings.store') }}" class="space-y-4">
  @csrf

  <div class="rounded-lg border bg-white p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Received at</label>
      <input name="received_at" type="datetime-local" class="w-full rounded border px-3 py-2 text-sm" value="{{ now()->format('Y-m-d\TH:i') }}">
    </div>
    <div class="md:col-span-2">
      <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
      <input name="supplier_name" class="w-full rounded border px-3 py-2 text-sm" placeholder="Opsional">
    </div>
  </div>

  <div class="rounded-lg border bg-white overflow-hidden">
    <div class="border-b px-4 py-3">
      <div class="font-semibold">Detail Barang</div>
      <div class="mt-2 rounded border border-amber-200 bg-amber-50 p-2 text-sm text-amber-700">
        Biaya bisa diisi sebagai <b>harga per unit</b> atau <b>total harga</b> sesuai mode di setiap baris.
      </div>
    </div>

    <div class="overflow-x-auto p-4">
      <div id="lines" class="space-y-3"></div>
      <button type="button" id="addLine" class="mt-2 rounded border bg-white px-3 py-2 text-sm hover:bg-gray-50">+ Tambah baris</button>
    </div>
  </div>

  <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Simpan</button>
</form>

<script>
  const items = @json($items->map(fn($it)=>['id'=>$it->id,'name'=>$it->name,'base'=>$it->baseUnit->symbol]));
  const units = @json($units->map(fn($u)=>['id'=>$u->id,'symbol'=>$u->symbol]));
  let idx = 0;

  function addLine(){
    const el = document.createElement('div');
    el.className = 'rounded-lg border border-slate-200 bg-slate-50 p-3';
    el.innerHTML = `
      <div class="grid grid-cols-12 gap-3">
        <div class="col-span-12 md:col-span-4">
          <label class="mb-1 block text-xs font-medium text-gray-600">Bahan</label>
          <select name="lines[${idx}][item_id]" class="w-full rounded border p-2 text-sm" required>
            ${items.map(it => `<option value="${it.id}">${it.name} (base: ${it.base})</option>`).join('')}
          </select>
        </div>
        <div class="col-span-6 md:col-span-2">
          <label class="mb-1 block text-xs font-medium text-gray-600">Qty</label>
          <input name="lines[${idx}][qty]" type="number" step="0.0001" class="w-full rounded border p-2 text-sm" placeholder="Qty" required>
        </div>
        <div class="col-span-6 md:col-span-2">
          <label class="mb-1 block text-xs font-medium text-gray-600">Satuan</label>
          <select name="lines[${idx}][unit_id]" class="w-full rounded border p-2 text-sm" required>
            ${units.map(u => `<option value="${u.id}">${u.symbol}</option>`).join('')}
          </select>
        </div>
        <div class="col-span-6 md:col-span-2">
          <label class="mb-1 block text-xs font-medium text-gray-600">Mode Biaya</label>
          <select name="lines[${idx}][cost_mode]" class="w-full rounded border p-2 text-sm" required>
            <option value="UNIT">Harga per unit</option>
            <option value="TOTAL">Total harga</option>
          </select>
        </div>
        <div class="col-span-6 md:col-span-2">
          <label class="mb-1 block text-xs font-medium text-gray-600">Biaya</label>
          <input name="lines[${idx}][unit_cost]" type="number" step="0.0001" class="w-full rounded border p-2 text-sm" placeholder="Biaya" required>
        </div>
        <div class="col-span-8 md:col-span-3">
          <label class="mb-1 block text-xs font-medium text-gray-600">Expired</label>
          <input name="lines[${idx}][expired_at]" type="date" class="w-full rounded border p-2 text-sm" required>
        </div>
        <div class="col-span-4 md:col-span-2 flex items-end">
          <button type="button" onclick="this.closest('.rounded-lg').remove()" class="w-full rounded border bg-white px-3 py-2 text-sm hover:bg-gray-50">Hapus</button>
        </div>
      </div>
    `;
    document.getElementById('lines').appendChild(el);
    idx++;
  }

  document.getElementById('addLine').addEventListener('click', addLine);
  addLine();
</script>
@endsection
