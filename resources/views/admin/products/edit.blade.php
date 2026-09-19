@extends('admin.layouts.app')

@section('title','Edit Produk')
@section('page_title','Edit Produk')

@section('content')
<div style="margin-bottom:1rem">
    <a href="{{ route('admin.products.index') }}" class="btn small secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</div>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fa-solid fa-pen"></i> Edit: {{ $product->name }}</h2>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @include('admin.products._form', ['product' => $product])
    </form>
</div>
@endsection
