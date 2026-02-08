@extends('layouts.dashboard') {{-- atau layouts.admin, sesuaikan dengan project kamu --}}

@section('content')
<div class="container">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="d-flex justify-content-between mb-3">
        <h1>Produk / Katalog Menu</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Tambah Menu</a>
    </div>

    <table class="table table-bordered align-middle">
        <thead>
        <tr>
            <th>Foto</th>
            <th>Nama</th>
            <th>Harga Default</th>
            <th>Aktif?</th>
            <th width="160">Aksi</th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td style="width: 80px;">
                    <img src="{{ $product->image_url }}"
                         alt="{{ $product->name }}"
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                </td>
                <td>{{ $product->name }}</td>
                <td>{{ number_format($product->price_default, 0, ',', '.') }}</td>
                <td>
                    @if($product->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Non Aktif</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('admin.products.destroy', $product) }}"
                          method="POST"
                          style="display:inline-block"
                          onsubmit="return confirm('Hapus produk ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada produk.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $products->links() }}
</div>
@endsection