@extends('layouts.app')

@section('title','Pilih Tipe Pesanan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}?v=1">
@endpush

@section('content')
<div class="checkout-wrap">

    <div class="checkout-steps">
        <div class="step active"><span class="num">1</span> Tipe Pesanan</div>
        <div class="step"><span class="num">2</span> Detail</div>
        <div class="step"><span class="num">3</span> Pembayaran</div>
    </div>

    <div style="text-align:center;margin-bottom:1rem">
        <h1 style="font-family:'Playfair Display',serif;color:var(--coffee-dark);font-size:2.2rem;margin:0 0 .5rem">
            Mau Nikmati Kopi Di Mana? ☕
        </h1>
        <p style="color:var(--text-muted);max-width:520px;margin:0 auto">
            Pilih tipe pesanan yang paling cocok buat kamu hari ini
        </p>
    </div>

    <form method="POST" action="{{ route('checkout.form') }}" id="typeForm">
        @csrf
        <div class="type-grid">
            <label class="type-card" onclick="document.getElementById('t_dine').click();this.closest('form').submit()">
                <input type="radio" name="order_type" id="t_dine" value="dine_in" hidden>
                <div class="icon"><i class="fa-solid fa-utensils"></i></div>
                <h3>Dine In</h3>
                <p>Santap langsung di kafe kami. Nikmati suasana hangat sambil ngobrol.</p>
                <ul class="features">
                    <li>Disajikan langsung ke meja</li>
                    <li>Suasana kafe yang cozy</li>
                    <li>WiFi &amp; colokan tersedia</li>
                    <li>Bebas nongkrong sepuasnya</li>
                </ul>
                <button type="button" class="btn gold block" onclick="document.getElementById('t_dine').click();this.closest('form').submit()">
                    <i class="fa-solid fa-arrow-right"></i> Pilih Dine In
                </button>
            </label>

            <label class="type-card" onclick="document.getElementById('t_take').click();this.closest('form').submit()">
                <input type="radio" name="order_type" id="t_take" value="take_away" hidden>
                <div class="icon"><i class="fa-solid fa-bag-shopping"></i></div>
                <h3>Take Away</h3>
                <p>Ambil pesanan dan nikmati di mana saja. Praktis buat yang buru-buru!</p>
                <ul class="features">
                    <li>Dibungkus rapi &amp; aman</li>
                    <li>Bisa jadwalkan waktu ambil</li>
                    <li>Antrian prioritas</li>
                    <li>Cocok saat mobile</li>
                </ul>
                <button type="button" class="btn gold block" onclick="document.getElementById('t_take').click();this.closest('form').submit()">
                    <i class="fa-solid fa-arrow-right"></i> Pilih Take Away
                </button>
            </label>
        </div>
    </form>

    <div style="text-align:center;margin-top:2rem">
        <a href="{{ route('cart') }}" style="color:var(--text-muted);text-decoration:none">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Keranjang
        </a>
    </div>
</div>
@endsection
