<x-admin-layout>
    <x-slot:header>
        Itineraries
    </x-slot>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Active Itineraries</h6>
                    <h3 class="mb-0">{{ $activeCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Draft Itineraries</h6>
                    <h3 class="mb-0">{{ $draftCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Purchased Itineraries</h6>
                    <h3 class="mb-0">{{ $purchasedCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Copied Itineraries</h6>
                    <h3 class="mb-0">{{ $copiedCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Creator</th>
                            <th>Categories</th>
                            <th>Orders</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itineraries as $itinerary)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($itinerary->cover_image)
                                            <img src="{{ Storage::url($itinerary->cover_image) }}" alt="{{ $itinerary->title }}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary bg-opacity-10 rounded p-2 me-2">
                                                <i class="bi bi-image text-primary"></i>
                                            </div>
                                        @endif
                                        {{ $itinerary->title }}
                                    </div>
                                </td>
                                <td>{{ $itinerary->user->name }}</td>
                                <td>
                                    @foreach($itinerary->categories as $category)
                                        <span class="badge bg-info me-1">{{ $category->name }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $itinerary->orders_count }}</td>
                                <td>
                                    <span class="badge bg-{{ $itinerary->is_published ? 'success' : 'warning' }}">
                                        {{ $itinerary->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.itineraries.toggle-featured', $itinerary) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $itinerary->is_featured ? 'warning' : 'secondary' }}">
                                            <i class="bi bi-star{{ $itinerary->is_featured ? '-fill' : '' }}"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>{{ $itinerary->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.itineraries.show', $itinerary) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No itineraries found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $itineraries->links() }}
            </div>
        </div>
    </div>
</x-admin-layout> 