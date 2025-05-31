<?php

namespace App\Http\Controllers;

use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayoutRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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
        $this->authorize('update', $payoutRequest);
        $payoutRequest->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Payout request approved successfully.');
    }

    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        $this->authorize('update', $payoutRequest);
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

    public function complete(PayoutRequest $payoutRequest)
    {
        $this->authorize('update', $payoutRequest);
        
        if ($payoutRequest->status !== 'approved') {
            return back()->with('error', 'Only approved payout requests can be marked as completed.');
        }

        try {
            DB::beginTransaction();

            // Update the payout request status
            $payoutRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Create a payout record
            $payoutRequest->user->payouts()->create([
                'amount' => $payoutRequest->amount,
                'payment_method' => $payoutRequest->payment_method,
                'payment_details' => $payoutRequest->payment_details,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Reduce the user's balance
            $user = $payoutRequest->user;
            $user->orders()
                ->where('payment_status', 'completed')
                ->where('seller_amount', '>', 0)
                ->orderBy('created_at')
                ->each(function ($order) use ($payoutRequest) {
                    if ($payoutRequest->amount <= 0) {
                        return false;
                    }

                    $deductionAmount = min($order->seller_amount, $payoutRequest->amount);
                    $order->update([
                        'seller_amount' => $order->seller_amount - $deductionAmount
                    ]);
                    $payoutRequest->amount -= $deductionAmount;
                });

            DB::commit();
            return back()->with('success', 'Payout request marked as completed and user balance has been updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete payout request. Please try again.');
        }
    }
}
