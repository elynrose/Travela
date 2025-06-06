<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'itinerary'])
            ->latest()
            ->paginate(10);

        $totalCount = Order::count();
        $completedCount = Order::where('payment_status', 'completed')->count();
        $pendingCount = Order::where('payment_status', 'pending')->count();
        $failedCount = Order::where('payment_status', 'failed')->count();

        return view('admin.orders.index', compact('orders', 'totalCount', 'completedCount', 'pendingCount', 'failedCount'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'itinerary']);
        
        return view('admin.orders.show', compact('order'));
    }
}
