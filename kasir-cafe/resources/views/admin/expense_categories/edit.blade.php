@extends('layouts.dashboard')

@section('content')
<div class="max-w-2xl">
  <h1 class="text-xl font-semibold mb-4">Edit Kategori Pengeluaran</h1>

  <form action="{{ route('admin.expense_categories.update', $expenseCategory) }}" method="POST" class="bg-white border rounded-lg p-4 space-y-4">
    @csrf
    @method('PUT')

    <div>
      <label class="block text-sm font-medium mb-1">Nama Kategori</label>
      <input type="text" name="name" value="{{ old('name', $expenseCategory->name) }}" class="w-full border rounded px-3 py-2" required>
      @error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Deskripsi</label>
      <textarea name="description" rows="3" class="w-full border rounded px-3 py-2">{{ old('description', $expenseCategory->description) }}</textarea>
      @error('description')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Limit Approval (Rp)</label>
      <input type="number" min="0" step="1" name="approval_limit_amount" value="{{ old('approval_limit_amount', $expenseCategory->approval_limit_amount) }}" class="w-full border rounded px-3 py-2" placeholder="Kosongkan jika tanpa batas">
      <div class="mt-1 text-xs text-slate-500">Dipakai sebagai acuan kontrol. Pengajuan yang melebihi nominal ini akan ditandai jelas di daftar pengeluaran.</div>
      @error('approval_limit_amount')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
    </div>

    <label class="flex items-center gap-2 text-sm">
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $expenseCategory->is_active))>
      Aktif
    </label>

    <div class="flex items-center gap-2">
      <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white text-sm">Update</button>
      <a href="{{ route('admin.expense_categories.index') }}" class="px-4 py-2 rounded border text-sm">Batal</a>
    </div>
  </form>
</div>
@endsection
