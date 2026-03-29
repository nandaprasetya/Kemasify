@extends('admin.layouts.admin')
@section('page-title', 'Edit: ' . $productModel->name)

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.product-models.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <h2 style="font-size:18px; font-weight:800;">Edit: {{ $productModel->name }}</h2>
</div>

<div style="max-width:700px;">
    <div class="card">
        <form method="POST" action="{{ route('admin.product-models.update', $productModel) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.product-models._form', ['model' => $productModel])
            <hr class="divider">
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Update Model</button>
                <a href="{{ route('admin.product-models.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection