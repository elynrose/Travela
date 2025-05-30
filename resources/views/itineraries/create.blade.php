<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Create Itinerary</h2>
            <a href="{{ route('itineraries.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Itineraries
            </a>
        </div>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('itineraries.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">
                    <!-- Basic Information -->
                    <div class="col-md-8">
                        <div class="mb-4">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                id="title" name="title" value="{{ old('title') }}" required>
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
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                    id="location" name="location" value="{{ old('location') }}" required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                    id="country" name="country" value="{{ old('country') }}" required>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="accommodation" class="form-label">Accommodation</label>
                                <input type="text" class="form-control @error('accommodation') is-invalid @enderror" 
                                    id="accommodation" name="accommodation" value="{{ old('accommodation') }}" required>
                                @error('accommodation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="accommodation_address" class="form-label">Accommodation Address</label>
                                <input type="text" class="form-control @error('accommodation_address') is-invalid @enderror" 
                                    id="accommodation_address" name="accommodation_address" value="{{ old('accommodation_address') }}">
                                @error('accommodation_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cover_image" class="form-label">Cover Image</label>
                            <input type="file" 
                                   class="form-control @error('cover_image') is-invalid @enderror" 
                                   id="cover_image" 
                                   name="cover_image" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif"
                                   required>
                            <div class="form-text">Upload a cover image (max 2MB, JPEG, PNG, or GIF)</div>
                            @error('cover_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="gallery" class="form-label">Gallery Images</label>
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
                    </div>

                    <!-- Price and Duration -->
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label for="price" class="form-label">Price ($)</label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                id="price" name="price" value="{{ old('price') }}" min="0" step="0.01" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="duration_days" class="form-label">Duration (days)</label>
                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                id="duration_days" name="duration_days" value="{{ old('duration_days') }}" min="1" required>
                            @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="categories" class="form-label">Categories</label>
                            <select class="form-select @error('categories') is-invalid @enderror" 
                                id="categories" name="categories[]" multiple required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ in_array($category->id, old('categories', [])) ? 'selected' : '' }}>
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
                                @foreach(old('highlights', []) as $index => $highlight)
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
                                @foreach(old('included_items', []) as $index => $item)
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
                                @foreach(old('excluded_items', []) as $index => $item)
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
                                @foreach(old('requirements', []) as $index => $requirement)
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

                    <!-- Publish Checkbox -->
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">
                                Publish this itinerary
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Create Itinerary
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Add Highlight
        document.getElementById('add-highlight').addEventListener('click', function() {
            const container = document.getElementById('highlights-container');
            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group mb-2';
            inputGroup.innerHTML = `
                <input type="text" class="form-control" name="highlights[]" required>
                <button type="button" class="btn btn-outline-danger remove-highlight">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(inputGroup);
        });

        // Remove Highlight
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-highlight')) {
                e.target.closest('.input-group').remove();
            }
        });

        // Add Included Item
        document.getElementById('add-included-item').addEventListener('click', function() {
            const container = document.getElementById('included-items-container');
            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group mb-2';
            inputGroup.innerHTML = `
                <input type="text" class="form-control" name="included_items[]" required>
                <button type="button" class="btn btn-outline-danger remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(inputGroup);
        });

        // Add Excluded Item
        document.getElementById('add-excluded-item').addEventListener('click', function() {
            const container = document.getElementById('excluded-items-container');
            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group mb-2';
            inputGroup.innerHTML = `
                <input type="text" class="form-control" name="excluded_items[]" required>
                <button type="button" class="btn btn-outline-danger remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(inputGroup);
        });

        // Remove Item
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                e.target.closest('.input-group').remove();
            }
        });

        // Add Requirement
        document.getElementById('add-requirement').addEventListener('click', function() {
            const container = document.getElementById('requirements-container');
            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group mb-2';
            inputGroup.innerHTML = `
                <input type="text" class="form-control" name="requirements[]" required>
                <button type="button" class="btn btn-outline-danger remove-requirement">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(inputGroup);
        });

        // Remove Requirement
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-requirement')) {
                e.target.closest('.input-group').remove();
            }
        });
    </script>
    @endpush
</x-app-layout> 