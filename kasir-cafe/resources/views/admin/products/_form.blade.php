@csrf

<div class="mb-3">
    <label class="form-label">Nama Menu</label>
    <input type="text" name="name" class="form-control"
           value="{{ old('name', $product->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Harga Default</label>
    <input type="number" name="price_default" class="form-control"
           value="{{ old('price_default', $product->price_default ?? 0) }}" min="0" required>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="is_active" class="form-select">
        <option value="1" {{ old('is_active', $product->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ old('is_active', $product->is_active ?? 1) == 0 ? 'selected' : '' }}>Non Aktif</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Foto Menu</label>
    <input type="file" name="image" class="form-control" accept="image/*">

    @if (!empty($product?->image_path))
        <div class="mt-2">
            <p class="mb-1">Preview saat ini:</p>
            <img src="{{ $product->image_url }}"
                 alt="{{ $product->name }}"
                 style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
        </div>
    @endif

    <div class="form-text">
        Opsional. Maks 2MB. Disarankan rasio 1:1 (square).
    </div>
</div>