<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Itinerary;
use App\Models\Order;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\CheckAdmin::class);
    }

    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_itineraries' => Itinerary::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('payment_status', 'completed')->sum('amount'),
            'pending_payouts' => PayoutRequest::where('status', 'pending')->count(),
        ];

        $recent_users = User::latest()->take(5)->get();
        $recent_orders = Order::with(['user', 'itinerary'])->latest()->take(5)->get();
        $pending_payouts = PayoutRequest::with('user')->where('status', 'pending')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_orders', 'pending_payouts'));
    }
}
