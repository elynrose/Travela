<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ __('Itineraries') }}</h2>
            @auth
                <a href="{{ route('itineraries.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Create Itinerary
                </a>
            @endauth
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Filters -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('itineraries.index') }}" method="GET">
                        <div class="row g-3">
                            <!-- Location -->
                            <div class="col-md-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="{{ request('location') }}">
                            </div>

                            <!-- Duration -->
                            <div class="col-md-3">
                                <label for="duration" class="form-label">Duration (days)</label>
                                <select class="form-select" id="duration" name="duration">
                                    <option value="">Any</option>
                                    <option value="1-3" {{ request('duration') == '1-3' ? 'selected' : '' }}>1-3 days</option>
                                    <option value="4-7" {{ request('duration') == '4-7' ? 'selected' : '' }}>4-7 days</option>
                                    <option value="8-14" {{ request('duration') == '8-14' ? 'selected' : '' }}>8-14 days</option>
                                    <option value="15+" {{ request('duration') == '15+' ? 'selected' : '' }}>15+ days</option>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="col-md-3">
                                <label for="price_range" class="form-label">Price Range</label>
                                <select class="form-select" id="price_range" name="price_range">
                                    <option value="">Any</option>
                                    <option value="0-100" {{ request('price_range') == '0-100' ? 'selected' : '' }}>$0 - $100</option>
                                    <option value="101-500" {{ request('price_range') == '101-500' ? 'selected' : '' }}>$101 - $500</option>
                                    <option value="501-1000" {{ request('price_range') == '501-1000' ? 'selected' : '' }}>$501 - $1000</option>
                                    <option value="1001+" {{ request('price_range') == '1001+' ? 'selected' : '' }}>$1000+</option>
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">Any</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-2"></i>Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Itineraries Grid -->
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
                                <img src="{{ $itinerary->user->profile_photo_url }}" alt="{{ $itinerary->user->name }}" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                <div>
                                    <div class="small fw-bold">{{ $itinerary->user->name }}</div>
                                    <div class="small text-muted">{{ $itinerary->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-calendar3 me-1"></i>{{ $itinerary->duration_days }} days
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-map display-1 text-muted"></i>
                    <h3 class="h5 mt-3">No itineraries found</h3>
                    <p class="text-muted">Try adjusting your search or filter to find what you're looking for.</p>
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