<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Payout Request Details</h2>
            <a href="{{ route('payout-requests.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Requests
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Request #{{ $payoutRequest->id }}</h5>
                            <span class="badge bg-{{ $payoutRequest->status === 'completed' ? 'success' : ($payoutRequest->status === 'approved' ? 'info' : ($payoutRequest->status === 'rejected' ? 'danger' : 'warning')) }}">
                                {{ ucfirst($payoutRequest->status) }}
                            </span>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Amount</label>
                                    <h4>${{ number_format($payoutRequest->amount, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Requested On</label>
                                    <p class="mb-0">{{ $payoutRequest->created_at->format('F d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Payment Method</label>
                            <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $payoutRequest->payment_method)) }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Payment Details</label>
                            <p class="mb-0">{{ $payoutRequest->payment_details }}</p>
                        </div>

                        @if($payoutRequest->notes)
                            <div class="mb-3">
                                <label class="form-label text-muted">Additional Notes</label>
                                <p class="mb-0">{{ $payoutRequest->notes }}</p>
                            </div>
                        @endif

                        @if($payoutRequest->admin_notes)
                            <div class="mb-3">
                                <label class="form-label text-muted">Admin Notes</label>
                                <p class="mb-0">{{ $payoutRequest->admin_notes }}</p>
                            </div>
                        @endif

                        @if($payoutRequest->processed_at)
                            <div class="mb-3">
                                <label class="form-label text-muted">Processed On</label>
                                <p class="mb-0">{{ $payoutRequest->processed_at->format('F d, Y H:i') }}</p>
                            </div>
                        @endif

                        @if($payoutRequest->completed_at)
                            <div class="mb-3">
                                <label class="form-label text-muted">Completed On</label>
                                <p class="mb-0">{{ $payoutRequest->completed_at->format('F d, Y H:i') }}</p>
                            </div>
                        @endif

                        @if($payoutRequest->isPending() && auth()->user()->isAdmin())
                            <div class="d-flex gap-2 mt-4">
                                <form action="{{ route('payout-requests.approve', $payoutRequest) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg me-2"></i>Approve
                                    </button>
                                </form>

                                <button type="button" 
                                        class="btn btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal">
                                    <i class="bi bi-x-lg me-2"></i>Reject
                                </button>
                            </div>
                        @endif

                        @if($payoutRequest->status === 'approved' && auth()->user()->isAdmin())
                            <div class="mt-4">
                                <form action="{{ route('payout-requests.complete', $payoutRequest) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>Mark as Completed
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($payoutRequest->isPending() && auth()->user()->isAdmin())
        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('payout-requests.reject', $payoutRequest) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Reject Payout Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="admin_notes" class="form-label">Reason for Rejection</label>
                                <textarea class="form-control" 
                                          id="admin_notes" 
                                          name="admin_notes" 
                                          rows="3" 
                                          required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-app-layout> 