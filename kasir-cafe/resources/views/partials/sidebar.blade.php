<nav class="space-y-2">
  @role('admin')
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('admin.dashboard') }}">Admin</a>
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('admin.units.index') }}">Satuan & Konversi</a>
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('admin.items.index') }}">Master Bahan</a>
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('admin.products.index') }}">Master Menu</a>
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('admin.recipes.index') }}">Resep (BOM)</a>
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('admin.receivings.index') }}">Penerimaan Stok</a>
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('admin.stock.index') }}">Stok</a>
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('admin.reports.sales') }}">Laporan</a>
  @endrole

  @role('admin|manager')
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('manager.dashboard') }}">Manajer</a>
  @endrole

  @role('admin|cashier')
    <a class="block px-3 py-2 rounded hover:bg-gray-100" href="{{ route('cashier.pos') }}">Kasir (POS)</a>
  @endrole
</nav>