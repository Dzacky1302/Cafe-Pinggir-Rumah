@extends('admin.layouts.app')

@section('title','Kelola Menu')
@section('page_title','Kelola Menu / Produk')

@section('content')
<div class="panel">
    <div class="panel-header">
        <h2><i class="fa-solid fa-mug-saucer"></i> Semua Produk ({{ $products->total() }})</h2>
        <div style="display:flex;gap:.6rem">
            <form class="filter-bar" method="GET">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk...">
                <button class="btn small" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <a href="{{ route('admin.products.create') }}" class="btn gold"><i class="fa-solid fa-plus"></i> Tambah</a>
        </div>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Varian</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>
                            @if($p->image)
                                <img src="{{ $p->image }}" class="thumb" alt="{{ $p->name }}">
                            @else
                                <div class="thumb" style="display:flex;align-items:center;justify-content:center;color:var(--gold-dark)"><i class="fa-solid fa-mug-hot"></i></div>
                            @endif
                        </td>
                        <td><strong>{{ $p->name }}</strong></td>
                        <td>{{ ucfirst($p->variant ?? '-') }}</td>
                        <td><strong>Rp {{ number_format($p->price/100,0,',','.') }}</strong></td>
                        <td>
                            <div style="display:flex;gap:.4rem">
                                <a href="{{ route('admin.products.edit', $p) }}" class="btn small info"><i class="fa-solid fa-pen"></i></a>
                                <form method="POST" action="{{ route('admin.products.destroy', $p) }}"
                                      onsubmit="return confirm('Hapus produk {{ $p->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn small danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--text-muted)">
                        <i class="fa-solid fa-mug-empty" style="font-size:2.5rem;opacity:.3"></i>
                        <p style="margin-top:1rem">Belum ada produk.</p>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">{{ $products->links() }}</div>
</div>
@endsection
