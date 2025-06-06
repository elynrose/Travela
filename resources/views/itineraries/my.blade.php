<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ __('My Itineraries') }}</h2>
            <a href="{{ route('itineraries.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Create Itinerary
            </a>
        </div>
    </x-slot>

    <div class="row g-4">
        @forelse($itineraries as $itinerary)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        @if($itinerary->getCoverImageUrl())
                            <img src="{{ $itinerary->getCoverImageUrl() }}" alt="{{ $itinerary->title }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                        <span class="position-absolute top-0 end-0 m-3 badge bg-primary">
                            ${{ number_format($itinerary->price, 2) }}
                        </span>
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge {{ $itinerary->is_published ? 'bg-success' : 'bg-warning' }}">
                                {{ $itinerary->is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            @foreach($itinerary->categories as $category)
                                <a href="{{ route('categories.show', $category) }}" class="badge bg-light text-dark text-decoration-none me-1">
                                    {{ $category->name }}
                                </a>
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
                                @if($itinerary->user->getFirstMedia('avatar'))
                                    <img src="{{ $itinerary->user->getAvatarThumbUrlAttribute() }}" alt="{{ $itinerary->user->name }}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-light" style="width: 32px; height: 32px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person text-muted" viewBox="0 0 16 16">
                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="small fw-bold">{{ $itinerary->user->name }}</div>
                                    <div class="small text-muted">{{ $itinerary->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('itineraries.edit', $itinerary) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('itineraries.destroy', $itinerary) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this itinerary?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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
                    <p class="text-muted">Start creating your first itinerary to share with travelers.</p>
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