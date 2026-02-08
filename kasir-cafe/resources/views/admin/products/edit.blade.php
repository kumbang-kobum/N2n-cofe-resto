@extends('layouts.dashboard')

@section('content')
<div class="container">
    <h1>Edit Menu: {{ $product->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Gagal menyimpan:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product) }}"
          method="POST"
          enctype="multipart/form-data">
        @method('PUT')

        @include('admin.products._form', ['product' => $product])

        <button class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection