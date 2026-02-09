@extends('layouts.dashboard')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
  <div>
    <h1 class="text-xl font-semibold">Audit Log</h1>
    <p class="text-sm text-gray-600">Perubahan harga & stok</p>
  </div>
</div>

<div class="bg-white border rounded-lg p-4 mb-4">
  <form method="GET" action="{{ route('admin.reports.audit_logs') }}" class="flex flex-wrap items-end gap-3">
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
      <input type="date" name="from" value="{{ $from }}" class="border rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
      <input type="date" name="to" value="{{ $to }}" class="border rounded px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">Action</label>
      <select name="action" class="border rounded px-3 py-2 text-sm">
        <option value="">Semua</option>
        @foreach($actions as $a)
          <option value="{{ $a }}" @selected($action === $a)>{{ $a }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium">
        Tampilkan
      </button>
    </div>
  </form>
</div>

<div class="bg-white border rounded-lg p-4">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="text-left p-2 border-b">Waktu</th>
          <th class="text-left p-2 border-b">Action</th>
          <th class="text-left p-2 border-b">Detail</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr class="border-b">
            <td class="p-2 align-top">
              {{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}
            </td>
            <td class="p-2 align-top">
              <span class="rounded bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ $log->action }}</span>
            </td>
            <td class="p-2 align-top text-xs">
              <pre class="whitespace-pre-wrap text-gray-700">{{ json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="p-4 text-center text-gray-500">Belum ada log.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $logs->links() }}
  </div>
</div>
@endsection
