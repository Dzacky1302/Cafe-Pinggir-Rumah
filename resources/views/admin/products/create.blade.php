@extends('admin.layouts.app')

@section('title','Tambah Produk')
@section('page_title','Tambah Produk Baru')

@section('content')
<div style="margin-bottom:1rem">
    <a href="{{ route('admin.products.index') }}" class="btn small secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</div>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fa-solid fa-plus"></i> Produk Baru</h2>
    </div>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @include('admin.products._form', ['product' => null])
    </form>
</div>
@endsection
