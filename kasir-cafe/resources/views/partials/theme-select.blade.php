<label class="{{ $wrapperClass ?? '' }}">
  <span>{{ $label ?? 'Tema' }}</span>
  <select data-theme-select class="{{ $selectClass ?? '' }}">
    <option value="light" class="text-slate-900">Light</option>
    <option value="dark" class="text-slate-900">Dark</option>
    <option value="forest" class="text-slate-900">Hijau</option>
    <option value="amber" class="text-slate-900">Amber</option>
    <option value="maroon" class="text-slate-900">Marun</option>
  </select>
</label>
