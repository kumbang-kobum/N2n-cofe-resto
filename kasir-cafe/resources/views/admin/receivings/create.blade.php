@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
  <div>
    <h1 class="text-xl font-semibold">Terima Stok</h1>
    <div class="text-sm text-gray-600">Input stok masuk per batch (expired wajib).</div>
  </div>
  <a class="px-3 py-2 rounded border bg-white" href="{{ route('admin.receivings.index') }}">Kembali</a>
</div>

<form method="POST" action="{{ route('admin.receivings.store') }}" class="space-y-4">
  @csrf

  <div class="bg-white border rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
    <div>
      <label class="text-sm text-gray-600">Received at</label>
      <input name="received_at" type="datetime-local" class="w-full rounded border p-2" value="{{ now()->format('Y-m-d\\TH:i') }}">
    </div>
    <div class="md:col-span-2">
      <label class="text-sm text-gray-600">Supplier</label>
      <input name="supplier_name" class="w-full rounded border p-2" placeholder="Opsional">
    </div>
  </div>

  <div class="bg-white border rounded-lg p-4 space-y-2">
    <div class="font-semibold">Detail Barang</div>
    <div id="lines" class="space-y-2"></div>
    <button type="button" id="addLine" class="px-3 py-2 rounded border bg-white">+ Tambah baris</button>
  </div>

  <button class="px-4 py-2 rounded bg-gray-900 text-white">Simpan</button>
</form>

<script>
  const items = @json($items->map(fn($it)=>['id'=>$it->id,'name'=>$it->name,'base'=>$it->baseUnit->symbol]));
  const units = @json($units->map(fn($u)=>['id'=>$u->id,'symbol'=>$u->symbol]));
  let idx = 0;

  function addLine(){
    const el = document.createElement('div');
    el.className = 'grid grid-cols-12 gap-2';
    el.innerHTML = `
      <div class="col-span-12 md:col-span-4">
        <select name="lines[${idx}][item_id]" class="w-full rounded border p-2" required>
          ${items.map(it => `<option value="${it.id}">${it.name} (base: ${it.base})</option>`).join('')}
        </select>
      </div>
      <div class="col-span-6 md:col-span-2">
        <input name="lines[${idx}][qty]" type="number" step="0.0001" class="w-full rounded border p-2" placeholder="Qty" required>
      </div>
      <div class="col-span-6 md:col-span-2">
        <select name="lines[${idx}][unit_id]" class="w-full rounded border p-2" required>
          ${units.map(u => `<option value="${u.id}">${u.symbol}</option>`).join('')}
        </select>
      </div>
      <div class="col-span-6 md:col-span-2">
        <input name="lines[${idx}][unit_cost]" type="number" step="0.0001" class="w-full rounded border p-2" placeholder="Harga/unit" required>
      </div>
      <div class="col-span-6 md:col-span-2">
        <input name="lines[${idx}][expired_at]" type="date" class="w-full rounded border p-2" required>
      </div>
      <div class="col-span-12 md:col-span-12">
        <button type="button" onclick="this.closest('.grid').remove()" class="px-3 py-1.5 rounded border">Hapus</button>
      </div>
    `;
    document.getElementById('lines').appendChild(el);
    idx++;
  }

  document.getElementById('addLine').addEventListener('click', addLine);
  addLine();
</script>
@endsection