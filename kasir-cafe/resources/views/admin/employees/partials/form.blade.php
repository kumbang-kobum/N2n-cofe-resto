<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Karyawan</label>
        <input type="text" name="employee_code" value="{{ old('employee_code', $employee->employee_code ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
        @error('employee_code')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
        <input type="text" name="name" value="{{ old('name', $employee->name ?? '') }}" class="w-full border rounded px-3 py-2 text-sm" required>
        @error('name')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
        <input type="text" name="position" value="{{ old('position', $employee->position ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
        <input type="text" name="department" value="{{ old('department', $employee->department ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Jatah Makan Nominal / Bulan</label>
        <input type="number" name="meal_allowance_monthly" min="0" step="1" value="{{ old('meal_allowance_monthly', $employee->meal_allowance_monthly ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
        <div class="mt-1 text-xs text-gray-500">Kosongkan jika tidak dibatasi. Isi 0 jika seluruh makan karyawan harus dipotong payroll.</div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gunakan Aplikasi</label>
        <select name="uses_app" class="w-full border rounded px-3 py-2 text-sm">
            <option value="0" @selected((string) old('uses_app', $employee->uses_app ?? false) === '0')>Tidak</option>
            <option value="1" @selected((string) old('uses_app', $employee->uses_app ?? false) === '1')>Ya</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Link User (opsional)</label>
        <select name="user_id" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">-</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('user_id', $employee->user_id ?? '') === (string) $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="is_active" class="w-full border rounded px-3 py-2 text-sm">
            <option value="1" @selected((string) old('is_active', $employee->is_active ?? true) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', $employee->is_active ?? true) === '0')>Nonaktif</option>
        </select>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
    <textarea name="note" rows="3" class="w-full border rounded px-3 py-2 text-sm">{{ old('note', $employee->note ?? '') }}</textarea>
</div>
