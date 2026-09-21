@extends('admin.layouts.app')

@section('title','Detail Pesanan')
@section('page_title','Detail Pesanan')

@section('content')
<div style="margin-bottom:1rem">
    <a href="{{ route('admin.orders.index') }}" class="btn small secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</div>

<div class="two-col">
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-mug-hot"></i> Item Pesanan</h2>
            <span class="badge badge-{{ $order->order_type }}"><i class="fa-solid {{ $order->type_icon }}"></i> {{ $order->type_label }}</span>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Produk</th><th>Varian</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
                </thead>
                <tbody>
                    @foreach($order->items as $it)
                        <tr>
                            <td>
                                <div style="display:flex;gap:.75rem;align-items:center">
                                    @if(!empty($it['image']))
                                        <img src="{{ $it['image'] }}" class="thumb" alt="">
                                    @else
                                        <div class="thumb" style="display:flex;align-items:center;justify-content:center;color:var(--gold-dark)"><i class="fa-solid fa-mug-hot"></i></div>
                                    @endif
                                    <strong>{{ $it['name'] }}</strong>
                                </div>
                            </td>
                            <td>{{ ucfirst($it['variant'] ?? '-') }}</td>
                            <td>{{ $it['qty'] }}</td>
                            <td>Rp {{ number_format($it['price'],0,',','.') }}</td>
                            <td><strong>Rp {{ number_format($it['price']*$it['qty'],0,',','.') }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;font-weight:600">TOTAL</td>
                        <td style="font-size:1.15rem;font-weight:700;color:var(--gold-dark)">Rp {{ $order->total_rupiah }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($order->notes)
            <div style="margin-top:1.25rem;padding:1rem;background:var(--cream);border-radius:10px;border-left:4px solid var(--gold)">
                <strong><i class="fa-solid fa-note-sticky"></i> Catatan Pelanggan:</strong>
                <p style="margin:.4rem 0 0">{{ $order->notes }}</p>
            </div>
        @endif
    </div>

    <div>
        <div class="panel">
            <div class="panel-header">
                <h2 style="font-size:1.1rem"><i class="fa-solid fa-circle-info"></i> Info Pesanan</h2>
            </div>
            <ul class="info-list">
                <li><span class="lbl">Order ID</span><span class="val">{{ $order->order_id }}</span></li>
                <li><span class="lbl">Pelanggan</span><span class="val">{{ $order->customer_name }}</span></li>
                @if($order->phone)<li><span class="lbl">No. HP</span><span class="val">{{ $order->phone }}</span></li>@endif
                @if($order->order_type==='dine_in')
                    <li><span class="lbl">Nomor Meja</span><span class="val">{{ $order->table_number }}</span></li>
                @else
                    <li><span class="lbl">Waktu Ambil</span><span class="val">
                        @if($order->pickup_time)
                            <i class="fa-solid fa-clock"></i> {{ \Carbon\Carbon::parse($order->pickup_time)->format('H:i') }} WIB
                        @else
                            <i class="fa-solid fa-bolt"></i> Sekarang (± 15 menit)
                        @endif
                    </span></li>
                @endif
                <li><span class="lbl">Metode Bayar</span><span class="val">{{ $order->payment_method ?? '—' }}</span></li>
                <li><span class="lbl">Dibuat</span><span class="val">{{ $order->created_at->format('d M Y, H:i') }}</span></li>
                <li><span class="lbl">Status</span><span class="val"><span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span></span></li>
            </ul>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2 style="font-size:1.1rem"><i class="fa-solid fa-pen-to-square"></i> Update Status</h2>
            </div>
            <form method="POST" action="{{ route('admin.orders.status', $order->order_id) }}">
                @csrf @method('PATCH')
                <div class="form-group">
                    <label>Status Pesanan</label>
                    <select name="status">
                        @foreach(['pending'=>'Menunggu Pembayaran','paid'=>'Sudah Dibayar','processing'=>'Sedang Diproses','done'=>'Selesai','cancelled'=>'Dibatalkan','expired'=>'Kedaluwarsa','refunded'=>'Dana Dikembalikan'] as $k=>$v)
                            <option value="{{ $k }}" @selected($order->status===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn gold block" type="submit"><i class="fa-solid fa-check"></i> Simpan Status</button>
            </form>

            <hr style="margin:1.25rem 0;border:none;border-top:1px solid #f3f4f6">

            <form method="POST" action="{{ route('admin.orders.destroy', $order->order_id) }}"
                  onsubmit="return confirm('Hapus pesanan ini secara permanen?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn danger block"><i class="fa-solid fa-trash"></i> Hapus Pesanan</button>
            </form>
        </div>
    </div>
</div>
@endsection
