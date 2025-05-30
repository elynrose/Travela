<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Order Details</h2>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Order Status -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1">Order #{{ $order->order_number }}</h5>
                                <p class="text-muted small mb-0">
                                    Placed on {{ $order->created_at->format('F d, Y') }}
                                </p>
                            </div>
                            <span class="badge bg-{{ $order->payment_status === 'completed' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }} fs-6">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Itinerary Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Itinerary Details</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <img src="{{ Storage::url($order->itinerary->cover_image) }}" 
                                     alt="{{ $order->itinerary->title }}" 
                                     class="img-fluid rounded">
                            </div>
                            <div class="col-md-8">
                                <h4 class="mb-2">{{ $order->itinerary->title }}</h4>
                                <p class="text-muted mb-3">{{ Str::limit($order->itinerary->description, 200) }}</p>
                                
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="bi bi-clock me-2"></i>
                                            <span>{{ $order->itinerary->duration }} days</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="bi bi-geo-alt me-2"></i>
                                            <span>{{ $order->itinerary->location }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if($order->payment_status === 'completed')
                                    <div class="mt-4">
                                        <a href="{{ route('itineraries.days.show', $order->itinerary) }}" class="btn btn-primary me-2">
                                            <i class="bi bi-calendar3 me-2"></i>View Day-by-Day Itinerary
                                        </a>
                                        <a href="{{ route('itineraries.show', $order->itinerary) }}" class="btn btn-outline-primary">
                                            <i class="bi bi-info-circle me-2"></i>View Itinerary Overview
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Payment Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Payment Details</h5>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Amount</span>
                                <span class="fw-bold">${{ number_format($order->amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Platform Fee</span>
                                <span>${{ number_format($order->platform_fee, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Seller Amount</span>
                                <span>${{ number_format($order->seller_amount, 2) }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Total</span>
                                <span class="fw-bold">${{ number_format($order->amount, 2) }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Payment Method</span>
                                <span>{{ ucfirst($order->payment_method) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Payment Status</span>
                                <span class="badge bg-{{ $order->payment_status === 'completed' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                            @if($order->payment_intent_id)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Payment ID</span>
                                    <span class="text-truncate" style="max-width: 200px;">{{ $order->payment_intent_id }}</span>
                                </div>
                            @endif
                        </div>

                        @if($order->payment_status === 'completed')
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-2"></i>
                                Payment completed on {{ $order->updated_at->format('F d, Y') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Need Help? -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Need Help?</h5>
                        <p class="text-muted mb-3">If you have any questions about your order, please contact our support team.</p>
                        <a href="{{ route('contact') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-envelope me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
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