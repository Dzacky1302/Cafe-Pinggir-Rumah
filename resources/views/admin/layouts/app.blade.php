<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Brew & Bean</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=2">
    @stack('styles')
</head>
<body class="admin">
    <div class="admin-shell">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-mug-hot"></i>
                <div>
                    <h2>Brew &amp; Bean</h2>
                    <small>Admin Panel</small>
                </div>
            </div>

            <ul class="sidebar-nav">
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a></li>
                <li><a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                    <i class="fa-solid fa-receipt"></i> Pesanan
                </a></li>
                <li><a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                    <i class="fa-solid fa-mug-saucer"></i> Menu / Produk
                </a></li>
                <li><a href="{{ route('coffee.index') }}" target="_blank">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Website
                </a></li>
            </ul>

            <div class="sidebar-footer">
                <i class="fa-solid fa-copyright"></i> {{ date('Y') }} Brew & Bean Café
            </div>
        </aside>

        <div>
            <header class="admin-topbar">
                <div style="display:flex;align-items:center;gap:1rem">
                    <button class="toggle-sidebar" onclick="document.getElementById('sidebar').classList.toggle('open')">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1>@yield('page_title', 'Dashboard')</h1>
                </div>
                <div class="topbar-actions">
                    <div class="user-menu">
                        <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}</div>
                        <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="btn small danger" type="submit"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
                    </form>
                </div>
            </header>

            <main class="admin-content">
                @if(session('success'))
                    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
