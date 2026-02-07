@extends('layouts.dashboard')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">Stock Opname</h1>
  <a href="{{ route('admin.stock_opname.create') }}" class="px-3 py-2 rounded bg-gray-900 text-white text-sm">Buat Opname</a>
</div>

@if(session('status'))
  <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded">{{ session('status') }}</div>
@endif

<div class="bg-white border rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-3">Kode</th>
          <th class="text-left p-3">Tanggal</th>
          <th class="text-left p-3">Status</th>
          <th class="text-right p-3">Lines</th>
          <th class="text-left p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($opnames as $o)
          <tr class="border-t">
            <td class="p-3 font-medium">{{ $o->code }}</td>
            <td class="p-3">{{ \Carbon\Carbon::parse($o->counted_at)->format('d M Y') }}</td>
            <td class="p-3">
              <span class="px-2 py-1 rounded text-xs
                {{ $o->status === 'POSTED' ? 'bg-green-100 text-green-700' : ($o->status === 'DRAFT' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700') }}">
                {{ $o->status }}
              </span>
            </td>
            <td class="p-3 text-right">{{ $o->lines_count }}</td>
            <td class="p-3">
              <a class="text-blue-600" href="{{ route('admin.stock_opname.show', $o->id) }}">Detail</a>
            </td>
          </tr>
        @empty
          <tr class="border-t">
            <td colspan="5" class="p-3 text-gray-600">Belum ada opname.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="p-3">{{ $opnames->links() }}</div>
</div>
@endsection