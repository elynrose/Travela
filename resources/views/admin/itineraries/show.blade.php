<x-admin-layout>
    <x-slot:header>
        Itinerary Details
    </x-slot>

    <div class="row g-4">
        <!-- Itinerary Info -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    @if($itinerary->cover_image)
                        <img src="{{ Storage::url($itinerary->cover_image) }}" alt="{{ $itinerary->title }}" class="img-fluid rounded mb-3">
                    @endif

                    <h4>{{ $itinerary->title }}</h4>
                    <p class="text-muted">by {{ $itinerary->user->name }}</p>

                    <div class="mb-3">
                        <label class="text-muted">Status</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $itinerary->is_published ? 'success' : 'warning' }}">
                                {{ $itinerary->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted">Featured</label>
                        <p class="mb-0">
                            <form action="{{ route('admin.itineraries.toggle-featured', $itinerary) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-{{ $itinerary->is_featured ? 'warning' : 'secondary' }}">
                                    <i class="bi bi-star{{ $itinerary->is_featured ? '-fill' : '' }}"></i>
                                    {{ $itinerary->is_featured ? 'Featured' : 'Not Featured' }}
                                </button>
                            </form>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted">Categories</label>
                        <p class="mb-0">
                            @foreach($itinerary->categories as $category)
                                <span class="badge bg-info me-1">{{ $category->name }}</span>
                            @endforeach
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted">Created</label>
                        <p class="mb-0">{{ $itinerary->created_at->format('M d, Y') }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted">Last Updated</label>
                        <p class="mb-0">{{ $itinerary->updated_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Itinerary Content -->
        <div class="col-md-8">
            <!-- Description -->
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Description</h5>
                </div>
                <div class="card-body">
                    <p>{{ $itinerary->description }}</p>
                </div>
            </div>

            <!-- Itinerary Days -->
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Itinerary Days</h5>
                </div>
                <div class="card-body">
                    @forelse($itinerary->days as $day)
                        <div class="mb-4">
                            <h6>Day {{ $day->day_number }}</h6>
                            <p>{{ $day->description }}</p>
                            @if($day->photos->count() > 0)
                                <div class="row g-2">
                                    @foreach($day->photos as $photo)
                                        <div class="col-md-3">
                                            <img src="{{ Storage::url($photo->path) }}" alt="Day {{ $day->day_number }} Photo" class="img-fluid rounded">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted">No days added to this itinerary.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($itinerary->orders->take(5) as $order)
                                    <tr>
                                        <td>{{ $order->user->name }}</td>
                                        <td>${{ number_format($order->amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->payment_status === 'completed' ? 'success' : 'warning' }}">
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout> 