<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Payment Successful</h2>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="card-title mb-3">Thank You for Your Purchase!</h3>
                        <p class="text-muted mb-4">
                            Your payment has been processed successfully. You can now access your purchased itinerary.
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-primary">
                                <i class="bi bi-eye me-2"></i>View Order Details
                            </a>
                            <a href="{{ route('itineraries.show', $order->itinerary) }}" class="btn btn-outline-primary">
                                <i class="bi bi-map me-2"></i>View Itinerary
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 