<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ $itinerary->title ?? 'Untitled Itinerary' }}</h2>
            <div class="d-flex gap-2">
                @if(auth()->check() && auth()->id() === $itinerary->user_id)
                    <a href="{{ route('itineraries.edit', $itinerary) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i>Edit Itinerary
                    </a>
                    <a href="{{ route('itineraries.days.edit', $itinerary) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-2"></i>Edit Day-by-Day Itinerary
                    </a>
                @elseif(auth()->check() && auth()->user()->hasPurchased($itinerary))
                    <a href="{{ route('itineraries.days.show', $itinerary) }}" class="btn btn-primary">
                        <i class="bi bi-calendar-week me-2"></i>View Day-by-Day Itinerary
                    </a>
                @endif
                <a href="{{ route('itineraries.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Itineraries
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <!-- Cover Image -->
                @if($itinerary->getCoverImageUrl())
                    <div class="mb-4">
                        <img src="{{ $itinerary->getCoverImageUrl() }}" 
                             alt="{{ $itinerary->title ?? 'Itinerary Cover' }}" 
                             class="img-fluid rounded shadow-sm w-100" 
                             style="max-height: 400px; object-fit: cover;">
                    </div>
                @endif

                <!-- Title and Basic Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="h5 mb-3">About this Itinerary</h3>
                        <p class="text-muted">{{ $itinerary->description ?? 'No description available.' }}</p>
                    </div>
                </div>

                <!-- Highlights -->
                @if($itinerary->highlights && count($itinerary->highlights) > 0)
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-3">Highlights</h3>
                            <div class="row g-3">
                                @foreach($itinerary->highlights as $highlight)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <span>{{ $highlight }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Included & Excluded -->
                <div class="row g-4 mb-4">
                    @if($itinerary->included_items && count($itinerary->included_items) > 0)
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">What's Included</h3>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($itinerary->included_items as $item)
                                            <li class="mb-2">
                                                <i class="bi bi-check2 text-success me-2"></i>
                                                {{ $item }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($itinerary->excluded_items && count($itinerary->excluded_items) > 0)
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h3 class="h5 mb-3">What's Not Included</h3>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($itinerary->excluded_items as $item)
                                            <li class="mb-2">
                                                <i class="bi bi-x text-danger me-2"></i>
                                                {{ $item }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Requirements -->
                @if($itinerary->requirements && count($itinerary->requirements) > 0)
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-3">Requirements</h3>
                            <ul class="list-unstyled mb-0">
                                @foreach($itinerary->requirements as $requirement)
                                    <li class="mb-2">
                                        <i class="bi bi-info-circle me-2"></i>
                                        {{ $requirement }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Gallery -->
                @if($itinerary->gallery && count($itinerary->gallery) > 0)
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h3 class="h5 mb-3">Photo Gallery</h3>
                            <div class="row g-3">
                                @foreach($itinerary->gallery as $image)
                                    <div class="col-md-4">
                                        <img src="{{ Storage::url($image) }}" 
                                             alt="Gallery Image" 
                                             class="img-fluid rounded" 
                                             style="height: 200px; width: 100%; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Booking Card -->
                <div class="card shadow-sm sticky-top" style="top: 2rem;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h5 mb-0">Book this Itinerary</h3>
                            <span class="h4 mb-0">${{ number_format($itinerary->price ?? 0, 2) }}</span>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-calendar3 me-2"></i>
                                <span>{{ count($itinerary->days ?? []) }} days</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-geo-alt me-2"></i>
                                <span>{{ $itinerary->location ?? 'Location not specified' }}, {{ $itinerary->country ?? 'Country not specified' }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person me-2"></i>
                                <span>Created by {{ $itinerary->user->name ?? 'Unknown User' }}</span>
                            </div>
                        </div>

                        @auth
                            @if(auth()->id() !== $itinerary->user_id)
                                @if($itinerary->is_published)
                                    <form action="{{ route('orders.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="itinerary_id" value="{{ $itinerary->id }}">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-cart me-2"></i>Purchase Itinerary
                                        </button>
                                    </form>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        This itinerary is not available for purchase.
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-info mb-0">
                                    This is your itinerary. You can edit it from the button above.
                                </div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login to Purchase
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Categories -->
                @if($itinerary->categories && count($itinerary->categories) > 0)
                    <div class="card shadow-sm mt-4">
                        <div class="card-body">
                            <h3 class="h5 mb-3">Categories</h3>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($itinerary->categories as $category)
                                    <a href="{{ route('categories.show', $category) }}" class="badge bg-light text-dark text-decoration-none">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Contact Host -->
                @auth
                    @if(auth()->id() !== $itinerary->user_id && $itinerary->user)
                        <div class="card shadow-sm mt-4">
                            <div class="card-body">
                                <h3 class="h5 mb-3">Contact Host</h3>
                                <a href="{{ route('messages.conversation', $itinerary->user) }}" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-chat me-2"></i>Send Message
                                </a>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</x-app-layout> 