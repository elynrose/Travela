<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">{{ $itinerary->title }}</h2>
            <a href="{{ route('itineraries.show', $itinerary) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Overview
            </a>
        </div>
    </x-slot>

    <!-- Travel Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h3 class="h5 mb-3">Travel Information</h3>
            <div class="row g-3">
                @if($itinerary->transportation_type)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-airplane me-2 text-primary"></i>
                            <strong>Transportation:</strong>
                            <span class="ms-2">{{ ucfirst($itinerary->transportation_type) }}</span>
                        </div>
                    </div>
                @endif

                @if($itinerary->flight_duration)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-clock me-2 text-primary"></i>
                            <strong>Flight Duration:</strong>
                            <span class="ms-2">{{ $itinerary->flight_duration }}</span>
                        </div>
                    </div>
                @endif

                @if($itinerary->airfare_min && $itinerary->airfare_max)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-currency-dollar me-2 text-primary"></i>
                            <strong>Airfare Range:</strong>
                            <span class="ms-2">${{ number_format($itinerary->airfare_min, 2) }} - ${{ number_format($itinerary->airfare_max, 2) }}</span>
                        </div>
                    </div>
                @endif

                @if($itinerary->booking_website)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-globe me-2 text-primary"></i>
                            <strong>Booking Website:</strong>
                            <a href="{{ $itinerary->booking_website }}" target="_blank" class="ms-2">Visit Website</a>
                        </div>
                    </div>
                @endif

                @if($itinerary->transportation_type === 'road' || $itinerary->transportation_type === 'both')
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-signpost-split me-2 text-primary"></i>
                            <strong>Road Distance:</strong>
                            <span class="ms-2">{{ $itinerary->road_distance }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-clock-history me-2 text-primary"></i>
                            <strong>Road Duration:</strong>
                            <span class="ms-2">{{ $itinerary->road_duration }}</span>
                        </div>
                    </div>
                    @if($itinerary->road_type)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-road me-2 text-primary"></i>
                                <strong>Road Type:</strong>
                                <span class="ms-2">{{ ucfirst($itinerary->road_type) }}</span>
                            </div>
                        </div>
                    @endif
                @endif

                @if($itinerary->languages)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-translate me-2 text-primary"></i>
                            <strong>Languages:</strong>
                            <span class="ms-2">{{ $itinerary->languages }}</span>
                        </div>
                    </div>
                @endif

                @if($itinerary->peak_travel_times)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>
                            <strong>Peak Travel Times:</strong>
                            <span class="ms-2">{{ $itinerary->peak_travel_times }}</span>
                        </div>
                    </div>
                @endif

                @if($itinerary->travel_agency)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-building me-2 text-primary"></i>
                            <strong>Travel Agency:</strong>
                            <span class="ms-2">{{ $itinerary->travel_agency }}</span>
                        </div>
                    </div>
                @endif

                @if($itinerary->agency_fees)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-currency-dollar me-2 text-primary"></i>
                            <strong>Agency Fees:</strong>
                            <span class="ms-2">${{ number_format($itinerary->agency_fees, 2) }}</span>
                        </div>
                    </div>
                @endif

                @if($itinerary->travel_notes)
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Additional Notes:</strong>
                            <p class="mb-0 mt-2">{{ $itinerary->travel_notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Day-by-Day Itinerary -->
    <div class="row">
        <div class="col-lg-8">
            <div class="accordion" id="itineraryDaysAccordion">
                @foreach($days as $index => $day)
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="headingDay{{ $index }}">
                            <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDay{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapseDay{{ $index }}">
                                Day {{ $day->day_number }}
                            </button>
                        </h2>
                        <div id="collapseDay{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="headingDay{{ $index }}" data-bs-parent="#itineraryDaysAccordion">
                            <div class="accordion-body">
                                <!-- Accommodation -->
                                @if($day->accommodation)
                                    <div class="mb-4">
                                        <h4 class="h6 text-primary mb-3">
                                            <i class="bi bi-house-door me-2"></i>Accommodation
                                        </h4>
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">{{ $day->accommodation }}</h5>
                                                @if($day->accommodation_address)
                                                    <div class="mb-2">
                                                        <div class="map-container" style="height: 300px; width: 100%;">
                                                            <iframe
                                                                width="100%"
                                                                height="100%"
                                                                frameborder="0"
                                                                style="border:0"
                                                                src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google.maps_api_key') }}&q={{ urlencode($day->accommodation_address) }}"
                                                                allowfullscreen>
                                                            </iframe>
                                                        </div>
                                                    </div>
                                                    <p class="text-muted mb-2">
                                                        <i class="bi bi-geo-alt me-1"></i>{{ $day->accommodation_address }}
                                                    </p>
                                                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($day->accommodation_address) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-primary nowrap">
                                                        <i class="bi bi-map me-1"></i>Let's go
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Meals -->
                                @if($day->meals)
                                    <div class="mb-4">
                                        <h4 class="h6 text-primary mb-3">
                                            <i class="bi bi-cup-hot me-2"></i>Meals
                                        </h4>
                                        <div class="row g-3">
                                            @foreach(['breakfast', 'lunch', 'dinner'] as $mealType)
                                                @if(isset($day->meals[$mealType]['name']))
                                                    <div class="col-md-4">
                                                        <div class="card h-100">
                                                            <div class="card-body">
                                                                <h5 class="card-title text-capitalize">{{ $mealType }}</h5>
                                                                <p class="card-text mb-1">{{ $day->meals[$mealType]['name'] }}</p>
                                                                @if(isset($day->meals[$mealType]['address']))
                                                                    <div class="mb-2">
                                                                        <div class="map-container" style="height: 200px; width: 100%;">
                                                                            <iframe
                                                                                width="100%"
                                                                                height="100%"
                                                                                frameborder="0"
                                                                                style="border:0"
                                                                                src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google.maps_api_key') }}&q={{ urlencode($day->meals[$mealType]['address']) }}"
                                                                                allowfullscreen>
                                                                            </iframe>
                                                                        </div>
                                                                    </div>
                                                                    <p class="text-muted small mb-2">
                                                                        <i class="bi bi-geo-alt me-1"></i>{{ $day->meals[$mealType]['address'] }}
                                                                    </p>
                                                                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($day->meals[$mealType]['address']) }}" 
                                                                       target="_blank" 
                                                                       class="btn btn-sm btn-primary nowrap">
                                                                        <i class="bi bi-map me-1"></i>Let's go
                                                                    </a>
                                                                @endif

                                                                <!-- Meal Photos -->
                                                                @php
                                                                    $mealPhotos = $day->getMealPhotos($mealType);
                                                                @endphp
                                                                @if($mealPhotos->isNotEmpty())
                                                                    <div class="mt-3">
                                                                        <h6 class="small text-muted mb-2">Photos</h6>
                                                                        <div class="row g-2">
                                                                            @foreach($mealPhotos as $photo)
                                                                                <div class="col-4">
                                                                                    <div class="position-relative">
                                                                                        <a href="{{ $photo['url'] }}" target="_blank" class="d-block">
                                                                                            <img src="{{ $photo['thumb_url'] }}" 
                                                                                                 alt="Meal photo" 
                                                                                                 class="img-fluid rounded"
                                                                                                 style="width: 100%; height: 80px; object-fit: cover;">
                                                                                        </a>
                                                                                        <button type="button" 
                                                                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 delete-photo"
                                                                                            data-photo-path="{{ $photo['path'] }}"
                                                                                            data-day-id="{{ $day->id }}">
                                                                                            <i class="bi bi-trash"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Activities -->
                                @if($day->activities)
                                    <div class="mb-4">
                                        <h4 class="h6 text-primary mb-3">
                                            <i class="bi bi-calendar-check me-2"></i>Activities & Sightseeing
                                        </h4>
                                        <div class="row g-3">
                                            @foreach($day->activities as $activityIndex => $activity)
                                                <div class="col-md-6">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-start mb-2">
                                                                <div class="flex-grow-1">
                                                                    <h5 class="card-title h6 mb-1">{{ $activity['name'] }}</h5>
                                                                    @if(isset($activity['description']))
                                                                        <p class="card-text small text-muted mb-2">{{ $activity['description'] }}</p>
                                                                    @endif
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    <span class="badge {{ isset($activity['entry_fee']) && $activity['entry_fee'] > 0 ? 'bg-success' : 'bg-info' }} rounded-pill">
                                                                        <i class="bi bi-ticket-perforated me-1"></i>
                                                                        @if(isset($activity['entry_fee']) && $activity['entry_fee'] > 0)
                                                                            ${{ number_format($activity['entry_fee'], 2) }}
                                                                        @else
                                                                            Free
                                                                        @endif
                                                                    </span>
                                                                    <span class="badge bg-primary rounded-pill">
                                                                        <i class="bi bi-geo-alt me-1"></i>Location
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            @if(isset($activity['address']))
                                                                <div class="mb-3">
                                                                    <div class="map-container" style="height: 150px; width: 100%;">
                                                                        <iframe
                                                                            width="100%"
                                                                            height="100%"
                                                                            frameborder="0"
                                                                            style="border:0"
                                                                            src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google.maps_api_key') }}&q={{ urlencode($activity['address']) }}"
                                                                            allowfullscreen>
                                                                        </iframe>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <p class="text-muted small mb-0">
                                                                        <i class="bi bi-geo-alt me-1"></i>{{ $activity['address'] }}
                                                                    </p>
                                                                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($activity['address']) }}" 
                                                                       target="_blank" 
                                                                       class="btn btn-sm btn-primary nowrap">
                                                                        <i class="bi bi-map me-1"></i>Let's go
                                                                    </a>
                                                                </div>
                                                            @endif

                                                            <!-- Activity Photos -->
                                                            @php
                                                                $activityPhotos = $day->getActivityPhotos($activityIndex);
                                                            @endphp
                                                            @if($activityPhotos->isNotEmpty())
                                                                <div class="mt-3">
                                                                    <h6 class="small text-muted mb-2">Photos</h6>
                                                                    <div class="row g-2">
                                                                        @foreach($activityPhotos as $photo)
                                                                            <div class="col-4">
                                                                                <div class="position-relative">
                                                                                    <a href="{{ $photo['url'] }}" target="_blank" class="d-block">
                                                                                        <img src="{{ $photo['thumb_url'] }}" 
                                                                                             alt="Activity photo" 
                                                                                             class="img-fluid rounded"
                                                                                             style="width: 100%; height: 80px; object-fit: cover;">
                                                                                    </a>
                                                                                    <button type="button" 
                                                                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 delete-photo"
                                                                                        data-photo-path="{{ $photo['path'] }}"
                                                                                        data-day-id="{{ $day->id }}">
                                                                                        <i class="bi bi-trash"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- Receipts -->
                                                            @php
                                                                $receipts = $day->getReceipts();
                                                            @endphp
                                                            @if($receipts->isNotEmpty())
                                                                <div class="mt-3">
                                                                    <h6 class="small text-muted mb-2">Receipts</h6>
                                                                    <div class="list-group list-group-flush">
                                                                        @foreach($receipts as $receipt)
                                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                                <a href="{{ $receipt['url'] }}" 
                                                                                   target="_blank" 
                                                                                   class="text-decoration-none">
                                                                                    <i class="bi bi-file-earmark-text me-2"></i>
                                                                                    <span class="text-truncate">{{ $receipt['name'] }}</span>
                                                                                </a>
                                                                                <button type="button" 
                                                                                    class="btn btn-danger btn-sm delete-photo"
                                                                                    data-photo-path="{{ $receipt['path'] }}"
                                                                                    data-day-id="{{ $day->id }}">
                                                                                    <i class="bi bi-trash"></i>
                                                                                </button>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Notes -->
                                @if($day->notes)
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Additional Notes:</strong>
                                        <p class="mb-0 mt-2">{{ $day->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 2rem;">
                <div class="card-body">
                    <h3 class="h5 mb-3">Quick Information</h3>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-calendar3 me-2 text-primary"></i>
                            <strong>Duration:</strong> {{ count($days) }} days
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-geo-alt me-2 text-primary"></i>
                            <strong>Location:</strong> {{ $itinerary->location }}
                        </li>
                        @if($itinerary->price)
                            <li class="mb-3">
                                <i class="bi bi-currency-dollar me-2 text-primary"></i>
                                <strong>Price:</strong> ${{ number_format($itinerary->price, 2) }}
                            </li>
                        @endif
                        @if($itinerary->included_items)
                            <li class="mb-3">
                                <i class="bi bi-check-circle me-2 text-primary"></i>
                                <strong>Included:</strong>
                                <ul class="list-unstyled ms-4 mt-1">
                                    @foreach($itinerary->included_items as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                        @if($itinerary->excluded_items)
                            <li class="mb-3">
                                <i class="bi bi-x-circle me-2 text-primary"></i>
                                <strong>Not Included:</strong>
                                <ul class="list-unstyled ms-4 mt-1">
                                    @foreach($itinerary->excluded_items as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .timeline {
            position: relative;
            padding-left: 1rem;
        }
        .timeline-item {
            position: relative;
        }
        .timeline-marker {
            position: absolute;
            left: -1rem;
            top: 0.25rem;
        }
        .timeline-marker i {
            font-size: 0.5rem;
        }
        .timeline-content {
            padding-left: 1rem;
        }
        .map-container {
            position: relative;
            overflow: hidden;
            border-radius: 0.375rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .map-container iframe {
            border-radius: 0.375rem;
        }
        .card {
            transition: transform 0.2s ease-in-out;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .nowrap {
            white-space: nowrap;
        }
        @media print {
            .btn, .sticky-top {
                display: none !important;
            }
            .card {
                break-inside: avoid;
            }
            .map-container {
                display: none !important;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Add this to your existing scripts
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-photo')) {
                const button = e.target.closest('.delete-photo');
                const photoPath = button.dataset.photoPath;
                const dayId = button.dataset.dayId;

                if (confirm('Are you sure you want to delete this photo?')) {
                    fetch(`/days/${dayId}/photos/${encodeURIComponent(photoPath)}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            button.closest('.col-4, .list-group-item').remove();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete photo');
                    });
                }
            }
        });
    </script>
    @endpush
</x-app-layout> 