<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = PayoutRequest::with('user')
            ->latest()
            ->paginate(10);

        $totalCount = PayoutRequest::count();
        $pendingCount = PayoutRequest::where('status', 'pending')->count();
        $approvedCount = PayoutRequest::where('status', 'approved')->count();
        $rejectedCount = PayoutRequest::where('status', 'rejected')->count();

        return view('admin.payouts.index', compact('payouts', 'totalCount', 'pendingCount', 'approvedCount', 'rejectedCount'));
    }

    public function approve(PayoutRequest $payoutRequest)
    {
        $payoutRequest->update([
            'status' => 'approved'
        ]);

        return back()->with('success', 'Payout request approved successfully.');
    }

    public function reject(PayoutRequest $payoutRequest)
    {
        $payoutRequest->update([
            'status' => 'rejected'
        ]);

        return back()->with('success', 'Payout request rejected successfully.');
    }
} 