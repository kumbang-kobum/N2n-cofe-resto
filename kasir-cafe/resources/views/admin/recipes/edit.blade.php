@extends('layouts.dashboard')

@section('content')
  <h1 class="text-xl font-semibold mb-4">Resep Menu</h1>

  <div class="bg-white border rounded-lg p-4 mb-4 space-y-2">
    <div class="text-sm text-gray-600">Menu</div>
    <div class="text-lg font-semibold">
      {{ $product->name }} ({{ $product->sku ?? '-' }})
    </div>
    <div class="text-sm text-gray-600">
      Harga jual: Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}
    </div>
  </div>

  @if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
      <div class="font-semibold mb-1">Terjadi kesalahan:</div>
      <ul class="list-disc list-inside space-y-1">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form
    method="POST"
    action="{{ route('admin.recipes.update', $product->id) }}"
    class="space-y-4"
  >
    @csrf
    @method('PUT')

    {{-- Info umum resep --}}
    <div class="bg-white border rounded-lg p-4 space-y-3">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <label class="text-xs text-gray-600">Yield (porsi / unit hasil)</label>
          <input
            type="number"
            step="0.0001"
            name="yield_qty"
            value="{{ old('yield_qty', $recipe->yield_qty ?? 1) }}"
            class="w-full border rounded p-2 text-sm"
          >
        </div>

        <div>
          <label class="text-xs text-gray-600">Catatan</label>
          <input
            type="text"
            name="note"
            value="{{ old('note', $recipe->note ?? '') }}"
            class="w-full border rounded p-2 text-sm"
            placeholder="Catatan resep (opsional)"
          >
        </div>
      </div>
    </div>

    {{-- Daftar Bahan --}}
    <div class="bg-white border rounded-lg p-4 space-y-3">
      <div class="flex items-center justify-between mb-2">
        <h2 class="font-semibold text-sm">Bahan / Komposisi</h2>
        <button
          type="button"
          id="btn-add-line"
          class="px-3 py-1.5 rounded bg-blue-600 text-white text-xs"
        >
          + Tambah Bahan
        </button>
      </div>

      <div id="recipe-lines" class="space-y-3">
        @php
          // Siapkan data awal baris resep
          $existingLines = old('lines', null);

          if (is_array($existingLines)) {
              // data dari request sebelumnya (validasi gagal)
              $lineData = collect($existingLines);
          } else {
              // data dari database
              $lineData = ($lines ?? collect())->map(function ($ln) {
                  return [
                      'item_id' => $ln->item_id,
                      'qty'     => $ln->qty,
                      'unit_id' => $ln->unit_id,
                  ];
              });
          }

          if ($lineData->isEmpty()) {
              // minimal satu baris kosong
              $lineData = collect([
                  ['item_id' => null, 'qty' => null, 'unit_id' => null],
              ]);
          }
        @endphp

        @foreach($lineData as $i => $ln)
          <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end" data-line>
            <div class="md:col-span-6">
              <label class="text-xs text-gray-600">Bahan</label>
              <select
                name="lines[{{ $i }}][item_id]"
                class="w-full border rounded p-2 text-sm"
                required
              >
                <option value="">-- Pilih Bahan --</option>
                @foreach($items as $it)
                  <option
                    value="{{ $it->id }}"
                    @selected((int)($ln['item_id'] ?? 0) === $it->id)
                  >
                    {{ $it->name }} ({{ $it->baseUnit->symbol ?? $it->base_unit ?? '' }})
                  </option>
                @endforeach
              </select>
            </div>

            <div class="md:col-span-3">
              <label class="text-xs text-gray-600">Qty</label>
              <input
                type="number"
                step="0.0001"
                min="0"
                name="lines[{{ $i }}][qty]"
                value="{{ $ln['qty'] ?? '' }}"
                class="w-full border rounded p-2 text-sm"
                required
              >
            </div>

            <div class="md:col-span-3">
              <label class="text-xs text-gray-600">Unit</label>
              <div class="flex gap-2">
                <select
                  name="lines[{{ $i }}][unit_id]"
                  class="w-full border rounded p-2 text-sm"
                  required
                >
                  <option value="">-- Pilih Unit --</option>
                  @foreach($units as $u)
                    <option
                      value="{{ $u->id }}"
                      @selected((int)($ln['unit_id'] ?? 0) === $u->id)
                    >
                      {{ $u->name }} ({{ $u->symbol }})
                    </option>
                  @endforeach
                </select>

                <button
                  type="button"
                  class="btn-remove-line px-2 py-2 border rounded text-xs text-red-600"
                  title="Hapus baris"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <p class="text-xs text-gray-500 mt-1">
        Resep ini akan dipakai untuk menghitung kebutuhan BHP dan pengurangan stok bahan saat penjualan menu.
      </p>
    </div>

    <div class="flex gap-2">
      <button
        type="submit"
        class="px-4 py-2 rounded bg-green-600 text-white text-sm"
      >
        Simpan Resep
      </button>

      <a
        href="{{ route('admin.products.index') }}"
        class="px-4 py-2 rounded border text-sm"
      >
        Kembali
      </a>
    </div>
  </form>

  {{-- Script untuk tambah / hapus baris bahan --}}
  <script>
    (function () {
      const addBtn  = document.getElementById('btn-add-line');
      const wrapper = document.getElementById('recipe-lines');

      if (!addBtn || !wrapper) return;

      // Hitung index awal dari jumlah baris yang sudah ada
      let lineIndex = wrapper.querySelectorAll('[data-line]').length;
      if (lineIndex < 1) {
        lineIndex = 1;
      }

      addBtn.addEventListener('click', function (e) {
        e.preventDefault();

        const el = document.createElement('div');
        el.className = 'grid grid-cols-1 md:grid-cols-12 gap-2 items-end';
        el.setAttribute('data-line', '1');

        el.innerHTML = `
          <div class="md:col-span-6">
            <label class="text-xs text-gray-600">Bahan</label>
            <select
              name="lines[${lineIndex}][item_id]"
              class="w-full border rounded p-2 text-sm"
              required
            >
              <option value="">-- Pilih Bahan --</option>
              @foreach($items as $it)
                <option value="{{ $it->id }}">
                  {{ $it->name }} ({{ $it->baseUnit->symbol ?? $it->base_unit ?? '' }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="md:col-span-3">
            <label class="text-xs text-gray-600">Qty</label>
            <input
              type="number"
              step="0.0001"
              min="0"
              name="lines[${lineIndex}][qty]"
              class="w-full border rounded p-2 text-sm"
              required
            >
          </div>

          <div class="md:col-span-3">
            <label class="text-xs text-gray-600">Unit</label>
            <div class="flex gap-2">
              <select
                name="lines[${lineIndex}][unit_id]"
                class="w-full border rounded p-2 text-sm"
                required
              >
                <option value="">-- Pilih Unit --</option>
                @foreach($units as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->symbol }})</option>
                @endforeach
              </select>

              <button
                type="button"
                class="btn-remove-line px-2 py-2 border rounded text-xs text-red-600"
                title="Hapus baris"
              >
                ✕
              </button>
            </div>
          </div>
        `;

        wrapper.appendChild(el);
        lineIndex++;
      });

      // Hapus baris
      wrapper.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-line')) {
          e.preventDefault();
          const row = e.target.closest('[data-line]');
          if (!row) return;

          row.remove();

          // kalau semua baris terhapus, buat satu baris kosong supaya form tetap ada
          if (wrapper.querySelectorAll('[data-line]').length === 0) {
            lineIndex = 0;
            addBtn.click();
          }
        }
      });
    })();
  </script>
@endsection