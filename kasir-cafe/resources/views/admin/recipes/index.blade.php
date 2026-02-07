@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">Resep (BOM)</h1>
</div>

<div class="bg-white border rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-3">Menu</th>
          <th class="text-right p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($products as $p)
          <tr class="border-t">
            <td class="p-3">{{ $p->name }}</td>
            <td class="p-3 text-right">
              <a class="px-3 py-1.5 rounded bg-gray-900 text-white" href="{{ route('admin.recipes.edit', $p->id) }}">Edit Resep</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection