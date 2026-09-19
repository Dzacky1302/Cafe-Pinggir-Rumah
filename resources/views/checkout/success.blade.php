@extends('layouts.app')

@section('title','Status Pesanan')

@php
    // Halaman ini hanya membaca status. Status 'paid' hanya boleh datang dari
    // notifikasi Midtrans yang signature-nya sudah terverifikasi.
    $isPaid = in_array($order->status, ['paid', 'processing', 'done'], true);
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}?v=1">
@endpush

@section('content')
<div class="success-wrap">
    <div class="success-card">
        <div class="success-icon">
            <i class="fa-solid {{ $isPaid ? 'fa-check' : 'fa-hourglass-half' }}"></i>
        </div>

        @if($isPaid)
            <h1>Pembayaran Berhasil! 🎉</h1>
            <p>Terima kasih {{ $order->customer_name }}, pesananmu sudah kami terima dan akan segera diproses.</p>
        @else
            <h1>Menunggu Pembayaran</h1>
            <p>
                Halo {{ $order->customer_name }}, konfirmasi pembayaran untuk pesanan ini belum kami terima.
                Status akan berubah otomatis begitu Midtrans mengonfirmasinya.
            </p>
        @endif

        <div class="order-detail-box">
            <div class="id">{{ $order->order_id }}</div>

            <div class="row">
                <span class="lbl">Tipe Pesanan</span>
                <span class="val">
                    <span class="badge-type" style="font-size:.75rem">
                        <i class="fa-solid {{ $order->type_icon }}"></i> {{ $order->type_label }}
                    </span>
                </span>
            </div>

            @if($order->order_type === 'dine_in')
                <div class="row"><span class="lbl">Nomor Meja</span><span class="val">{{ $order->table_number }}</span></div>
            @else
                <div class="row"><span class="lbl">Nomor HP</span><span class="val">{{ $order->phone }}</span></div>
                <div class="row">
                    <span class="lbl">Waktu Ambil</span>
                    <span class="val">
                        @if($order->pickup_time)
                            <i class="fa-solid fa-clock"></i> {{ \Carbon\Carbon::parse($order->pickup_time)->format('H:i') }} WIB
                        @else
                            <i class="fa-solid fa-bolt"></i> Sekarang (± 15 menit)
                        @endif
                    </span>
                </div>
            @endif

            @if($order->notes)
                <div class="row"><span class="lbl">Catatan</span><span class="val" style="text-align:right;max-width:60%">{{ $order->notes }}</span></div>
            @endif

            <div class="row" style="border-top:2px dashed rgba(60,42,26,.15);margin-top:.75rem;padding-top:.75rem">
                <span class="lbl">Total Bayar</span>
                <span class="val" style="color:var(--gold-dark);font-size:1.1rem">Rp {{ $order->total_rupiah }}</span>
            </div>
            <div class="row">
                <span class="lbl">Status</span>
                <span class="val" style="color:{{ $order->status_color }}">
                    <i class="fa-solid fa-circle" style="font-size:.6rem"></i> {{ $order->status_label }}
                </span>
            </div>
        </div>

        <div style="background:#fff9ec;border-radius:12px;padding:1rem 1.25rem;text-align:left;font-size:.88rem;color:#78350f;line-height:1.6">
            <strong><i class="fa-solid fa-circle-info"></i> Info:</strong>
            @if(! $isPaid)
                Selesaikan pembayaranmu melalui popup Midtrans. Bila popup sudah tertutup, buka kembali
                halaman pembayaran. Halaman ini menyegarkan sendiri sampai pembayaran terkonfirmasi.
            @elseif($order->order_type === 'dine_in')
                Barista akan mengantar pesananmu ke meja <strong>{{ $order->table_number }}</strong>. Silakan tunggu dengan nyaman ya!
            @else
                Simpan nomor pesanan di atas. Tunjukkan ke kasir saat mengambil pesananmu.
            @endif
        </div>

        <div class="btn-group">
            <a href="{{ route('coffee.index') }}" class="btn gold">
                <i class="fa-solid fa-mug-hot"></i> Pesan Lagi
            </a>
            <a href="{{ route('coffee.index') }}" class="btn secondary">
                <i class="fa-solid fa-home"></i> Ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection

@unless($isPaid)
    @push('scripts')
    <script>
        /* Muat ulang terbatas sampai notifikasi Midtrans tiba. Dibatasi 6 kali
           agar tidak menjadi polling tanpa henti bila pembayaran ditinggalkan. */
        (function () {
            var tries = Number(sessionStorage.getItem('paymentPoll') || 0);
            if (tries < 6) {
                sessionStorage.setItem('paymentPoll', tries + 1);
                setTimeout(function () { window.location.reload(); }, 5000);
            }
        })();
    </script>
    @endpush
@endunless
