<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class PayoutRequestController extends Controller
{
    public function index()
    {
        $payoutRequests = PayoutRequest::with('user')
            ->latest()
            ->paginate(10);

        return view('admin.payout-requests.index', compact('payoutRequests'));
    }

    public function approve(PayoutRequest $payoutRequest)
    {
        $payoutRequest->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);

        return redirect()->route('admin.payout-requests.index')
            ->with('success', 'Payout request has been approved.');
    }

    public function reject(PayoutRequest $payoutRequest)
    {
        $payoutRequest->update([
            'status' => 'rejected',
            'processed_at' => now(),
        ]);

        return redirect()->route('admin.payout-requests.index')
            ->with('success', 'Payout request has been rejected.');
    }
}
