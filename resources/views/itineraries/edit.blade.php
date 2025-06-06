<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Edit Itinerary</h2>
            <a href="{{ route('itineraries.show', $itinerary) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Itinerary
            </a>
        </div>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- User Alerts --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('itineraries.update', $itinerary) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Cover Image Banner -->
                    <div class="col-12">
                        <div class="position-relative mb-4">
                            @if($itinerary->getCoverImageUrl())
                                <img src="{{ $itinerary->getCoverImageUrl() }}" 
                                     alt="{{ $itinerary->title ?? 'Itinerary Cover' }}" 
                                     class="img-fluid w-100 rounded shadow-sm" 
                                     style="max-height: 300px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded shadow-sm" style="height: 300px;">
                                    <svg width="64" height="64" fill="currentColor" class="bi bi-image text-muted" viewBox="0 0 16 16">
                                        <path d="M14.002 3H2c-.55 0-1 .45-1 1v8c0 .55.45 1 1 1h12.002c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1zM2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12.002a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2zm10.002 7.5l-2.5 3.5H2v-1l3-4 2.5 3.5 2.5-3.5 3 4v1h-3.998z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="position-absolute top-0 end-0 p-3">
                                <label for="cover_image" class="btn btn-light rounded-circle">
                                    <i class="bi bi-pencil"></i>
                                </label>
                                <input type="file" 
                                       class="d-none" 
                                       id="cover_image" 
                                       name="cover_image" 
                                       accept="image/jpeg,image/png,image/jpg,image/gif">
                            </div>
                            @error('cover_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="col-md-8">
                        <div class="mb-4">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                id="title" name="title" value="{{ old('title', $itinerary->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="5" 
                                      required>{{ old('description', $itinerary->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                    id="location" name="location" value="{{ old('location', $itinerary->location) }}" required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                    id="country" name="country" value="{{ old('country', $itinerary->country) }}" required>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Price and Duration -->
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label for="price" class="form-label">Price ($)</label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                id="price" name="price" value="{{ old('price', $itinerary->price) }}" min="0" step="0.01" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="duration_days" class="form-label">Duration (days)</label>
                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                id="duration_days" name="duration_days" value="{{ old('duration_days', $itinerary->days_count) }}" min="1" required>
                            @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Total number of days in this itinerary</small>
                        </div>

                        <div class="mb-4">
                            <label for="categories" class="form-label">Categories</label>
                            <select class="form-select @error('categories') is-invalid @enderror" 
                                id="categories" name="categories[]" multiple required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ in_array($category->id, old('categories', $itinerary->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categories')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Highlights -->
                    <div class="col-12">
                        <div class="mb-4">
                            <label class="form-label">Highlights</label>
                            <div id="highlights-container">
                                @foreach(old('highlights', $itinerary->highlights) as $index => $highlight)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" 
                                            name="highlights[]" value="{{ $highlight }}" required>
                                        <button type="button" class="btn btn-outline-danger remove-highlight">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-highlight">
                                <i class="bi bi-plus me-2"></i>Add Highlight
                            </button>
                        </div>
                    </div>

                    <!-- Included Items -->
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">What's Included</label>
                            <div id="included-items-container">
                                @foreach(old('included_items', $itinerary->included_items) as $index => $item)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" 
                                            name="included_items[]" value="{{ $item }}" required>
                                        <button type="button" class="btn btn-outline-danger remove-item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-included-item">
                                <i class="bi bi-plus me-2"></i>Add Item
                            </button>
                        </div>
                    </div>

                    <!-- Excluded Items -->
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">What's Not Included</label>
                            <div id="excluded-items-container">
                                @foreach(old('excluded_items', $itinerary->excluded_items) as $index => $item)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" 
                                            name="excluded_items[]" value="{{ $item }}" required>
                                        <button type="button" class="btn btn-outline-danger remove-item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-excluded-item">
                                <i class="bi bi-plus me-2"></i>Add Item
                            </button>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="col-12">
                        <div class="mb-4">
                            <label class="form-label">Requirements</label>
                            <div id="requirements-container">
                                @foreach(old('requirements', $itinerary->requirements ?? []) as $index => $requirement)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" 
                                            name="requirements[]" value="{{ $requirement }}" required>
                                        <button type="button" class="btn btn-outline-danger remove-requirement">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-requirement">
                                <i class="bi bi-plus me-2"></i>Add Requirement
                            </button>
                        </div>
                    </div>

                    <!-- Travel Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-3">Travel Information</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary">Transportation Type</label>
                                    <select class="form-select" name="transportation_type" required>
                                        <option value="flight" {{ old('transportation_type', $itinerary->transportation_type) === 'flight' ? 'selected' : '' }}>Flight</option>
                                        <option value="road" {{ old('transportation_type', $itinerary->transportation_type) === 'road' ? 'selected' : '' }}>Road</option>
                                        <option value="both" {{ old('transportation_type', $itinerary->transportation_type) === 'both' ? 'selected' : '' }}>Both Flight and Road</option>
                                    </select>
                                </div>

                                <!-- Flight Information -->
                                <div class="col-md-6 flight-info">
                                    <label class="form-label fw-bold text-primary">Flight Duration</label>
                                    <input type="text" class="form-control" name="flight_duration" value="{{ old('flight_duration', $itinerary->flight_duration) }}" placeholder="e.g., 2 hours 30 minutes">
                                </div>

                                <div class="col-md-6 flight-info">
                                    <label class="form-label fw-bold text-primary">Airfare Range</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" name="airfare_min" value="{{ old('airfare_min', $itinerary->airfare_min) }}" placeholder="Min">
                                        <span class="input-group-text">to</span>
                                        <input type="number" class="form-control" name="airfare_max" value="{{ old('airfare_max', $itinerary->airfare_max) }}" placeholder="Max">
                                    </div>
                                </div>

                                <div class="col-md-6 flight-info">
                                    <label class="form-label fw-bold text-primary">Booking Website</label>
                                    <input type="url" class="form-control" name="booking_website" value="{{ old('booking_website', $itinerary->booking_website) }}" placeholder="https://example.com">
                                </div>

                                <!-- Road Information -->
                                <div class="col-md-6 road-info">
                                    <label class="form-label fw-bold text-primary">Road Distance</label>
                                    <input type="text" class="form-control" name="road_distance" value="{{ old('road_distance', $itinerary->road_distance) }}" placeholder="e.g., 300 km">
                                </div>

                                <div class="col-md-6 road-info">
                                    <label class="form-label fw-bold text-primary">Road Duration</label>
                                    <input type="text" class="form-control" name="road_duration" value="{{ old('road_duration', $itinerary->road_duration) }}" placeholder="e.g., 4 hours">
                                </div>

                                <div class="col-md-6 road-info">
                                    <label class="form-label fw-bold text-primary">Road Type</label>
                                    <select class="form-select" name="road_type">
                                        <option value="highway" {{ old('road_type', $itinerary->road_type) === 'highway' ? 'selected' : '' }}>Highway</option>
                                        <option value="local" {{ old('road_type', $itinerary->road_type) === 'local' ? 'selected' : '' }}>Local Roads</option>
                                        <option value="mixed" {{ old('road_type', $itinerary->road_type) === 'mixed' ? 'selected' : '' }}>Mixed</option>
                                    </select>
                                </div>

                                <!-- Common Information -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary">Languages Spoken</label>
                                    <input type="text" class="form-control" name="languages" value="{{ old('languages', $itinerary->languages) }}" placeholder="e.g., English, Swahili">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary">Peak Travel Times</label>
                                    <input type="text" class="form-control" name="peak_travel_times" value="{{ old('peak_travel_times', $itinerary->peak_travel_times) }}" placeholder="e.g., June to September">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary">Travel Agency</label>
                                    <input type="text" class="form-control" name="travel_agency" value="{{ old('travel_agency', $itinerary->travel_agency) }}" placeholder="Agency name">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary">Agency Fees</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" name="agency_fees" value="{{ old('agency_fees', $itinerary->agency_fees) }}" placeholder="Agency fees">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-primary">Additional Travel Notes</label>
                                    <textarea class="form-control" name="travel_notes" rows="3" placeholder="Any additional travel information, requirements, or tips">{{ old('travel_notes', $itinerary->travel_notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery Images Card -->
                    <div class="col-12">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h3 class="h5 mb-3">Gallery Images</h3>
                                <div class="mb-3">
                                    <label for="gallery" class="form-label">Upload Gallery Images</label>
                                    <input type="file" 
                                           class="form-control @error('gallery') is-invalid @enderror" 
                                           id="gallery" 
                                           name="gallery[]" 
                                           accept="image/jpeg,image/png,image/jpg,image/gif"
                                           multiple>
                                    <div class="form-text">Upload gallery images (max 2MB each, JPEG, PNG, or GIF)</div>
                                    @error('gallery')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Current Gallery Thumbnails -->
                                @if($itinerary->gallery)
                                    <div class="mb-3">
                                        <label class="form-label">Current Gallery Images</label>
                                        <div class="row g-2">
                                            @foreach($itinerary->gallery as $image)
                                                <div class="col-md-3 position-relative">
                                                    <img src="{{ asset('storage/' . $image) }}" 
                                                         alt="Gallery image" 
                                                         class="img-thumbnail w-100 gallery-image-preview" 
                                                         style="max-height: 150px; object-fit: cover; cursor: pointer;"
                                                         data-bs-toggle="modal" data-bs-target="#galleryImageModal" data-image="{{ asset('storage/' . $image) }}">
                                                    <button type="button" class="btn btn-sm btn-danger delete-gallery-image" data-image="{{ urlencode($image) }}" style="position: absolute; top: 8px; right: 8px;" title="Delete image">
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

                    <!-- Publish Checkbox -->
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $itinerary->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">
                                Publish this itinerary
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('itineraries.show', $itinerary) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Itinerary</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Gallery Image Modal --}}
    <div class="modal fade" id="galleryImageModal" tabindex="-1" aria-labelledby="galleryImageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="galleryImageModalLabel">Gallery Image</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <img src="" alt="Gallery Image" id="galleryImageModalImg" class="img-fluid rounded shadow-sm" style="max-height: 70vh;">
          </div>
        </div>
      </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Add highlight
        document.getElementById('add-highlight').addEventListener('click', function() {
            const container = document.getElementById('highlights-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="highlights[]" required>
                <button type="button" class="btn btn-outline-danger remove-highlight">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(div);
        });

        // Remove highlight
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-highlight')) {
                e.target.closest('.input-group').remove();
            }
        });

        // Add included item
        document.getElementById('add-included-item').addEventListener('click', function() {
            const container = document.getElementById('included-items-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="included_items[]" required>
                <button type="button" class="btn btn-outline-danger remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(div);
        });

        // Add excluded item
        document.getElementById('add-excluded-item').addEventListener('click', function() {
            const container = document.getElementById('excluded-items-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="excluded_items[]" required>
                <button type="button" class="btn btn-outline-danger remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(div);
        });

        // Remove item (included or excluded)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                e.target.closest('.input-group').remove();
            }
        });

        // Add requirement
        document.getElementById('add-requirement').addEventListener('click', function() {
            const container = document.getElementById('requirements-container');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="requirements[]" required>
                <button type="button" class="btn btn-outline-danger remove-requirement">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(div);
        });

        // Remove requirement
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-requirement')) {
                e.target.closest('.input-group').remove();
            }
        });

        // Show/hide transportation specific fields
        document.addEventListener('DOMContentLoaded', function() {
            const transportationType = document.querySelector('select[name="transportation_type"]');
            const flightInfo = document.querySelectorAll('.flight-info');
            const roadInfo = document.querySelectorAll('.road-info');

            function updateFields() {
                const value = transportationType.value;
                flightInfo.forEach(el => el.style.display = (value === 'flight' || value === 'both') ? 'block' : 'none');
                roadInfo.forEach(el => el.style.display = (value === 'road' || value === 'both') ? 'block' : 'none');
            }

            transportationType.addEventListener('change', updateFields);
            updateFields(); // Initial state
        });

        // Gallery image deletion via AJAX using POST with _method=DELETE
        $(document).ready(function() {
            $('.delete-gallery-image').on('click', function() {
                const image = $(this).data('image');
                const button = $(this);
                const imageContainer = button.closest('.col-md-3');

                if (confirm('Are you sure you want to delete this image?')) {
                    // Use slug instead of ID
                    const itinerarySlug = `{{ $itinerary->slug }}`;
                    const url = `/itineraries/${itinerarySlug}/gallery?image=${image}`;
                    
                    // Show loading state
                    button.prop('disabled', true);
                    button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success message
                                const alertDiv = $('<div>')
                                    .addClass('alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3')
                                    .html(`
                                        ${response.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    `);
                                $('body').append(alertDiv);
                                setTimeout(() => alertDiv.remove(), 3000);

                                // Remove the image container with a fade effect
                                imageContainer.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                throw new Error(response.message || 'Failed to delete image');
                            }
                        },
                        error: function(xhr) {
                            console.error('Delete error:', xhr.status, xhr.responseText);
                            let errorMessage = 'Failed to delete gallery image.';
                            
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                console.error('Error parsing error response:', e);
                            }

                            // Show error message
                            const alertDiv = $('<div>')
                                .addClass('alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3')
                                .html(`
                                    ${errorMessage}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                `);
                            $('body').append(alertDiv);
                            setTimeout(() => alertDiv.remove(), 5000);
                        },
                        complete: function() {
                            // Reset button state
                            button.prop('disabled', false);
                            button.html('<i class="bi bi-trash"></i>');
                        }
                    });
                }
            });

            // Handle gallery upload errors
            $('#gallery').on('change', function(e) {
                const files = e.target.files;
                let hasError = false;
                let errorMessage = '';

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    
                    // Check file size (2MB limit)
                    if (file.size > 2 * 1024 * 1024) {
                        errorMessage = `File "${file.name}" exceeds 2MB size limit.`;
                        hasError = true;
                        break;
                    }

                    // Check file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        errorMessage = `File "${file.name}" is not a valid image type. Allowed types: JPEG, PNG, JPG, GIF.`;
                        hasError = true;
                        break;
                    }
                }

                if (hasError) {
                    // Show error message
                    const alertDiv = $('<div>')
                        .addClass('alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3')
                        .html(`
                            ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `);
                    $('body').append(alertDiv);
                    setTimeout(() => alertDiv.remove(), 5000);

                    // Clear the file input
                    $(this).val('');
                }
            });

            // Gallery image modal preview
            $(document).on('click', '.gallery-image-preview', function() {
                const imgSrc = $(this).data('image');
                $('#galleryImageModalImg').attr('src', imgSrc);
            });
        });

        document.getElementById('cover_image').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const formData = new FormData();
                formData.append('cover_image', e.target.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                // Show loading state
                const img = document.querySelector('.position-relative img');
                if (img) {
                    img.style.opacity = '0.5';
                }

                // Add loading indicator
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'position-absolute top-50 start-50 translate-middle';
                loadingDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                document.querySelector('.position-relative').appendChild(loadingDiv);

                fetch('{{ route('itineraries.uploadCoverImage', $itinerary) }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}, message: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update the image
                        const img = document.querySelector('.position-relative img');
                        if (img) {
                            img.src = data.cover_image_url;
                            img.style.opacity = '1';
                        } else {
                            // If no image exists, create one
                            const newImg = document.createElement('img');
                            newImg.src = data.cover_image_url;
                            newImg.alt = 'Cover image';
                            newImg.className = 'img-fluid w-100';
                            newImg.style.maxHeight = '300px';
                            newImg.style.objectFit = 'cover';
                            document.querySelector('.position-relative').prepend(newImg);
                        }
                        // Show success message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                        alertDiv.innerHTML = `
                            Cover image updated successfully
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        document.body.appendChild(alertDiv);
                        setTimeout(() => alertDiv.remove(), 3000);
                    } else {
                        throw new Error(data.message || 'Upload failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Show detailed error message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    alertDiv.innerHTML = `
                        Upload failed: ${error.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.body.appendChild(alertDiv);
                    setTimeout(() => alertDiv.remove(), 5000);
                })
                .finally(() => {
                    // Remove loading indicator
                    const loadingDiv = document.querySelector('.position-relative .spinner-border');
                    if (loadingDiv) {
                        loadingDiv.parentElement.remove();
                    }
                    // Reset opacity
                    const img = document.querySelector('.position-relative img');
                    if (img) {
                        img.style.opacity = '1';
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout> 