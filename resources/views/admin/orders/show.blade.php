<x-admin-layout>
    <x-slot:header>
        Order Details
    </x-slot>

    <div class="row g-4">
        <!-- Order Info -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h4>Order #{{ $order->order_number }}</h4>
                    <p class="text-muted">Placed on {{ $order->created_at->format('M d, Y H:i') }}</p>

                    <div class="mb-3">
                        <label class="text-muted">Status</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $order->status === 'completed' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted">Amount</label>
                        <p class="mb-0">${{ number_format($order->amount, 2) }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted">Payment Method</label>
                        <p class="mb-0">{{ ucfirst($order->payment_method) }}</p>
                    </div>

                    @if($order->payment_id)
                        <div class="mb-3">
                            <label class="text-muted">Payment ID</label>
                            <p class="mb-0">{{ $order->payment_id }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($order->user->avatar)
                            <img src="{{ Storage::url($order->user->avatar) }}" alt="{{ $order->user->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px;">
                        @else
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="bi bi-person text-primary"></i>
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-0">{{ $order->user->name }}</h6>
                            <small class="text-muted">{{ $order->user->email }}</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.show', $order->user) }}" class="btn btn-sm btn-info w-100">
                        View Customer Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Itinerary Info -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Itinerary Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($order->itinerary->cover_image)
                                <img src="{{ Storage::url($order->itinerary->cover_image) }}" alt="{{ $order->itinerary->title }}" class="img-fluid rounded mb-3">
                            @else
                                <div class="bg-primary bg-opacity-10 rounded p-4 mb-3 text-center">
                                    <i class="bi bi-image text-primary" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h5>{{ $order->itinerary->title }}</h5>
                            <p class="text-muted">by {{ $order->itinerary->user->name }}</p>
                            <p>{{ Str::limit($order->itinerary->description, 200) }}</p>
                            <div class="mb-3">
                                @foreach($order->itinerary->categories as $category)
                                    <span class="badge bg-info me-1">{{ $category->name }}</span>
                                @endforeach
                            </div>
                            <a href="{{ route('admin.itineraries.show', $order->itinerary) }}" class="btn btn-info">
                                View Itinerary Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="card mt-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Order Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">Order Placed</h6>
                                <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @if($order->paid_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Payment Completed</h6>
                                    <small class="text-muted">{{ $order->paid_at->format('M d, Y H:i') }}</small>
                                </div>
                            </div>
                        @endif
                        @if($order->completed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Order Completed</h6>
                                    <small class="text-muted">{{ $order->completed_at->format('M d, Y H:i') }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-marker {
            position: absolute;
            left: -30px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
        }
        .timeline-item:not(:last-child):before {
            content: '';
            position: absolute;
            left: -23px;
            top: 15px;
            height: 100%;
            width: 2px;
            background: #e9ecef;
        }
    </style>
</x-admin-layout> 