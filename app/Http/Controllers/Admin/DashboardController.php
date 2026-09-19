<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();

        $stats = [
            'orders_today'  => Order::where('created_at', '>=', $today)->count(),
            'revenue_today' => (int) Order::where('created_at', '>=', $today)
                                    ->whereIn('status', ['paid', 'processing', 'done'])
                                    ->sum('total'),
            'pending'       => Order::where('status', 'pending')->count(),
            'processing'    => Order::where('status', 'processing')->count(),
            'total_orders'  => Order::count(),
            'total_products'=> Product::count(),
        ];

        $recent = Order::latest()->limit(8)->get();

        // Chart last 7 days
        $chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $next = (clone $day)->addDay();
            $chart[] = [
                'label' => $day->isoFormat('DD MMM'),
                'count' => Order::whereBetween('created_at', [$day, $next])->count(),
            ];
        }

        return view('admin.dashboard', compact('stats', 'recent', 'chart'));
    }
}
