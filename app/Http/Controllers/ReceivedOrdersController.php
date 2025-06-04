<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ReceivedOrdersController extends Controller
{
    public function index()
    {
        $query = Order::whereHas('itinerary', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['user', 'itinerary']);

        // Apply date range filter
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        // Apply status filter
        if (request('status')) {
            $query->where('payment_status', request('status'));
        }

        $orders = $query->latest()->paginate(10);

        return view('orders.received', compact('orders'));
    }

    public function show(Order $order)
    {
        // Ensure the order belongs to one of the user's itineraries
        if ($order->itinerary->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['user', 'itinerary']);
        return view('orders.received-show', compact('order'));
    }

    public function complete(Order $order)
    {
        // Ensure the order belongs to one of the user's itineraries
        if ($order->itinerary->user_id !== auth()->id()) {
            abort(403);
        }

        // Only allow completing pending orders
        if ($order->payment_status !== 'pending') {
            return back()->with('error', 'Only pending orders can be marked as completed.');
        }

        $order->update([
            'payment_status' => 'completed'
        ]);

        return back()->with('success', 'Order marked as completed successfully.');
    }
} 