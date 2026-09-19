@extends('layouts.app')

@section('title','Keranjang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cart.css') }}?v=1">
@endpush

@section('content')
<section class="page-header">
    <h1>Keranjang Belanja</h1>
    <div class="breadcrumb"><a href="{{ route('coffee.index') }}">Beranda</a> / Keranjang</div>
</section>

<div class="cart-wrap">
    <div class="cart-items">
        <h2><i class="fa-solid fa-bag-shopping"></i> Item Kamu ({{ count($cart) }})</h2>

        @if(session('success'))
            <div class="alert success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        @if(empty($cart))
            <div class="empty-cart">
                <i class="fa-solid fa-mug-saucer"></i>
                <h3>Keranjang Masih Kosong</h3>
                <p>Yuk, pilih kopi favoritmu dan mulai pesan sekarang!</p>
                <a href="{{ route('coffee.index') }}" class="btn"><i class="fa-solid fa-mug-hot"></i> Lihat Menu</a>
            </div>
        @else
            @foreach($cart as $item)
                <div class="cart-item">
                    <div class="cart-item-img">
                        @if(!empty($item['image']))
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                        @else
                            <i class="fa-solid fa-mug-hot"></i>
                        @endif
                    </div>
                    <div class="cart-item-info">
                        <h4>{{ $item['name'] }}</h4>
                        <div class="variant">Varian: {{ ucfirst($item['variant'] ?? 'Regular') }}</div>
                        <span class="qty"><i class="fa-solid fa-cube"></i> {{ $item['qty'] }} pcs</span>
                    </div>
                    <div class="cart-item-price">
                        Rp {{ number_format(($item['price']*$item['qty'])/100,0,',','.') }}
                    </div>
                    <div class="actions">
                        <form method="POST" action="{{ url('cart/remove/'.$item['id']) }}">
                            @csrf
                            <button class="btn small danger" type="submit" title="Hapus item"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @if(!empty($cart))
    <aside class="cart-summary">
        <h3><i class="fa-solid fa-receipt" style="color:var(--gold-dark)"></i> Ringkasan Pesanan</h3>
        <div class="summary-row"><span>Subtotal</span><span>Rp {{ number_format($total/100,0,',','.') }}</span></div>
        <div class="summary-row"><span>Ongkir</span><span style="color:#1e6b3a;font-weight:600">Gratis</span></div>
        <div class="summary-row"><span>Pajak (0%)</span><span>Rp 0</span></div>

        <div class="promo-row">
            <input type="text" placeholder="Kode promo…">
            <button class="btn small secondary">Terapkan</button>
        </div>

        <div class="summary-row total">
            <span>Total</span>
            <span>Rp {{ number_format($total/100,0,',','.') }}</span>
        </div>

        <a href="{{ route('checkout.type') }}" class="btn gold block" style="margin-top:1.25rem">
            <i class="fa-solid fa-credit-card"></i> Lanjut ke Pembayaran
        </a>
        <a href="{{ route('coffee.index') }}" class="btn secondary block" style="margin-top:.6rem">
            <i class="fa-solid fa-arrow-left"></i> Lanjut Belanja
        </a>

        <p style="margin-top:1rem;font-size:.8rem;color:var(--text-muted);text-align:center">
            <i class="fa-solid fa-shield-halved" style="color:var(--gold-dark)"></i> Transaksi 100% aman &amp; terenkripsi
        </p>
    </aside>
    @endif
</div>
@endsection

