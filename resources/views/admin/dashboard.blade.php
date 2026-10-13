@extends('admin.layouts.app')

@section('title','Dashboard')
@section('page_title','Dashboard')

@section('content')

    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="info">
                <div class="lbl">Pesanan Hari Ini</div>
                <div class="val">{{ $stats['orders_today'] }}</div>
            </div>
            <div class="icon"><i class="fa-solid fa-cart-shopping"></i></div>
        </div>
        <div class="stat-card green">
            <div class="info">
                <div class="lbl">Pendapatan Hari Ini</div>
                <div class="val">Rp {{ number_format($stats['revenue_today'],0,',','.') }}</div>
            </div>
            <div class="icon"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>
        <div class="stat-card orange">
            <div class="info">
                <div class="lbl">Menunggu Bayar</div>
                <div class="val">{{ $stats['pending'] }}</div>
            </div>
            <div class="icon"><i class="fa-solid fa-clock"></i></div>
        </div>
        <div class="stat-card purple">
            <div class="info">
                <div class="lbl">Sedang Diproses</div>
                <div class="val">{{ $stats['processing'] }}</div>
            </div>
            <div class="icon"><i class="fa-solid fa-fire-burner"></i></div>
        </div>
        <div class="stat-card gold">
            <div class="info">
                <div class="lbl">Total Pesanan</div>
                <div class="val">{{ $stats['total_orders'] }}</div>
            </div>
            <div class="icon"><i class="fa-solid fa-list-check"></i></div>
        </div>
        <div class="stat-card red">
            <div class="info">
                <div class="lbl">Total Produk</div>
                <div class="val">{{ $stats['total_products'] }}</div>
            </div>
            <div class="icon"><i class="fa-solid fa-mug-saucer"></i></div>
        </div>
    </div>

    <div class="two-col">
        <div class="panel">
            <div class="panel-header">
                <h2><i class="fa-solid fa-chart-column"></i> Pesanan 7 Hari Terakhir</h2>
            </div>
            <div class="chart-box">
                <canvas id="ordersChart" height="240"></canvas>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2><i class="fa-solid fa-bolt"></i> Aksi Cepat</h2>
            </div>
            <div style="display:flex;flex-direction:column;gap:.6rem">
                <a href="{{ route('admin.orders.index') }}" class="btn info block"><i class="fa-solid fa-receipt"></i> Kelola Pesanan</a>
                <a href="{{ route('admin.products.create') }}" class="btn gold block"><i class="fa-solid fa-plus"></i> Tambah Produk Baru</a>
                <a href="{{ route('admin.products.index') }}" class="btn secondary block"><i class="fa-solid fa-mug-saucer"></i> Kelola Menu</a>
                <a href="{{ route('coffee.index') }}" target="_blank" class="btn secondary block"><i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Website</a>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-clock-rotate-left"></i> Pesanan Terbaru</h2>
            <a href="{{ route('admin.orders.index') }}" class="btn small secondary">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Pelanggan</th>
                        <th>Tipe</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent as $o)
                        <tr>
                            <td class="order-id">{{ $o->order_id }}</td>
                            <td>{{ $o->customer_name }}</td>
                            <td><span class="badge badge-{{ $o->order_type }}"><i class="fa-solid {{ $o->type_icon }}"></i> {{ $o->type_label }}</span></td>
                            <td><strong>Rp {{ $o->total_rupiah }}</strong></td>
                            <td><span class="badge badge-{{ $o->status }}">{{ $o->status_label }}</span></td>
                            <td>{{ $o->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $o->order_id) }}" class="btn small info"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var el = document.getElementById('ordersChart');
            if (!el || typeof Chart === 'undefined') return;
            var labels = @json(array_column($chart, 'label'));
            var counts = @json(array_column($chart, 'count'));
            var ctx = el.getContext('2d');
            var grad = ctx.createLinearGradient(0, 0, 0, 240);
            grad.addColorStop(0, 'rgba(212,163,63,0.85)');
            grad.addColorStop(1, 'rgba(212,163,63,0.15)');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pesanan',
                        data: counts,
                        backgroundColor: grad,
                        hoverBackgroundColor: '#b0862a',
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 42
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#2a1c10',
                            titleColor: '#d4a33f',
                            bodyColor: '#f6efe1',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: function (c) { return ' ' + c.parsed.y + ' pesanan'; }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#6b7280', font: { family: 'Poppins', size: 12 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            border: { display: false },
                            ticks: { color: '#6b7280', precision: 0, font: { family: 'Poppins', size: 12 } }
                        }
                    },
                    animation: { duration: 900, easing: 'easeOutQuart' }
                }
            });
        })();
    </script>
    @endpush
@endsection
