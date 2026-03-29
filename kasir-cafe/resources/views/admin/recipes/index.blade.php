@extends('layouts.dashboard')

@section('content')
<h1 class="text-xl font-semibold mb-4">Resep (BOM)</h1>

<div class="mb-4 flex items-center justify-between gap-2">
  <div class="text-sm text-gray-600">
    Daftar menu yang sudah bisa disusun resep bahan bakunya.
  </div>
  <div class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
    {{ $products->total() }} menu
  </div>
</div>

<form method="GET" class="mb-4">
  <div class="flex items-center gap-2">
    <input type="text"
           name="q"
           value="{{ $q ?? '' }}"
           placeholder="Cari nama menu..."
           class="w-full max-w-sm rounded border px-3 py-2 text-sm">
    <button type="submit"
            class="rounded bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">
      Cari
    </button>
    @if(!empty($q))
      <a href="{{ route('admin.recipes.index') }}"
         class="rounded border px-3 py-2 text-sm hover:bg-gray-50">
        Reset
      </a>
    @endif
  </div>
</form>

<div class="rounded-lg border bg-white">
  <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
    <table class="w-full text-left text-sm">
      <thead class="bg-gray-50 text-xs uppercase text-gray-500">
        <tr>
          <th class="px-3 py-2 sticky top-0 z-10 bg-gray-50">Menu</th>
          <th class="px-3 py-2 text-center sticky top-0 z-10 bg-gray-50">Status Resep</th>
          <th class="px-3 py-2 text-right sticky top-0 z-10 bg-gray-50">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $product)
          <tr class="border-t">
            <td class="px-3 py-2 font-medium text-slate-800">{{ $product->name }}</td>
            <td class="px-3 py-2 text-center">
              @if($product->recipe)
                <span class="rounded bg-green-50 px-2 py-0.5 text-xs text-green-700">Sudah ada resep</span>
              @else
                <span class="rounded bg-amber-50 px-2 py-0.5 text-xs text-amber-700">Belum disusun</span>
              @endif
            </td>
            <td class="px-3 py-2 text-right">
              <a href="{{ route('admin.recipes.edit', $product->id) }}"
                 class="text-xs font-medium text-blue-600 hover:underline">
                Edit Resep
              </a>
            </td>
          </tr>
        @empty
          <tr class="border-t">
            <td colspan="3" class="px-3 py-4 text-center text-sm text-gray-500">
              Belum ada menu yang cocok dengan pencarian.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  {{ $products->links() }}
</div>
@endsection
