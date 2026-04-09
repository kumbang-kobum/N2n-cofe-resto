<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Shift</label>
        <input type="text" name="name" value="{{ old('name', $shift->name ?? '') }}" class="dashboard-input" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="is_active" class="dashboard-input">
            <option value="1" @selected((string) old('is_active', $shift->is_active ?? true) === '1')>Aktif</option>
            <option value="0" @selected((string) old('is_active', $shift->is_active ?? true) === '0')>Nonaktif</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Masuk</label>
        <input type="time" name="start_time" value="{{ old('start_time', $shift->start_time ?? '') }}" class="dashboard-input" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Jam Pulang</label>
        <input type="time" name="end_time" value="{{ old('end_time', $shift->end_time ?? '') }}" class="dashboard-input" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Toleransi Telat (menit)</label>
        <input type="number" name="late_tolerance_minutes" min="0" value="{{ old('late_tolerance_minutes', $shift->late_tolerance_minutes ?? 0) }}" class="dashboard-input">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Lembur Mulai Setelah (menit)</label>
        <input type="number" name="overtime_after_minutes" min="0" value="{{ old('overtime_after_minutes', $shift->overtime_after_minutes ?? 0) }}" class="dashboard-input">
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
    <textarea name="note" rows="3" class="dashboard-input">{{ old('note', $shift->note ?? '') }}</textarea>
</div>
