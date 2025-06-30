<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Day-by-Day Itinerary: {{ $itinerary->title }}</h2>
            <div>
                <a href="{{ route('itineraries.edit', $itinerary) }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-pencil me-2"></i>Edit Basic Info
                </a>
                <a href="{{ route('itineraries.show', $itinerary) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Itinerary
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('itineraries.days.update', $itinerary) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div id="itinerary-days-container">
                    <ul class="nav nav-tabs mb-3" id="dayTabs" role="tablist">
                        @foreach(old('itinerary_days', $itineraryDays ?? []) as $index => $day)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                    id="day-tab-{{ $index }}" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#day-{{ $index }}" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="day-{{ $index }}" 
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    Day {{ $index + 1 }}
                                </button>
                            </li>
                        @endforeach
                        <li class="nav-item">
                            <button type="button" class="btn btn-outline-primary btn-sm ms-2" id="add-day">
                                <i class="bi bi-plus me-2"></i>Add Day
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="dayTabsContent">
                        @foreach(old('itinerary_days', $itineraryDays ?? []) as $index => $day)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                                id="day-{{ $index }}" 
                                role="tabpanel" 
                                aria-labelledby="day-tab-{{ $index }}">
                                <div class="card itinerary-day">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-day">
                                                <i class="bi bi-trash me-2"></i>Remove Day
                                            </button>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-primary">Accommodation</label>
                                                <input type="text" class="form-control @error('itinerary_days.'.$index.'.accommodation') is-invalid @enderror" 
                                                    name="itinerary_days[{{ $index }}][accommodation]" 
                                                    value="{{ $day['accommodation'] ?? '' }}" 
                                                    placeholder="Hotel name" required>
                                                @error('itinerary_days.'.$index.'.accommodation')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div id="pac-container-{{ $index }}"></div>
                                                <input type="hidden" name="itinerary_days[{{ $index }}][accommodation_address]" id="accommodation-address-{{ $index }}" value="{{ $day['accommodation_address'] ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-primary">Meals</label>
                                                <div class="card mb-2">
                                                    <div class="card-body p-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text fw-bold">Breakfast</span>
                                                            <input type="text" class="form-control" 
                                                                name="itinerary_days[{{ $index }}][meals][breakfast][name]" 
                                                                value="{{ $day['meals']['breakfast']['name'] ?? '' }}" 
                                                                placeholder="Restaurant name">
                                                        </div>
                                                        <input type="text" class="form-control mt-1" 
                                                            name="itinerary_days[{{ $index }}][meals][breakfast][address]" 
                                                            value="{{ $day['meals']['breakfast']['address'] ?? '' }}" 
                                                            placeholder="Restaurant address">
                                                        <div class="mt-2">
                                                            <label class="form-label small text-muted">Photos</label>
                                                            @if(isset($day['meals']['breakfast']['photos']) && !empty($day['meals']['breakfast']['photos']))
                                                                <div class="row g-2 mb-2">
                                                                    @foreach($day['meals']['breakfast']['photos'] as $photo)
                                                                        <div class="col-4">
                                                                            <div class="position-relative">
                                                                                <img src="{{ Storage::url($photo['thumb_path']) }}" 
                                                                                     alt="Meal photo" 
                                                                                     class="img-fluid rounded"
                                                                                     style="width: 100%; height: 80px; object-fit: cover;"
                                                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                                                     style="width: 100%; height: 80px; display: none;">
                                                                                    <i class="bi bi-image text-muted"></i>
                                                                                </div>
                                                                                <button type="button" 
                                                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 delete-photo"
                                                                                        data-photo-path="{{ $photo['path'] }}"
                                                                                        data-day-id="{{ $itinerary->days[$index]->id }}">
                                                                                    <i class="bi bi-trash"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            <input type="file" class="form-control form-control-sm" 
                                                                name="itinerary_days[{{ $index }}][meals][breakfast][photos][]" 
                                                                multiple accept="image/*">
                                                            <div class="form-text small">Upload photos (max 2MB each)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card mb-2">
                                                    <div class="card-body p-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text fw-bold">Lunch</span>
                                                            <input type="text" class="form-control" 
                                                                name="itinerary_days[{{ $index }}][meals][lunch][name]" 
                                                                value="{{ $day['meals']['lunch']['name'] ?? '' }}" 
                                                                placeholder="Restaurant name">
                                                        </div>
                                                        <input type="text" class="form-control mt-1" 
                                                            name="itinerary_days[{{ $index }}][meals][lunch][address]" 
                                                            value="{{ $day['meals']['lunch']['address'] ?? '' }}" 
                                                            placeholder="Restaurant address">
                                                        <div class="mt-2">
                                                            <label class="form-label small text-muted">Photos</label>
                                                            @if(isset($day['meals']['lunch']['photos']) && !empty($day['meals']['lunch']['photos']))
                                                                <div class="row g-2 mb-2">
                                                                    @foreach($day['meals']['lunch']['photos'] as $photo)
                                                                        <div class="col-4">
                                                                            <div class="position-relative">
                                                                                <img src="{{ Storage::url($photo['thumb_path']) }}" 
                                                                                     alt="Meal photo" 
                                                                                     class="img-fluid rounded"
                                                                                     style="width: 100%; height: 80px; object-fit: cover;"
                                                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                                                     style="width: 100%; height: 80px; display: none;">
                                                                                    <i class="bi bi-image text-muted"></i>
                                                                                </div>
                                                                                <button type="button" 
                                                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 delete-photo"
                                                                                        data-photo-path="{{ $photo['path'] }}"
                                                                                        data-day-id="{{ $itinerary->days[$index]->id }}">
                                                                                    <i class="bi bi-trash"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            <input type="file" class="form-control form-control-sm" 
                                                                name="itinerary_days[{{ $index }}][meals][lunch][photos][]" 
                                                                multiple accept="image/*">
                                                            <div class="form-text small">Upload photos (max 2MB each)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-body p-2">
                                                        <div class="input-group">
                                                            <span class="input-group-text fw-bold">Dinner</span>
                                                            <input type="text" class="form-control" 
                                                                name="itinerary_days[{{ $index }}][meals][dinner][name]" 
                                                                value="{{ $day['meals']['dinner']['name'] ?? '' }}" 
                                                                placeholder="Restaurant name">
                                                        </div>
                                                        <input type="text" class="form-control mt-1" 
                                                            name="itinerary_days[{{ $index }}][meals][dinner][address]" 
                                                            value="{{ $day['meals']['dinner']['address'] ?? '' }}" 
                                                            placeholder="Restaurant address">
                                                        <div class="mt-2">
                                                            <label class="form-label small text-muted">Photos</label>
                                                            @if(isset($day['meals']['dinner']['photos']) && !empty($day['meals']['dinner']['photos']))
                                                                <div class="row g-2 mb-2">
                                                                    @foreach($day['meals']['dinner']['photos'] as $photo)
                                                                        <div class="col-4">
                                                                            <div class="position-relative">
                                                                                <img src="{{ Storage::url($photo['thumb_path']) }}" 
                                                                                     alt="Meal photo" 
                                                                                     class="img-fluid rounded"
                                                                                     style="width: 100%; height: 80px; object-fit: cover;"
                                                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                                                     style="width: 100%; height: 80px; display: none;">
                                                                                    <i class="bi bi-image text-muted"></i>
                                                                                </div>
                                                                                <button type="button" 
                                                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 delete-photo"
                                                                                        data-photo-path="{{ $photo['path'] }}"
                                                                                        data-day-id="{{ $itinerary->days[$index]->id }}">
                                                                                    <i class="bi bi-trash"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            <input type="file" class="form-control form-control-sm" 
                                                                name="itinerary_days[{{ $index }}][meals][dinner][photos][]" 
                                                                multiple accept="image/*">
                                                            <div class="form-text small">Upload photos (max 2MB each)</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-primary">Activities & Sightseeing</label>
                                                <div id="activities-container-{{ $index }}" class="activities-container">
                                                    @if(isset($day['activities']) && is_array($day['activities']))
                                                        @foreach($day['activities'] as $activityIndex => $activity)
                                                            <div class="card mb-2 activity-item">
                                                                <div class="card-body p-2">
                                                                    <div class="row g-2">
                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control" 
                                                                                name="itinerary_days[{{ $index }}][activities][{{ $activityIndex }}][name]" 
                                                                                value="{{ $activity['name'] ?? '' }}" 
                                                                                placeholder="Activity name" required>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="input-group">
                                                                                <span class="input-group-text">$</span>
                                                                                <input type="number" class="form-control" 
                                                                                    name="itinerary_days[{{ $index }}][activities][{{ $activityIndex }}][entry_fee]" 
                                                                                    value="{{ $activity['entry_fee'] ?? '' }}" 
                                                                                    placeholder="Entry fee" step="0.01" min="0">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <input type="text" class="form-control" 
                                                                                name="itinerary_days[{{ $index }}][activities][{{ $activityIndex }}][address]" 
                                                                                value="{{ $activity['address'] ?? '' }}" 
                                                                                placeholder="Activity address">
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <textarea class="form-control" 
                                                                                name="itinerary_days[{{ $index }}][activities][{{ $activityIndex }}][description]" 
                                                                                placeholder="Activity description">{{ $activity['description'] ?? '' }}</textarea>
                                                                        </div>
                                                                        <div class="col-12">
                                                                            <div class="mt-2">
                                                                                <label class="form-label small text-muted">Photos</label>
                                                                                @if(isset($activity['photos']) && !empty($activity['photos']))
                                                                                    <div class="row g-2 mb-2">
                                                                                        @foreach($activity['photos'] as $photo)
                                                                                            <div class="col-4">
                                                                                                <div class="position-relative">
                                                                                                    <img src="{{ Storage::url($photo['thumb_path']) }}" 
                                                                                                         alt="Activity photo" 
                                                                                                         class="img-fluid rounded"
                                                                                                         style="width: 100%; height: 80px; object-fit: cover;"
                                                                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                                                                         style="width: 100%; height: 80px; display: none;">
                                                                                                        <i class="bi bi-image text-muted"></i>
                                                                                                    </div>
                                                                                                    <button type="button" 
                                                                                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 delete-photo"
                                                                                                            data-photo-path="{{ $photo['path'] }}"
                                                                                                            data-day-id="{{ $itinerary->days[$index]->id }}">
                                                                                                        <i class="bi bi-trash"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                @endif
                                                                                <input type="file" class="form-control form-control-sm" 
                                                                                    name="itinerary_days[{{ $index }}][activities][{{ $activityIndex }}][photos][]" 
                                                                                    multiple accept="image/*">
                                                                                <div class="form-text small">Upload photos (max 2MB each)</div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <button type="button" class="btn btn-outline-danger btn-sm mt-2 remove-activity">
                                                                        <i class="bi bi-trash me-2"></i>Remove Activity
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-outline-primary btn-sm add-activity" data-day-index="{{ $index }}">
                                                    <i class="bi bi-plus me-2"></i>Add Activity
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-primary">Additional Notes</label>
                                                <textarea class="form-control" 
                                                    name="itinerary_days[{{ $index }}][notes]" 
                                                    rows="2" 
                                                    placeholder="Any additional information for this day">{{ $day['notes'] ?? '' }}</textarea>
                                            </div>
                                            <div class="col-12">
                                                <div class="mt-3">
                                                    <label class="form-label fw-bold text-primary">Receipts</label>
                                                    @if(isset($day['receipts']) && !empty($day['receipts']))
                                                        <div class="row g-2 mb-2">
                                                            @foreach($day['receipts'] as $receipt)
                                                                <div class="col-4">
                                                                    <div class="position-relative">
                                                                        @if(str_ends_with($receipt['mime_type'], 'pdf'))
                                                                            <div class="bg-light rounded p-2 text-center">
                                                                                <i class="bi bi-file-pdf text-danger fs-4"></i>
                                                                                <div class="small text-truncate">{{ $receipt['name'] }}</div>
                                                                            </div>
                                                                        @else
                                                                            <img src="{{ Storage::url($receipt['path']) }}" 
                                                                                 alt="Receipt" 
                                                                                 class="img-fluid rounded"
                                                                                 style="width: 100%; height: 80px; object-fit: cover;"
                                                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                                                     style="width: 100%; height: 80px; display: none;">
                                                                                    <i class="bi bi-image text-muted"></i>
                                                                                </div>
                                                                        @endif
                                                                        <button type="button" 
                                                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 delete-photo"
                                                                                data-photo-path="{{ $receipt['path'] }}"
                                                                                data-day-id="{{ $itinerary->days[$index]->id }}">
                                                                            <i class="bi bi-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <input type="file" class="form-control" 
                                                        name="itinerary_days[{{ $index }}][receipts][]" 
                                                        multiple accept="image/jpeg,image/png,application/pdf">
                                                    <div class="form-text">Upload receipts (max 2MB each, JPEG, PNG, or PDF)</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save Itinerary
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize Google Places Autocomplete for all address fields
        function initializeAutocomplete() {
            // Get all address input fields
            const addressInputs = document.querySelectorAll('input[placeholder*="address"]');
            
            addressInputs.forEach(input => {
                const autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ['address'],
                    fields: ['formatted_address', 'geometry', 'name'],
                });

                // Prevent form submission on enter key
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });

                // When a place is selected, update the input with the formatted address
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    if (place.formatted_address) {
                        input.value = place.formatted_address;
                    }
                });
            });
        }

        // Initialize autocomplete when the page loads
        document.addEventListener('DOMContentLoaded', initializeAutocomplete);

        // Initialize autocomplete for dynamically added fields
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    initializeAutocomplete();
                }
            });
        });

        // Start observing the container for changes
        observer.observe(document.getElementById('itinerary-days-container'), {
            childList: true,
            subtree: true
        });

        // Add new day
        document.getElementById('add-day').addEventListener('click', function() {
            const container = document.getElementById('itinerary-days-container');
            const dayIndex = document.querySelectorAll('.tab-pane').length;
            
            // Add new tab
            const tabList = document.getElementById('dayTabs');
            const newTab = document.createElement('li');
            newTab.className = 'nav-item';
            newTab.innerHTML = `
                <button class="nav-link" 
                    id="day-tab-${dayIndex}" 
                    data-bs-toggle="tab" 
                    data-bs-target="#day-${dayIndex}" 
                    type="button" 
                    role="tab" 
                    aria-controls="day-${dayIndex}" 
                    aria-selected="false">
                    Day ${dayIndex + 1}
                </button>
            `;
            tabList.insertBefore(newTab, this.parentElement);

            // Add new tab content
            const tabContent = document.getElementById('dayTabsContent');
            const newContent = document.createElement('div');
            newContent.className = 'tab-pane fade';
            newContent.id = `day-${dayIndex}`;
            newContent.setAttribute('role', 'tabpanel');
            newContent.setAttribute('aria-labelledby', `day-tab-${dayIndex}`);
            newContent.innerHTML = `
                <div class="card itinerary-day">
                    <div class="card-body">
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-day">
                                <i class="bi bi-trash me-2"></i>Remove Day
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Accommodation</label>
                                <input type="text" class="form-control" 
                                    name="itinerary_days[${dayIndex}][accommodation]" 
                                    placeholder="Hotel name" required>
                                <div id="pac-container-${dayIndex}"></div>
                                <input type="hidden" name="itinerary_days[${dayIndex}][accommodation_address]" id="accommodation-address-${dayIndex}" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Meals</label>
                                <div class="card mb-2">
                                    <div class="card-body p-2">
                                        <div class="input-group">
                                            <span class="input-group-text fw-bold">Breakfast</span>
                                            <input type="text" class="form-control" 
                                                name="itinerary_days[${dayIndex}][meals][breakfast][name]" 
                                                placeholder="Restaurant name">
                                        </div>
                                        <input type="text" class="form-control mt-1" 
                                            name="itinerary_days[${dayIndex}][meals][breakfast][address]" 
                                            placeholder="Restaurant address">
                                        <div class="mt-2">
                                            <label class="form-label small text-muted">Photos</label>
                                            <input type="file" class="form-control form-control-sm" 
                                                name="itinerary_days[${dayIndex}][meals][breakfast][photos][]" 
                                                multiple accept="image/*">
                                            <div class="form-text small">Upload photos (max 2MB each)</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card mb-2">
                                    <div class="card-body p-2">
                                        <div class="input-group">
                                            <span class="input-group-text fw-bold">Lunch</span>
                                            <input type="text" class="form-control" 
                                                name="itinerary_days[${dayIndex}][meals][lunch][name]" 
                                                placeholder="Restaurant name">
                                        </div>
                                        <input type="text" class="form-control mt-1" 
                                            name="itinerary_days[${dayIndex}][meals][lunch][address]" 
                                            placeholder="Restaurant address">
                                        <div class="mt-2">
                                            <label class="form-label small text-muted">Photos</label>
                                            <input type="file" class="form-control form-control-sm" 
                                                name="itinerary_days[${dayIndex}][meals][lunch][photos][]" 
                                                multiple accept="image/*">
                                            <div class="form-text small">Upload photos (max 2MB each)</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body p-2">
                                        <div class="input-group">
                                            <span class="input-group-text fw-bold">Dinner</span>
                                            <input type="text" class="form-control" 
                                                name="itinerary_days[${dayIndex}][meals][dinner][name]" 
                                                placeholder="Restaurant name">
                                        </div>
                                        <input type="text" class="form-control mt-1" 
                                            name="itinerary_days[${dayIndex}][meals][dinner][address]" 
                                            placeholder="Restaurant address">
                                        <div class="mt-2">
                                            <label class="form-label small text-muted">Photos</label>
                                            <input type="file" class="form-control form-control-sm" 
                                                name="itinerary_days[${dayIndex}][meals][dinner][photos][]" 
                                                multiple accept="image/*">
                                            <div class="form-text small">Upload photos (max 2MB each)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-primary">Activities & Sightseeing</label>
                                <div id="activities-container-${dayIndex}" class="activities-container">
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm add-activity" data-day-index="${dayIndex}">
                                    <i class="bi bi-plus me-2"></i>Add Activity
                                </button>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-primary">Additional Notes</label>
                                <textarea class="form-control" 
                                    name="itinerary_days[${dayIndex}][notes]" 
                                    rows="2" 
                                    placeholder="Any additional information for this day"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="mt-3">
                                    <label class="form-label fw-bold text-primary">Receipts</label>
                                    <input type="file" class="form-control" 
                                        name="itinerary_days[${dayIndex}][receipts][]" 
                                        multiple accept="image/jpeg,image/png,application/pdf">
                                    <div class="form-text">Upload receipts (max 2MB each, JPEG, PNG, or PDF)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            tabContent.appendChild(newContent);

            // Activate the new tab
            const tab = new bootstrap.Tab(document.getElementById(`day-tab-${dayIndex}`));
            tab.show();
        });

        // Add activity
        document.addEventListener('click', function(e) {
            if (e.target.closest('.add-activity')) {
                const activitiesContainer = e.target.closest('.col-12').querySelector('.activities-container');
                const dayIndex = e.target.closest('.tab-pane').id.split('-')[1];
                const activityIndex = activitiesContainer.children.length;
                const activityDiv = document.createElement('div');
                activityDiv.className = 'card mb-2 activity-item';
                activityDiv.innerHTML = `
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control" 
                                    name="itinerary_days[${dayIndex}][activities][${activityIndex}][name]" 
                                    placeholder="Activity name" required>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" 
                                        name="itinerary_days[${dayIndex}][activities][${activityIndex}][entry_fee]" 
                                        placeholder="Entry fee" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control" 
                                    name="itinerary_days[${dayIndex}][activities][${activityIndex}][address]" 
                                    placeholder="Activity address">
                            </div>
                            <div class="col-12">
                                <textarea class="form-control" 
                                    name="itinerary_days[${dayIndex}][activities][${activityIndex}][description]" 
                                    placeholder="Activity description"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="mt-2">
                                    <label class="form-label small text-muted">Photos</label>
                                    <input type="file" class="form-control form-control-sm" 
                                        name="itinerary_days[${dayIndex}][activities][${activityIndex}][photos][]" 
                                        multiple accept="image/*">
                                    <div class="form-text small">Upload photos (max 2MB each)</div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm mt-2 remove-activity">
                            <i class="bi bi-trash me-2"></i>Remove Activity
                        </button>
                    </div>
                `;
                activitiesContainer.appendChild(activityDiv);
            }
        });

        // Remove day
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-day')) {
                const dayElement = e.target.closest('.tab-pane');
                const dayIndex = dayElement.id.split('-')[1];
                
                // Remove the tab
                document.getElementById(`day-tab-${dayIndex}`).parentElement.remove();
                // Remove the content
                dayElement.remove();

                // Update remaining day numbers
                document.querySelectorAll('.nav-link').forEach((tab, index) => {
                    if (tab.id.startsWith('day-tab-')) {
                        tab.textContent = `Day ${index + 1}`;
                        const dayId = tab.id.split('-')[2];
                        tab.setAttribute('data-bs-target', `#day-${index}`);
                        tab.setAttribute('aria-controls', `day-${index}`);
                        tab.id = `day-tab-${index}`;
                    }
                });

                document.querySelectorAll('.tab-pane').forEach((pane, index) => {
                    pane.id = `day-${index}`;
                    pane.setAttribute('aria-labelledby', `day-tab-${index}`);
                });

                // Update all name attributes
                document.querySelectorAll('[name^="itinerary_days["]').forEach(input => {
                    input.name = input.name.replace(/itinerary_days\[\d+\]/, `itinerary_days[${input.closest('.tab-pane').id.split('-')[1]}]`);
                });

                // Activate the first tab if any remain
                const remainingTabs = document.querySelectorAll('.nav-link');
                if (remainingTabs.length > 0) {
                    const tab = new bootstrap.Tab(remainingTabs[0]);
                    tab.show();
                }
            }
        });

        // Remove activity
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-activity')) {
                e.target.closest('.card').remove();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Handle photo deletion
            document.querySelectorAll('.delete-photo').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this photo?')) {
                        const photoPath = this.dataset.photoPath;
                        const dayId = this.dataset.dayId;
                        
                        console.log('Deleting photo:', photoPath, 'for day:', dayId);
                        
                        fetch(`/itineraries/days/${dayId}/photos/${encodeURIComponent(photoPath)}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('Response data:', data);
                            if (data.success) {
                                // Remove the photo container from the DOM
                                this.closest('.col-4').remove();
                            } else {
                                alert('Failed to delete photo: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to delete photo: ' + error.message);
                        });
                    }
                });
            });
        });

        function initializePlaceAutocomplete(index) {
            const container = document.getElementById(`pac-container-${index}`);
            if (!container) return;

            // Remove any existing element
            container.innerHTML = '';

            // Create the PlaceAutocompleteElement
            const pac = new google.maps.places.PlaceAutocompleteElement();
            pac.id = `pac-${index}`;
            container.appendChild(pac);

            // Listen for place selection
            pac.addEventListener('gmp-placeautocomplete-placechanged', (event) => {
                const place = event.detail;
                // Set the hidden input value to the formatted address
                document.getElementById(`accommodation-address-${index}`).value = place.formatted_address || place.displayName;
            });
        }

        // Initialize for existing days
        document.addEventListener('DOMContentLoaded', () => {
            const days = document.querySelectorAll('.itinerary-day');
            days.forEach((day, index) => {
                initializePlaceAutocomplete(index);
            });
        });

        // Initialize for newly added days
        document.getElementById('add-day').addEventListener('click', () => {
            const dayIndex = document.querySelectorAll('.itinerary-day').length;
            initializePlaceAutocomplete(dayIndex);
        });
    </script>
    @endpush
</x-app-layout> 