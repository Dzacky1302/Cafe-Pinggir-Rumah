<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login Admin — Brew & Bean</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=1">
</head>
<body class="admin">
    <div class="login-wrap">
        <div class="login-card">
            <div class="login-brand">
                <i class="fa-solid fa-mug-hot"></i>
                <h1>Brew &amp; Bean Admin</h1>
                <p>Silakan login untuk mengakses panel</p>
            </div>

            @if(session('error'))
                <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label><i class="fa-solid fa-envelope"></i> Email</label>
                    <input type="email" name="email" value="{{ old('email','admin@brewbean.id') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-lock"></i> Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;font-size:.88rem">
                    <label style="display:flex;gap:.4rem;align-items:center;cursor:pointer">
                        <input type="checkbox" name="remember" value="1"> Ingat saya
                    </label>
                    <a href="{{ route('coffee.index') }}" style="color:var(--text-muted);text-decoration:none">
                        <i class="fa-solid fa-arrow-left"></i> Beranda
                    </a>
                </div>
                <button type="submit" class="btn gold block" style="padding:.9rem">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </button>
            </form>

            <div style="margin-top:1.5rem;padding:1rem;background:var(--cream);border-radius:10px;font-size:.82rem;color:var(--coffee-dark)">
                <strong><i class="fa-solid fa-info-circle"></i> Kredensial demo:</strong><br>
                Email: <code>admin@brewbean.id</code><br>
                Password: <code>admin123</code>
            </div>
        </div>
    </div>
</body>
</html>
