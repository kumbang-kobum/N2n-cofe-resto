<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Rule</label>
        <input type="text" name="name" value="{{ old('name', $rule->name ?? '') }}" class="w-full border rounded px-3 py-2 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="is_active" class="w-full border rounded px-3 py-2 text-sm">
            <option value="1" @selected((string) old('is_active', $rule->is_active ?? true) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', $rule->is_active ?? true) === '0')>Nonaktif</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Dari Menit</label>
        <input type="number" name="min_minutes" min="1" value="{{ old('min_minutes', $rule->min_minutes ?? '') }}" class="w-full border rounded px-3 py-2 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Menit</label>
        <input type="number" name="max_minutes" min="1" value="{{ old('max_minutes', $rule->max_minutes ?? '') }}" class="w-full border rounded px-3 py-2 text-sm" placeholder="Kosong = tanpa batas">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Potongan</label>
        <input type="number" name="deduction_amount" min="0" value="{{ old('deduction_amount', $rule->deduction_amount ?? 0) }}" class="w-full border rounded px-3 py-2 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $rule->sort_order ?? 0) }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
</div>
