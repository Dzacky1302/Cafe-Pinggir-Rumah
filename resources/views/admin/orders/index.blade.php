@extends('admin.layouts.app')

@section('title','Kelola Pesanan')
@section('page_title','Kelola Pesanan')

@section('content')
<div class="panel">
    <div class="panel-header">
        <h2><i class="fa-solid fa-receipt"></i> Semua Pesanan ({{ $orders->total() }})</h2>
        <form class="filter-bar" method="GET">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari order ID / nama...">
            <select name="type">
                <option value="">Semua Tipe</option>
                <option value="dine_in" @selected(request('type')==='dine_in')>Dine In</option>
                <option value="take_away" @selected(request('type')==='take_away')>Take Away</option>
            </select>
            <select name="status">
                <option value="">Semua Status</option>
                @foreach(['pending'=>'Menunggu','paid'=>'Dibayar','processing'=>'Diproses','done'=>'Selesai','cancelled'=>'Batal','expired'=>'Kedaluwarsa','refunded'=>'Refund'] as $k=>$v)
                    <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn small" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
            <a href="{{ route('admin.orders.index') }}" class="btn small secondary">Reset</a>
        </form>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pelanggan</th>
                    <th>Tipe</th>
                    <th>Detail</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td class="order-id">{{ $o->order_id }}</td>
                        <td>
                            {{ $o->customer_name }}
                            @if($o->phone)<br><small style="color:var(--text-muted)"><i class="fa-solid fa-phone"></i> {{ $o->phone }}</small>@endif
                        </td>
                        <td><span class="badge badge-{{ $o->order_type }}"><i class="fa-solid {{ $o->type_icon }}"></i> {{ $o->type_label }}</span></td>
                        <td style="font-size:.85rem">
                            @if($o->order_type==='dine_in')
                                Meja <strong>{{ $o->table_number }}</strong> @if($o->person_count) · {{ $o->person_count }} org @endif
                            @else
                                Ambil: @if($o->pickup_time) <strong>{{ \Carbon\Carbon::parse($o->pickup_time)->format('H:i') }}</strong> @else <em>ASAP</em> @endif
                            @endif
                        </td>
                        <td><strong>Rp {{ $o->total_rupiah }}</strong></td>
                        <td><span class="badge badge-{{ $o->status }}">{{ $o->status_label }}</span></td>
                        <td style="font-size:.82rem;color:var(--text-muted)">
                            {{ $o->created_at->format('d M Y') }}<br>{{ $o->created_at->format('H:i') }}
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $o->order_id) }}" class="btn small info" title="Detail"><i class="fa-solid fa-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted)">
                        <i class="fa-solid fa-inbox" style="font-size:2.5rem;opacity:.3"></i>
                        <p style="margin-top:1rem">Belum ada pesanan sesuai filter.</p>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $orders->links() }}
    </div>
</div>
@endsection
