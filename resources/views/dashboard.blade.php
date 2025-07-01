<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Dashboard</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('itineraries.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>New Itinerary
                </a>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <!-- Total Sales -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-journal-check text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Total Sales</h6>
                                <h3 class="mb-0">{{ auth()->user()->total_sales }}</h3>
                                <small class="text-muted">Number of itineraries sold</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Balance -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-wallet2 text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Available Balance</h6>
                                <h3 class="mb-0">${{ number_format(auth()->user()->available_balance, 2) }}</h3>
                                <small class="text-muted">Total sales minus 30% platform fee and withdrawals</small>
                                <div class="mt-2">
                                    <a href="{{ route('payout-requests.create') }}" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-cash me-1"></i>Request Payout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Itineraries -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-map text-info fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Total Itineraries</h6>
                                <h3 class="mb-0">{{ auth()->user()->itineraries()->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row g-4">
            <!-- Recent Itineraries -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Itineraries</h5>
                            <a href="{{ route('itineraries.index') }}" class="btn btn-sm btn-link text-primary">
                                View More <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @forelse($itineraries as $itinerary)
                                <a href="{{ route('itineraries.show', $itinerary) }}" class="list-group-item list-group-item-action border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        @if($itinerary->cover_image && $itinerary->getCoverThumbUrl())
                                            <img src="{{ $itinerary->getCoverThumbUrl() }}" 
                                                 alt="{{ $itinerary->title }}" 
                                                 class="rounded-3 me-3" 
                                                 style="width: 48px; height: 48px; object-fit: cover;">
                                        @else
                                            <div class="rounded-3 bg-light me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 48px; height: 48px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 text-truncate">{{ $itinerary->title }}</h6>
                                            <small class="text-muted">{{ $itinerary->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-map text-muted fs-1"></i>
                                    <p class="text-muted mt-2">No itineraries yet</p>
                                    <a href="{{ route('itineraries.create') }}" class="btn btn-sm btn-primary">Create One</a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Orders Received</h5>
                            <a href="{{ route('received-orders.index') }}" class="btn btn-sm btn-link text-primary">
                                View More <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @forelse($receivedOrders as $order)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-success bg-opacity-10 rounded-3 p-2 me-3">
                                            <i class="bi bi-check-circle text-success"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Order #{{ $order->order_number }}</h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                                <span class="badge bg-{{ $order->payment_status === 'completed' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                {{ $order->user->name }} purchased {{ Str::limit($order->itinerary->title, 30) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-cart text-muted fs-1"></i>
                                    <p class="text-muted mt-2">No orders received yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Payouts -->
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Payouts</h5>
                            <a href="{{ route('payouts.index') }}" class="btn btn-sm btn-link text-primary">
                                View More <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @forelse($payouts as $payout)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                            <i class="bi bi-wallet2 text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Payout #{{ $payout->id }}</h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">{{ $payout->created_at->diffForHumans() }}</small>
                                                <span class="badge bg-primary">${{ number_format($payout->amount, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-wallet2 text-muted fs-1"></i>
                                    <p class="text-muted mt-2">No payouts yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
