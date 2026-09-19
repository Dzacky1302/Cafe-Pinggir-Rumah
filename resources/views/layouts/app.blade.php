<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') — Brew &amp; Bean Café</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v=1">
    @stack('styles')
</head>
<body>
    <header class="navbar">
        <div class="nav-inner">
            <a href="{{ route('coffee.index') }}" class="brand">
                <i class="fa-solid fa-mug-hot"></i>
                <span>Brew &amp; Bean<small>Café · Est. 2024</small></span>
            </a>
            <button class="mobile-toggle" onclick="document.getElementById('navLinks').classList.toggle('open')">
                <i class="fa-solid fa-bars"></i>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="{{ route('coffee.index') }}" class="{{ request()->routeIs('coffee.index') ? 'active' : '' }}">Beranda</a></li>
                <li><a href="{{ route('coffee.index') }}#menu">Menu</a></li>
                <li><a href="{{ route('coffee.index') }}#tentang">Tentang</a></li>
                <li><a href="{{ route('coffee.index') }}#kontak">Kontak</a></li>
                <li>
                    @php $count = app(\App\Services\CartService::class)->count(); @endphp
                    <a href="{{ route('cart') }}" class="cart-btn">
                        <i class="fa-solid fa-bag-shopping"></i> Keranjang
                        <span class="badge">{{ $count }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer id="kontak">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3><i class="fa-solid fa-mug-hot"></i> Brew &amp; Bean</h3>
                <p>Menyajikan cita rasa kopi Nusantara pilihan yang diseduh dengan penuh cinta. Setiap tegukan adalah cerita.</p>
                <div class="socials">
                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Menu</h4>
                <ul>
                    <li><a href="{{ route('coffee.index') }}">Beranda</a></li>
                    <li><a href="{{ route('coffee.index') }}#menu">Katalog</a></li>
                    <li><a href="{{ route('cart') }}">Keranjang</a></li>
                    <li><a href="{{ route('admin.login') }}"><i class="fa-solid fa-user-shield"></i> Admin</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Jam Buka</h4>
                <p><i class="fa-regular fa-clock"></i> Sen – Jum<br>07.00 – 22.00</p>
                <p><i class="fa-regular fa-clock"></i> Sab – Min<br>08.00 – 23.00</p>
            </div>
            <div class="footer-col">
                <h4>Hubungi Kami</h4>
                <p><i class="fa-solid fa-location-dot"></i> Jl. Kopi Nikmat No. 24, Jakarta</p>
                <p><i class="fa-solid fa-phone"></i> +62 812 3456 7890</p>
                <p><i class="fa-solid fa-envelope"></i> hello@brewbean.id</p>
            </div>
        </div>
        <div class="footer-bottom">
            © {{ date('Y') }} Brew &amp; Bean Café. All rights reserved. Made with <i class="fa-solid fa-heart" style="color:var(--gold)"></i> in Indonesia.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

