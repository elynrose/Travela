<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($itinerary) ? 'Edit Itinerary' : 'Create Itinerary' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ isset($itinerary) ? route('itineraries.update', $itinerary) : route('itineraries.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @if(isset($itinerary))
                    @method('PUT')
                @endif

                <!-- Basic Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Title -->
                            <div class="sm:col-span-2">
                                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $itinerary->title ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="sm:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $itinerary->description ?? '') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                <input type="text" name="location" id="location" value="{{ old('location', $itinerary->location ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('location')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                                <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price', $itinerary->price ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Categories -->
                            <div class="sm:col-span-2">
                                <label for="categories" class="block text-sm font-medium text-gray-700">Categories</label>
                                <select name="categories[]" id="categories" multiple required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ isset($itinerary) && $itinerary->categories->contains($category->id) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categories')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cover Image -->
                            <div class="sm:col-span-2">
                                <label for="cover_image" class="block text-sm font-medium text-gray-700">Cover Image</label>
                                @if(isset($itinerary) && $itinerary->cover_image)
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($itinerary->cover_image) }}" alt="Current cover image" class="h-32 w-32 object-cover rounded" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    </div>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 128px; width: 128px; display: none;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                                <input type="file" name="cover_image" id="cover_image" accept="image/*" {{ !isset($itinerary) ? 'required' : '' }} class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @error('cover_image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Itinerary Days -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Itinerary Days</h3>
                            <button type="button" onclick="addDay()" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Day
                            </button>
                        </div>

                        <div id="days-container" class="space-y-6">
                            @if(isset($itinerary) && $itinerary->days->count() > 0)
                                @foreach($itinerary->days as $index => $day)
                                    <div class="day-container border rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-4">
                                            <h4 class="text-md font-medium text-gray-900">Day {{ $index + 1 }}</h4>
                                            <button type="button" onclick="removeDay(this)" class="text-red-600 hover:text-red-800">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                                <input type="text" name="days[{{ $index }}][title]" value="{{ $day->title }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                                <textarea name="days[{{ $index }}][description]" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $day->description }}</textarea>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Items</label>
                                                <div class="items-container space-y-4 mt-2">
                                                    @foreach($day->items as $itemIndex => $item)
                                                        <div class="item-container border rounded p-3">
                                                            <div class="flex justify-between items-start mb-2">
                                                                <h5 class="text-sm font-medium text-gray-900">Item {{ $itemIndex + 1 }}</h5>
                                                                <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-800">
                                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>

                                                            <div class="grid grid-cols-1 gap-3">
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700">Title</label>
                                                                    <input type="text" name="days[{{ $index }}][items][{{ $itemIndex }}][title]" value="{{ $item->title }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                                </div>

                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                                                    <textarea name="days[{{ $index }}][items][{{ $itemIndex }}][description]" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $item->description }}</textarea>
                                                                </div>

                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700">Location</label>
                                                                    <input type="text" name="days[{{ $index }}][items][{{ $itemIndex }}][location]" value="{{ $item->location }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                                </div>

                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700">Image</label>
                                                                    @if($item->image)
                                                                        <div class="mt-2">
                                                                            <img src="{{ Storage::url($item->image) }}" alt="Current item image" class="h-20 w-20 object-cover rounded" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                        </div>
                                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px; width: 80px; display: none;">
                                                                            <i class="bi bi-image text-muted"></i>
                                                                        </div>
                                                                    @endif
                                                                    <input type="file" name="days[{{ $index }}][items][{{ $itemIndex }}][image]" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" onclick="addItem(this)" class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Add Item
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ isset($itinerary) ? 'Update Itinerary' : 'Create Itinerary' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let dayCount = {{ isset($itinerary) ? $itinerary->days->count() : 0 }};
        let itemCounts = {};

        function addDay() {
            const container = document.getElementById('days-container');
            const dayHtml = `
                <div class="day-container border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-md font-medium text-gray-900">Day ${dayCount + 1}</h4>
                        <button type="button" onclick="removeDay(this)" class="text-red-600 hover:text-red-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="days[${dayCount}][title]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="days[${dayCount}][description]" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Items</label>
                            <div class="items-container space-y-4 mt-2"></div>
                            <button type="button" onclick="addItem(this)" class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Item
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', dayHtml);
            dayCount++;
        }

        function removeDay(button) {
            button.closest('.day-container').remove();
            updateDayNumbers();
        }

        function updateDayNumbers() {
            const days = document.querySelectorAll('.day-container');
            days.forEach((day, index) => {
                day.querySelector('h4').textContent = `Day ${index + 1}`;
                const inputs = day.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.name = input.name.replace(/days\[\d+\]/, `days[${index}]`);
                });
            });
            dayCount = days.length;
        }

        function addItem(button) {
            const container = button.previousElementSibling;
            const dayIndex = button.closest('.day-container').querySelector('input').name.match(/days\[(\d+)\]/)[1];
            const itemCount = container.children.length;

            const itemHtml = `
                <div class="item-container border rounded p-3">
                    <div class="flex justify-between items-start mb-2">
                        <h5 class="text-sm font-medium text-gray-900">Item ${itemCount + 1}</h5>
                        <button type="button" onclick="removeItem(this)" class="text-red-600 hover:text-red-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="days[${dayIndex}][items][${itemCount}][title]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="days[${dayIndex}][items][${itemCount}][description]" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <input type="text" name="days[${dayIndex}][items][${itemCount}][location]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Image</label>
                            <input type="file" name="days[${dayIndex}][items][${itemCount}][image]" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', itemHtml);
        }

        function removeItem(button) {
            button.closest('.item-container').remove();
            updateItemNumbers(button.closest('.items-container'));
        }

        function updateItemNumbers(container) {
            const items = container.querySelectorAll('.item-container');
            items.forEach((item, index) => {
                item.querySelector('h5').textContent = `Item ${index + 1}`;
                const inputs = item.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
                });
            });
        }
    </script>
    @endpush
</x-app-layout> 