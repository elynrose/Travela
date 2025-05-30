<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Itinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = auth()->user()->orders()
            ->with(['itinerary.user']);

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

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'itinerary_id' => 'required|exists:itineraries,id'
        ]);

        $itinerary = Itinerary::findOrFail($request->itinerary_id);
        
        // Calculate platform fee (30%)
        $platformFee = $itinerary->price * 0.30;
        $sellerAmount = $itinerary->price - $platformFee;

        $order = Order::create([
            'user_id' => auth()->id(),
            'itinerary_id' => $itinerary->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'amount' => $itinerary->price,
            'platform_fee' => $platformFee,
            'seller_amount' => $sellerAmount,
            'currency' => 'USD',
            'payment_status' => 'pending',
            'payment_method' => 'stripe', // We'll implement Stripe later
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order created successfully. Please complete the payment.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['itinerary.user']);
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
