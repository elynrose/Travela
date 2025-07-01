<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Order Details</h2>
            <div>
                @if($order->payment_status === 'completed')
                    <form action="{{ route('itineraries.copy', $order->itinerary) }}" method="POST" class="d-inline me-2">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-files me-2"></i>Copy Itinerary
                        </button>
                    </form>
                @endif
                <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Orders
                </a>
            </div>
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
                                @if($order->itinerary->cover_image)
                                    <img src="{{ Storage::url($order->itinerary->cover_image) }}" 
                                         alt="{{ $order->itinerary->title }}" 
                                         class="img-fluid rounded"
                                         style="height: 200px; width: 100%; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
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

                <!-- Payment Section -->
                @if($order->payment_status === 'pending')
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Complete Payment</h5>
                            <form id="payment-form" class="mb-4">
                                <button id="submit" class="btn btn-primary w-100">
                                    <span id="button-text">Pay Now</span>
                                    <span id="spinner" class="spinner d-none"></span>
                                </button>
                                <div id="payment-message" class="d-none mt-3"></div>
                            </form>
                        </div>
                    </div>
                @endif
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
        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 0.2em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border .75s linear infinite;
        }

        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }

        #payment-element {
            margin-bottom: 24px;
        }

        #payment-message {
            color: rgb(105, 115, 134);
            font-size: 16px;
            line-height: 20px;
            padding-top: 12px;
            text-align: center;
        }

        #payment-element .ElementsApp {
            border-radius: 4px;
            padding: 12px;
            border: 1px solid #E0E0E0;
            background: white;
            box-shadow: 0 1px 3px 0 #E6EBF1;
        }

        #payment-element .ElementsApp:focus-within {
            box-shadow: 0 1px 3px 0 #CFD7DF;
        }

        #payment-element .ElementsApp .InputElement {
            padding: 12px;
            border: 1px solid #E0E0E0;
            border-radius: 4px;
            background: white;
        }

        #payment-element .ElementsApp .InputElement:focus {
            border-color: #0d6efd;
            box-shadow: 0 1px 3px 0 #CFD7DF;
        }
    </style>
    @endpush

    @if($order->payment_status === 'pending')
        @push('scripts')
        <script>
            document
                .querySelector("#payment-form")
                .addEventListener("submit", handleSubmit);

            async function handleSubmit(e) {
                e.preventDefault();
                setLoading(true);

                try {
                    const response = await fetch("{{ route('payment.create-intent', $order) }}", {
                        method: "POST",
                        headers: { 
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ order_id: "{{ $order->id }}" })
                    });
                    
                    const data = await response.json();
                    
                    if (data.url) {
                        window.location.href = data.url;
                    } else {
                        showMessage(data.error || "An error occurred. Please try again.");
                        setLoading(false);
                    }
                } catch (error) {
                    console.error('Payment error:', error);
                    showMessage("An error occurred while processing your payment. Please try again.");
                    setLoading(false);
                }
            }

            function setLoading(isLoading) {
                if (isLoading) {
                    document.querySelector("#submit").disabled = true;
                    document.querySelector("#spinner").classList.remove("d-none");
                    document.querySelector("#button-text").classList.add("d-none");
                } else {
                    document.querySelector("#submit").disabled = false;
                    document.querySelector("#spinner").classList.add("d-none");
                    document.querySelector("#button-text").classList.remove("d-none");
                }
            }

            function showMessage(messageText) {
                const messageContainer = document.querySelector("#payment-message");
                messageContainer.classList.remove("d-none");
                messageContainer.textContent = messageText;
            }
        </script>
        @endpush
    @endif
</x-app-layout> 