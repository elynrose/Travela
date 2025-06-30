<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                Category: {{ $category->name }}
            </h2>
            <a href="{{ route('itineraries.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>All Itineraries
            </a>
        </div>
    </x-slot>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="h5 mb-2">About this Category</h3>
                        <p class="text-muted mb-0">{{ $category->description ?? 'No description available.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @forelse($itineraries as $itinerary)
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        @if($itinerary->cover_image)
                            <a href="{{ route('itineraries.show', $itinerary) }}">
                                <img src="{{ Storage::url($itinerary->cover_image) }}" alt="{{ $itinerary->title }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                            </a>
                        @else
                            <a href="{{ route('itineraries.show', $itinerary) }}" class="d-block bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                            </a>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                @foreach($itinerary->categories as $cat)
                                    <a href="{{ route('categories.show', $cat) }}" class="badge bg-light text-dark text-decoration-none me-1">{{ $cat->name }}</a>
                                @endforeach
                            </div>
                            <h5 class="card-title mb-1">
                                <a href="{{ route('itineraries.show', $itinerary) }}" class="text-decoration-none text-dark">{{ $itinerary->title }}</a>
                            </h5>
                            <p class="card-text text-muted small mb-2">by {{ $itinerary->user->name }}</p>
                            <p class="card-text text-muted flex-grow-1">{{ Str::limit($itinerary->description, 100) }}</p>
                            <a href="{{ route('itineraries.show', $itinerary) }}" class="btn btn-primary mt-2">View Details</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No itineraries found in this category.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout> 