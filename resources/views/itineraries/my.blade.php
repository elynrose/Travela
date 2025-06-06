<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ __('My Itineraries') }}</h2>
            <a href="{{ route('itineraries.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Create Itinerary
            </a>
        </div>
    </x-slot>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row g-4">
        @forelse($itineraries as $itinerary)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        @if($itinerary->cover_image && $itinerary->getCoverImageUrl())
                            <img src="{{ $itinerary->getCoverImageUrl() }}" alt="{{ $itinerary->title }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                        <span class="position-absolute top-0 end-0 m-3 badge bg-primary">
                            ${{ number_format($itinerary->price, 2) }}
                        </span>
                        @if($itinerary->is_published)
                            <span class="position-absolute top-0 start-0 m-3 badge bg-success">Published</span>
                        @else
                            <span class="position-absolute top-0 start-0 m-3 badge bg-warning">Draft</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            @foreach($itinerary->categories as $category)
                                <span class="badge bg-light text-dark me-1">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                        <h5 class="card-title">
                            <a href="{{ route('itineraries.show', $itinerary) }}" class="text-decoration-none text-dark">
                                {{ $itinerary->title }}
                            </a>
                        </h5>
                        <p class="card-text text-muted">{{ Str::limit($itinerary->description, 100) }}</p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $itinerary->days->count() }} {{ Str::plural('day', $itinerary->days->count()) }}
                                </span>
                                <span class="badge bg-light text-dark ms-2">
                                    <i class="bi bi-cart me-1"></i>{{ $itinerary->orders->count() }} {{ Str::plural('sale', $itinerary->orders->count()) }}
                                </span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('itineraries.edit', $itinerary) }}">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('itineraries.days.edit', $itinerary) }}">
                                            <i class="bi bi-calendar-check me-2"></i>Edit Days
                                        </a>
                                    </li>
                                    @if(!$itinerary->is_published)
                                        <li>
                                            <form action="{{ route('itineraries.publish', $itinerary) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-check-circle me-2"></i>Publish
                                                </button>
                                            </form>
                                        </li>
                                    @else
                                        <li>
                                            <form action="{{ route('itineraries.unpublish', $itinerary) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-x-circle me-2"></i>Unpublish
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('itineraries.destroy', $itinerary) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this itinerary?')">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-map display-1 text-muted"></i>
                    <h3 class="h5 mt-3">No itineraries yet</h3>
                    <p class="text-muted">Create your first itinerary to start sharing your travel experiences.</p>
                    <a href="{{ route('itineraries.create') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-lg me-2"></i>Create Itinerary
                    </a>
                </div>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="col-12">
            <div class="d-flex justify-content-center mt-4">
                {{ $itineraries->links() }}
            </div>
        </div>
    </div>
</x-app-layout> 