@extends('layouts.app')

@section('title','Beranda')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages.css') }}?v=1">
@endpush

@section('content')

<!-- HERO -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-content">
            <small><i class="fa-solid fa-mug-saucer"></i> Premium Coffee Experience</small>
            <h1>Rasakan Seni <span>Kopi Sejati</span> Setiap Hari</h1>
            <p>Diseduh dari biji kopi Arabika &amp; Robusta pilihan terbaik nusantara oleh barista berpengalaman. Setiap cangkir adalah karya seni.</p>
            <div class="hero-actions">
                <a href="#menu" class="btn gold"><i class="fa-solid fa-mug-hot"></i> Lihat Menu</a>
                <a href="#tentang" class="btn secondary" style="color:#fff;border-color:#fff">Tentang Kami</a>
            </div>
            <div class="hero-stats">
                <div><h3>15+</h3><p>Menu Signature</p></div>
                <div><h3>10K+</h3><p>Pelanggan Setia</p></div>
                <div><h3>4.9<i class="fa-solid fa-star" style="font-size:1rem"></i></h3><p>Rating Kepuasan</p></div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features">
    <div class="feature-card">
        <div class="feature-icon"><i class="fa-solid fa-seedling"></i></div>
        <h4>100% Biji Pilihan</h4>
        <p>Biji kopi single origin dari petani lokal terbaik Nusantara.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon"><i class="fa-solid fa-mug-hot"></i></div>
        <h4>Barista Berpengalaman</h4>
        <p>Diracik oleh barista bersertifikat dengan penuh cinta.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
        <h4>Pesan &amp; Bayar Mudah</h4>
        <p>Checkout instan dengan pembayaran aman via Midtrans.</p>
    </div>
</section>

<!-- MENU -->
<section class="menu-section container" id="menu">
    <div class="section-title">
        <small>Our Menu</small>
        <h2>Katalog Kopi Signature</h2>
        <p>Nikmati pilihan menu kopi terbaik kami — dari klasik espresso hingga racikan modern kekinian.</p>
    </div>

    @if(session('success'))
        <div class="alert success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    @php
        $defaultImages = [
            'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1497935586351-b67a49e012bf?auto=format&fit=crop&w=600&q=80',
        ];
        $tags = ['Best Seller','Signature','New','Popular','Favorite','Recommended','Premium','Hot Item'];
    @endphp

    <div class="grid">
        @foreach($grouped as $base => $variants)
            @php
                $img = $variants[0]->image ?? $defaultImages[$loop->index % count($defaultImages)];
                $tag = $tags[$loop->index % count($tags)];
                $minPrice = min(array_map(fn($x)=>$x->price,$variants));
                $rating = number_format(4.5 + (($loop->index % 5) * 0.1), 1);
            @endphp
            <div class="product-card">
                <div class="product-image">
                    <img src="{{ $img }}" alt="{{ $base }}" onerror="this.src='{{ $defaultImages[0] }}'">
                    <span class="product-tag">{{ $tag }}</span>
                    <span class="product-fav" title="Favorit"><i class="fa-regular fa-heart"></i></span>
                </div>
                <div class="product-body">
                    <div class="rating">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                        <span>({{ $rating }})</span>
                    </div>
                    <h3>{{ $base }}</h3>
                    <p class="desc">{{ $variants[0]->description ?? 'Kopi pilihan dengan aroma harum dan rasa khas yang memanjakan lidah.' }}</p>
                    <div class="price">Rp {{ number_format($minPrice/100,0,',','.') }} <small>/ mulai</small></div>

                    <form method="POST" action="{{ route('cart.add') }}">
                        @csrf
                        <select class="variant-select" name="product_id">
                            @foreach($variants as $v)
                                <option value="{{ $v->id }}">{{ ucfirst($v->variant ?? 'Regular') }} — Rp {{ number_format($v->price/100,0,',','.') }}</option>
                            @endforeach
                        </select>
                        <button class="btn block" type="submit">
                            <i class="fa-solid fa-cart-plus"></i> Tambah ke Keranjang
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- ABOUT -->
<section class="about" id="tentang">
    <div class="about-grid">
        <div class="about-img">
            <img src="https://images.unsplash.com/photo-1453614512568-c4024d13c247?auto=format&fit=crop&w=800&q=80" alt="Cafe Interior">
        </div>
        <div class="about-content">
            <small>Tentang Kami</small>
            <h2>Sepenggal Cerita di Balik Setiap Cangkir</h2>
            <p>Brew &amp; Bean Café hadir sejak 2024 dengan satu misi sederhana — menyajikan kopi berkualitas premium yang bisa dinikmati semua kalangan.</p>
            <p>Kami bekerja langsung dengan para petani kopi lokal di Aceh Gayo, Toraja, hingga Bali Kintamani untuk memastikan setiap biji kopi yang kami sajikan adalah yang terbaik.</p>
            <ul class="about-features">
                <li><i class="fa-solid fa-check"></i> Biji kopi fresh roasted setiap minggu</li>
                <li><i class="fa-solid fa-check"></i> Tempat nyaman untuk kerja &amp; bersantai</li>
                <li><i class="fa-solid fa-check"></i> Wi-Fi cepat &amp; suasana Instagramable</li>
            </ul>
            <a href="#menu" class="btn"><i class="fa-solid fa-mug-hot"></i> Pesan Sekarang</a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <h2>Siap Menikmati Secangkir Kebahagiaan?</h2>
    <p>Pesan kopi favoritmu sekarang dan rasakan pengalaman ngopi yang tak terlupakan.</p>
    <a href="#menu" class="btn gold"><i class="fa-solid fa-bag-shopping"></i> Mulai Pesan</a>
</section>

@endsection

