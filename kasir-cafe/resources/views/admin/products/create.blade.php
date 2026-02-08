@extends('layouts.dashboard')

@section('content')
<div class="container">
    <h1>Tambah Menu</h1>

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

    <form action="{{ route('admin.products.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @include('admin.products._form', ['product' => null])

        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection