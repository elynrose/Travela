<?php

namespace App\Http\Controllers;

use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\CheckAdmin::class)->except(['create', 'store', 'index']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payoutRequests = Auth::user()->payoutRequests()->latest()->paginate(10);
        return view('payout-requests.index', compact('payoutRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $availableBalance = Auth::user()->orders()
            ->where('payment_status', 'completed')
            ->sum('seller_amount');

        return view('payout-requests.create', compact('availableBalance'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'payment_details' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $availableBalance = Auth::user()->orders()
            ->where('payment_status', 'completed')
            ->sum('seller_amount');

        if ($request->amount > $availableBalance) {
            return back()->with('error', 'Requested amount exceeds available balance.');
        }

        $payoutRequest = Auth::user()->payoutRequests()->create([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_details' => $request->payment_details,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return redirect()->route('payout-requests.index')
            ->with('success', 'Payout request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PayoutRequest $payoutRequest)
    {
        $this->authorize('view', $payoutRequest);
        return view('payout-requests.show', compact('payoutRequest'));
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

    public function approve(PayoutRequest $payoutRequest)
    {
        $payoutRequest->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Payout request approved successfully.');
    }

    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $payoutRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Payout request rejected.');
    }
}
