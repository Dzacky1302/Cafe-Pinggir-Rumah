@extends('layouts.app')

@section('title','Pembayaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cart.css') }}?v=1">
@endpush

@section('content')
<section class="page-header">
    <h1>Selesaikan Pembayaran</h1>
    <div class="breadcrumb"><a href="{{ route('coffee.index') }}">Beranda</a> / <a href="{{ route('cart') }}">Keranjang</a> / Pembayaran</div>
</section>

<div class="steps">
    <div class="step done"><div class="step-num"><i class="fa-solid fa-check"></i></div><p>Keranjang</p></div>
    <div class="step active"><div class="step-num">2</div><p>Pembayaran</p></div>
    <div class="step"><div class="step-num">3</div><p>Selesai</p></div>
</div>

<div class="pay-wrap">
    <div class="pay-card">
        <h2><i class="fa-solid fa-credit-card"></i> Metode Pembayaran</h2>

        @if(session('error'))
            <div class="alert error"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        @if(empty($cart))
            <div style="text-align:center;padding:2rem 0">
                <i class="fa-solid fa-mug-saucer" style="font-size:3.5rem;color:var(--cream-200)"></i>
                <h3 style="margin-top:1rem">Tidak Ada Pesanan</h3>
                <p style="color:var(--text-muted);margin:.5rem 0 1.25rem">Keranjang belanjamu kosong.</p>
                <a href="{{ route('coffee.index') }}" class="btn"><i class="fa-solid fa-mug-hot"></i> Kembali ke Menu</a>
            </div>
        @else
            <div class="pay-methods">
                <div class="pay-method active">
                    <i class="fa-solid fa-bolt"></i><span>Midtrans Snap</span>
                </div>
                <div class="pay-method">
                    <i class="fa-solid fa-building-columns"></i><span>Transfer Bank</span>
                </div>
                <div class="pay-method">
                    <i class="fa-solid fa-qrcode"></i><span>QRIS / E-Wallet</span>
                </div>
            </div>

            <p style="color:var(--text-muted);font-size:.92rem;margin-bottom:1.25rem">
                <i class="fa-solid fa-info-circle" style="color:var(--gold-dark)"></i>
                Klik tombol di bawah untuk melanjutkan pembayaran melalui <strong>Midtrans</strong> — mendukung berbagai bank, e-wallet, dan QRIS.
            </p>

            <form method="POST" action="{{ route('checkout.charge', $order->order_id) }}">
                @csrf
                <button class="btn gold block" type="submit" style="padding:1rem">
                    <i class="fa-solid fa-lock"></i> Bayar Sekarang — Rp {{ number_format($total/100,0,',','.') }}
                </button>
            </form>

            @includeWhen(!empty($midtrans), 'partials.midtrans-box', ['midtrans' => $midtrans ?? [], 'order' => $order])

            <div class="security-badges">
                <span class="sb"><i class="fa-solid fa-shield-halved"></i> SSL Encrypted</span>
                <span class="sb"><i class="fa-solid fa-lock"></i> PCI DSS Compliant</span>
                <span class="sb"><i class="fa-solid fa-user-shield"></i> Data Aman</span>
            </div>
        @endif
    </div>

    @if(!empty($cart))
    <aside class="pay-summary">
        <h2 style="font-size:1.25rem;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px solid var(--cream-200);display:flex;align-items:center;gap:.5rem">
            <i class="fa-solid fa-receipt" style="color:var(--gold-dark)"></i> Detail Pesanan
        </h2>

        @foreach($cart as $item)
            <div class="order-item">
                <div class="name">
                    {{ $item['name'] }}
                    <small>{{ ucfirst($item['variant'] ?? 'Regular') }} × {{ $item['qty'] }}</small>
                </div>
                <div class="amt">Rp {{ number_format(($item['price']*$item['qty'])/100,0,',','.') }}</div>
            </div>
        @endforeach

        <div class="summary-row" style="margin-top:.5rem"><span>Subtotal</span><span>Rp {{ number_format($total/100,0,',','.') }}</span></div>
        <div class="summary-row"><span>Ongkir</span><span style="color:#1e6b3a;font-weight:600">Gratis</span></div>
        <div class="summary-row"><span>Biaya Layanan</span><span>Rp 0</span></div>

        <div class="summary-row total">
            <span>Total</span>
            <span>Rp {{ number_format($total/100,0,',','.') }}</span>
        </div>

        <div style="margin-top:1rem;padding:.75rem 1rem;background:var(--cream);border-radius:10px;font-size:.85rem">
            <div><strong>Order ID:</strong> {{ $order->order_id }}</div>
            <div><strong>Tipe:</strong>
                <i class="fa-solid {{ $order->type_icon }}" style="color:var(--gold-dark)"></i>
                {{ $order->type_label }}
            </div>
            <div><strong>Atas Nama:</strong> {{ $order->customer_name }}</div>
            @if($order->order_type === 'dine_in')
                <div><strong>Meja:</strong> {{ $order->table_number }}</div>
            @else
                <div><strong>HP:</strong> {{ $order->phone }}</div>
            @endif
        </div>

        <a href="{{ route('cart') }}" class="btn secondary block" style="margin-top:1rem">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Keranjang
        </a>
    </aside>
    @endif
</div>
@endsection

