<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Payout Requests</h2>
            <a href="{{ route('payout-requests.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Request Payout
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                @forelse($payoutRequests as $request)
                    <div class="border-bottom py-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h5 class="mb-1">${{ number_format($request->amount, 2) }}</h5>
                                <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Payment Method</small>
                                {{ ucfirst($request->payment_method) }}
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Requested On</small>
                                {{ $request->created_at->format('M d, Y') }}
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="{{ route('payout-requests.show', $request) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-wallet2 display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No payout requests yet</h5>
                        <p class="text-muted mb-4">Request a payout when you have available balance.</p>
                        <a href="{{ route('payout-requests.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Request Payout
                        </a>
                    </div>
                @endforelse

                @if($payoutRequests->hasPages())
                    <div class="mt-4">
                        {{ $payoutRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout> 