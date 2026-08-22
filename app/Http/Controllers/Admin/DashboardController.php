<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_revenue' => Order::where('status', 'paid')->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_payments' => Payment::where('status', 'completed')->count(),
        ];

        $recentOrders = Order::latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
