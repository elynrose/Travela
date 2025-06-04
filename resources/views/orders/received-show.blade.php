<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Order Details</h2>
            <a href="{{ route('received-orders.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Order Information -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Order #{{ $order->order_number }}</h5>
                            <div class="d-flex align-items-center gap-2">
                                @if($order->payment_status === 'pending')
                                    <form action="{{ route('received-orders.complete', $order) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-2"></i>Mark as Completed
                                        </button>
                                    </form>
                                @endif
                                <span class="badge bg-{{ $order->payment_status === 'completed' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Customer Information</h6>
                                <p class="mb-1"><strong>Name:</strong> {{ $order->user->name }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $order->user->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Order Information</h6>
                                <p class="mb-1"><strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                                <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Itinerary Details</h6>
                            <div class="d-flex align-items-center">
                                @if($order->itinerary->getCoverImageUrl())
                                    <img src="{{ $order->itinerary->getCoverThumbUrl() }}" 
                                         alt="{{ $order->itinerary->title }}" 
                                         class="rounded-3 me-3" 
                                         style="width: 64px; height: 64px; object-fit: cover;">
                                @endif
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('itineraries.show', $order->itinerary) }}" class="text-decoration-none">
                                            {{ $order->itinerary->title }}
                                        </a>
                                    </h6>
                                    <p class="text-muted mb-0">{{ Str::limit($order->itinerary->description, 100) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Itinerary Price</td>
                                        <td class="text-end">${{ number_format($order->amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Platform Fee (30%)</td>
                                        <td class="text-end">${{ number_format($order->platform_fee, 2) }}</td>
                                    </tr>
                                    <tr class="table-light">
                                        <td><strong>Your Earnings</strong></td>
                                        <td class="text-end"><strong>${{ number_format($order->seller_amount, 2) }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order Timeline</h5>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Order Created</h6>
                                    <p class="text-muted mb-0">{{ $order->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                            @if($order->payment_status === 'completed')
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Payment Completed</h6>
                                        <p class="text-muted mb-0">{{ $order->updated_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 