@extends('layouts.app')

@section('title','Detail Pesanan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}?v=1">
@endpush

@section('content')
<div class="checkout-wrap">

    <div class="checkout-steps">
        <div class="step done"><span class="num"><i class="fa-solid fa-check"></i></span> Tipe Pesanan</div>
        <div class="step active"><span class="num">2</span> Detail</div>
        <div class="step"><span class="num">3</span> Pembayaran</div>
    </div>

    <div style="text-align:center;margin-bottom:2rem">
        <span class="badge-type">
            <i class="fa-solid {{ $order_type === 'dine_in' ? 'fa-utensils' : 'fa-bag-shopping' }}"></i>
            {{ $order_type === 'dine_in' ? 'Dine In' : 'Take Away' }}
        </span>
        <h1 style="font-family:'Playfair Display',serif;color:var(--coffee-dark);font-size:2rem;margin:.75rem 0 .3rem">
            Isi Detail Pesananmu
        </h1>
        <p style="color:var(--text-muted)">Kami butuh sedikit info supaya pesananmu lancar</p>
    </div>

    <div class="checkout-grid">
        <div>
            @if($errors->any())
                <div class="errors-box">
                    <strong style="color:#991b1b"><i class="fa-solid fa-triangle-exclamation"></i> Ada kesalahan:</strong>
                    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('checkout.store') }}" class="form-card">
                @csrf
                <input type="hidden" name="order_type" value="{{ $order_type }}">

                <h3><i class="fa-solid fa-user"></i> Data Diri</h3>
                <p class="subtitle">Info kontak buat pesananmu</p>

                <div class="form-group">
                    <label>Nama Lengkap <span class="req">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Contoh: Budi Santoso" required>
                </div>

                @if($order_type === 'take_away')
                    <div class="form-group">
                        <label>Nomor HP / WhatsApp <span class="req">*</span></label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="0812-3456-7890" required>
                        <div class="hint">Kami akan hubungi jika pesanan sudah siap diambil</div>
                    </div>
                @endif

                @if($order_type === 'dine_in')
                    <h3 style="margin-top:2rem"><i class="fa-solid fa-chair"></i> Info Meja</h3>
                    <p class="subtitle">Beritahu kami di meja mana kamu duduk</p>

                    <div class="form-group">
                        <label>Nomor Meja <span class="req">*</span></label>
                        <input type="text" name="table_number" value="{{ old('table_number') }}" placeholder="Contoh: 12 atau A5" required>
                        <div class="hint"><i class="fa-solid fa-circle-info"></i> Lihat nomor di sudut meja kamu</div>
                    </div>
                @else
                    <h3 style="margin-top:2rem"><i class="fa-solid fa-clock"></i> Waktu Pengambilan</h3>
                    <p class="subtitle">Pilih jam kamu mau ambil pesanan</p>

                    <div class="form-group">
                        <label>Jam Pengambilan <span class="req">*</span></label>
                        <select name="pickup_time" required>
                            <option value="" disabled {{ old('pickup_time') ? '' : 'selected' }}>— Pilih jam pengambilan —</option>
                            <option value="now" {{ old('pickup_time') === 'now' ? 'selected' : '' }}>
                                ⚡ Sekarang (± 15 menit)
                            </option>
                            <optgroup label="━━ Jadwal Pengambilan ━━">
                                @foreach(['10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00'] as $slot)
                                    <option value="{{ $slot }}" {{ old('pickup_time') === $slot ? 'selected' : '' }}>
                                        🕐 {{ $slot }} WIB
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        <div class="hint"><i class="fa-solid fa-circle-info"></i> Jam buka 10:00 – 21:00 · Order terakhir jam 20:00</div>
                    </div>
                @endif


                <h3 style="margin-top:2rem"><i class="fa-solid fa-note-sticky"></i> Catatan (Opsional)</h3>
                <div class="form-group">
                    <textarea name="notes" placeholder="Contoh: Kopi jangan terlalu manis, es sedikit saja...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn gold block" style="margin-top:1rem">
                    <i class="fa-solid fa-arrow-right"></i> Lanjut ke Pembayaran
                </button>
                <a href="{{ route('checkout.type') }}" class="btn secondary block" style="margin-top:.6rem">
                    <i class="fa-solid fa-arrow-left"></i> Ganti Tipe Pesanan
                </a>
            </form>
        </div>

        <aside class="order-summary">
            <h3><i class="fa-solid fa-receipt"></i> Ringkasan</h3>
            @foreach($cart as $item)
                <div class="summary-item">
                    <div>
                        <div class="name">{{ $item['name'] }}</div>
                        <div class="qty-x">{{ $item['qty'] }} × Rp {{ number_format($item['price']/100,0,',','.') }}</div>
                    </div>
                    <div class="price">Rp {{ number_format(($item['price']*$item['qty'])/100,0,',','.') }}</div>
                </div>
            @endforeach
            <div class="summary-total">
                <span>Total</span>
                <span>Rp {{ number_format($total/100,0,',','.') }}</span>
            </div>
            <p style="margin-top:1rem;font-size:.8rem;color:var(--text-muted);text-align:center">
                <i class="fa-solid fa-shield-halved" style="color:var(--gold-dark)"></i> Pembayaran aman via Midtrans
            </p>
        </aside>
    </div>
</div>
@endsection
