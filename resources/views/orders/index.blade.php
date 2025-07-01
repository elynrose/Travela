<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ __('My Orders') }}</h2>
            <a href="{{ route('itineraries.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-plus me-2"></i>Browse Itineraries
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('orders.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-2"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders List -->
        <div class="card shadow-sm">
            <div class="card-body">
                @forelse($orders as $order)
                    <div class="card mb-3 border-0 bg-light">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Order Image -->
                                <div class="col-md-2">
                                    @if($order->itinerary->cover_image)
                                        <img src="{{ Storage::url($order->itinerary->cover_image) }}" 
                                             alt="{{ $order->itinerary->title }}" 
                                             class="img-fluid rounded" 
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    @endif
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="height: 100px; width: 100%; display: {{ $order->itinerary->cover_image ? 'none' : 'flex' }};">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                </div>

                                <!-- Order Details -->
                                <div class="col-md-4">
                                    <h5 class="card-title mb-1">
                                        <a href="{{ route('orders.show', $order) }}" class="text-decoration-none">
                                            {{ $order->itinerary->title }}
                                        </a>
                                    </h5>
                                    <p class="text-muted small mb-2">
                                        Order #{{ $order->order_number }}
                                    </p>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-person me-2"></i>
                                        {{ $order->itinerary->user->name }}
                                    </div>
                                </div>

                                <!-- Order Info -->
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <span class="badge bg-{{ $order->payment_status === 'completed' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar me-1"></i>
                                        {{ $order->created_at->format('M d, Y') }}
                                    </div>
                                </div>

                                <!-- Price and Actions -->
                                <div class="col-md-3 text-end">
                                    <div class="h5 mb-2">${{ number_format($order->amount, 2) }}</div>
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No orders found</h5>
                        <p class="text-muted mb-4">Start exploring our itineraries to make your first purchase.</p>
                        <a href="{{ route('itineraries.index') }}" class="btn btn-primary">
                            <i class="bi bi-compass me-2"></i>Browse Itineraries
                        </a>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .badge {
            font-size: 0.875rem;
            padding: 0.5em 0.75em;
        }
    </style>
    @endpush
</x-app-layout> 