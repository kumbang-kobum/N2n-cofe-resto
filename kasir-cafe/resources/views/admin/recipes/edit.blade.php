@extends('layouts.dashboard')

@section('content')
<div class="flex items-start justify-between gap-3 mb-4">
  <div>
    <h1 class="text-xl font-semibold">Edit Resep</h1>
    <div class="text-sm text-gray-600">{{ $product->name }}</div>
  </div>
  <a class="px-3 py-1.5 rounded border bg-white" href="{{ route('admin.recipes.index') }}">Kembali</a>
</div>

<form method="POST" action="{{ route('admin.recipes.update', $product->id) }}" class="space-y-4">
  @csrf

  <div class="bg-white border rounded-lg p-4 space-y-3">
    <div class="text-sm text-gray-700">Tambah bahan (boleh banyak baris).</div>

    <div class="space-y-2" id="lines">
      @php $i=0; @endphp
      @foreach($lines as $line)
        <div class="grid grid-cols-12 gap-2">
          <div class="col-span-12 md:col-span-5">
            <select name="lines[{{ $i }}][item_id]" class="w-full rounded border p-2">
              @foreach($items as $it)
                <option value="{{ $it->id }}" @selected($it->id === $line->item_id)>{{ $it->name }} (base: {{ $it->baseUnit->symbol }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-span-6 md:col-span-3">
            <input name="lines[{{ $i }}][qty]" value="{{ $line->qty }}" type="number" step="0.0001" class="w-full rounded border p-2" placeholder="Qty">
          </div>
          <div class="col-span-6 md:col-span-3">
            <select name="lines[{{ $i }}][unit_id]" class="w-full rounded border p-2">
              @foreach($units as $u)
                <option value="{{ $u->id }}" @selected($u->id === $line->unit_id)>{{ $u->symbol }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-span-12 md:col-span-1 flex items-center">
            <button type="button" onclick="this.closest('.grid').remove()" class="w-full rounded border p-2">✕</button>
          </div>
        </div>
        @php $i++; @endphp
      @endforeach
    </div>

    <button type="button" id="addLine" class="px-3 py-2 rounded border bg-white">+ Tambah baris</button>
  </div>

  <button class="px-4 py-2 rounded bg-gray-900 text-white">Simpan Resep</button>
</form>

<script>
  const items = @json($items->map(fn($it)=>['id'=>$it->id,'name'=>$it->name,'base'=>$it->baseUnit->symbol]));
  const units = @json($units->map(fn($u)=>['id'=>$u->id,'symbol'=>$u->symbol]));
  let idx = {{ $i ?? 0 }};

  document.getElementById('addLine').addEventListener('click', () => {
    const el = document.createElement('div');
    el.className = 'grid grid-cols-12 gap-2';
    el.innerHTML = `
      <div class="col-span-12 md:col-span-5">
        <select name="lines[${idx}][item_id]" class="w-full rounded border p-2">
          ${items.map(it => `<option value="${it.id}">${it.name} (base: ${it.base})</option>`).join('')}
        </select>
      </div>
      <div class="col-span-6 md:col-span-3">
        <input name="lines[${idx}][qty]" type="number" step="0.0001" class="w-full rounded border p-2" placeholder="Qty">
      </div>
      <div class="col-span-6 md:col-span-3">
        <select name="lines[${idx}][unit_id]" class="w-full rounded border p-2">
          ${units.map(u => `<option value="${u.id}">${u.symbol}</option>`).join('')}
        </select>
      </div>
      <div class="col-span-12 md:col-span-1 flex items-center">
        <button type="button" onclick="this.closest('.grid').remove()" class="w-full rounded border p-2">✕</button>
      </div>
    `;
    document.getElementById('lines').appendChild(el);
    idx++;
  });
</script>
@endsection