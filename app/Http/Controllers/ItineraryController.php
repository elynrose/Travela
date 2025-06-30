<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Itinerary;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Models\Day;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use App\Services\GeocodingService;
use App\Jobs\GeocodeItineraryJob;
use App\Jobs\GeocodeDayJob;

class ItineraryController extends Controller
{
    protected $imageManager;
    protected $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->geocodingService = $geocodingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $query = Itinerary::with(['user', 'categories', 'days'])
                ->where('is_published', true);

            // Apply filters
            if (request('location')) {
                $query->where('location', 'like', '%' . request('location') . '%');
            }

            if (request('duration')) {
                $duration = explode('-', request('duration'));
                if (count($duration) === 2) {
                    $query->whereBetween('duration_days', $duration);
                } elseif (request('duration') === '15+') {
                    $query->where('duration_days', '>=', 15);
                }
            }

            if (request('price_range')) {
                $priceRange = explode('-', request('price_range'));
                if (count($priceRange) === 2) {
                    $query->whereBetween('price', $priceRange);
                } elseif (request('price_range') === '1001+') {
                    $query->where('price', '>=', 1001);
                }
            }

            if (request('category')) {
                $query->whereHas('categories', function ($q) {
                    $q->where('categories.id', request('category'));
                });
            }

            $itineraries = $query->latest()->paginate(12);
            
            $categories = Category::where('is_active', true)->get();

            return view('itineraries.index', compact('itineraries', 'categories'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading itineraries. Please try again later.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('itineraries.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'accommodation' => 'required|string|max:255',
            'accommodation_address' => 'nullable|string|max:255',
            'highlights' => 'required|array|min:1',
            'highlights.*' => 'required|string|max:255',
            'included_items' => 'required|array|min:1',
            'included_items.*' => 'required|string|max:255',
            'excluded_items' => 'required|array|min:1',
            'excluded_items.*' => 'required|string|max:255',
            'requirements' => 'required|array|min:1',
            'requirements.*' => 'required|string|max:255',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ]);

        DB::beginTransaction();

        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']) . '-' . time();
        $validated['is_published'] = $request->has('is_published') ? true : false;
        $validated['is_featured'] = false;

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            try {
                $image = $this->imageManager->read($file);
                
                // Save original image using Laravel's store method
                $originalPath = $file->store('covers', 'public');
                
                if (!$originalPath) {
                    throw new \Exception('Failed to store the original image');
                }

                // Create thumbnail using Intervention Image
                $image = $this->imageManager->read($file);
                $thumbPath = 'covers/thumbnails/' . $filename;
                
                // Save thumbnail to temporary file first, then upload to cloud storage
                $tempThumbPath = storage_path('app/temp/' . $filename);
                
                // Ensure temp directory exists
                if (!file_exists(dirname($tempThumbPath))) {
                    mkdir(dirname($tempThumbPath), 0755, true);
                }
                
                $image->cover(800, 400)->save($tempThumbPath);
                
                // Upload thumbnail to cloud storage
                $thumbStream = fopen($tempThumbPath, 'r');
                Storage::disk('public')->put($thumbPath, $thumbStream);
                fclose($thumbStream);
                
                // Clean up temp file
                unlink($tempThumbPath);

                // Update the cover_image field in the database
                $validated['cover_image'] = $originalPath;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        $itinerary = Itinerary::create($validated);

        $itinerary->categories()->attach($request->input('categories'));

        // Handle gallery uploads
        if ($request->hasFile('gallery')) {
            $request->validate([
                'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'gallery.*.image' => 'Each file must be an image.',
                'gallery.*.mimes' => 'Each image must be a JPEG, PNG, JPG, or GIF.',
                'gallery.*.max' => 'Each image must not exceed 2MB.'
            ]);

            $gallery = $itinerary->gallery ?? [];
            foreach ($request->file('gallery') as $image) {
                try {
                    // Validate file size
                    if ($image->getSize() > 2048 * 1024) {
                        throw new \Exception('Image size exceeds 2MB limit: ' . $image->getClientOriginalName());
                    }

                    // Validate mime type
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                    if (!in_array($image->getMimeType(), $allowedMimes)) {
                        throw new \Exception('Invalid file type: ' . $image->getClientOriginalName());
                    }

                    $filename = time() . '_' . $image->getClientOriginalName();
                    $path = $image->store('gallery', 'public');
                    
                    if (!$path) {
                        throw new \Exception('Failed to store image: ' . $image->getClientOriginalName());
                    }

                    $gallery[] = $path;
                } catch (\Exception $e) {
                    throw new \Exception('Failed to upload gallery image: ' . $e->getMessage());
                }
            }
            $itinerary->update(['gallery' => $gallery]);
        }

        DB::commit();

        // If user wants to publish, check if there are days
        if ($validated['is_published']) {
            if ($itinerary->days()->count() < 1) {
                // Unpublish and redirect to add days
                $itinerary->update(['is_published' => false]);
                return redirect()->route('itineraries.editDays', $itinerary)
                    ->with('error', 'You must add at least one day before publishing.');
            }
        }

        // Dispatch geocoding job
        dispatch(new GeocodeItineraryJob($itinerary->id));

        return redirect()->route('itineraries.show', $itinerary)
            ->with('success', 'Itinerary created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Itinerary $itinerary)
    {
        try {
            $this->authorize('view', $itinerary);
            
            $itinerary->load(['user', 'categories', 'days']);

            // Verify critical data
            if (!$itinerary->user) {
                // Continue without the relationships rather than failing completely
            }

            return view('itineraries.show', compact('itinerary'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading the itinerary. Please try again later.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Itinerary $itinerary)
    {
        $this->authorize('update', $itinerary);
        $categories = Category::where('is_active', true)->get();
        $itinerary->loadCount('days');
        return view('itineraries.edit', compact('itinerary', 'categories'));
    }

    /**
     * Show the form for editing the day-by-day itinerary.
     */
    public function editDays(Itinerary $itinerary)
    {
        $this->authorize('update', $itinerary);
        
        // Fetch days ordered by day_number
        $days = $itinerary->days()->orderBy('day_number')->get();
        
        // Convert days to the format expected by the view
        $itineraryDays = $days->map(function($day) {
            return [
                'accommodation' => $day->accommodation,
                'accommodation_address' => $day->accommodation_address,
                'meals' => $day->meals,
                'activities' => $day->activities,
                'notes' => $day->notes
            ];
        })->toArray();

        return view('itineraries.days', compact('itinerary', 'itineraryDays'));
    }

    /**
     * Update the day-by-day itinerary.
     */
    public function updateDays(Request $request, Itinerary $itinerary)
    {
        $this->authorize('update', $itinerary);

        $request->validate([
            'itinerary_days' => 'required|array|min:1',
        ]);

        // Get existing days
        $existingDays = $itinerary->days()->get()->keyBy('day_number');

        // Update or create days
        foreach ($request->input('itinerary_days') as $index => $dayData) {
            // Get existing day or create new one
            $existingDay = $existingDays->get($index + 1);
            $day = $existingDay ?? $itinerary->days()->create([
                'day_number' => $index + 1,
                'accommodation' => $dayData['accommodation'] ?? null,
                'accommodation_address' => $dayData['accommodation_address'] ?? null,
                'meals' => [],
                'activities' => [],
                'notes' => $dayData['notes'] ?? null,
            ]);

            // Initialize arrays with existing data
            $meals = $day->meals ?? [];
            $activities = [];
            $receipts = $day->receipts ?? [];

            // Get coordinates for accommodation if address is provided
            if (!empty($dayData['accommodation_address'])) {
                $accommodationCoords = $this->geocodingService->getCoordinates(
                    $dayData['accommodation'],
                    $itinerary->country
                );
            }

            // Update meal data
            if (isset($dayData['meals'])) {
                foreach ($dayData['meals'] as $mealType => $mealData) {
                    // Preserve existing meal data
                    $existingMeal = $meals[$mealType] ?? [];
                    
                    // Get coordinates for meal location if address is provided
                    $mealCoords = null;
                    if (!empty($mealData['address'])) {
                        $mealCoords = $this->geocodingService->getCoordinates(
                            $mealData['name'],
                            $itinerary->country
                        );
                    }
                    
                    // Update meal info
                    $meals[$mealType] = array_merge($existingMeal, [
                        'name' => $mealData['name'] ?? $existingMeal['name'] ?? null,
                        'address' => $mealData['address'] ?? $existingMeal['address'] ?? null,
                        'description' => $mealData['description'] ?? $existingMeal['description'] ?? null,
                        'photos' => $existingMeal['photos'] ?? [],
                        'latitude' => $mealCoords['latitude'] ?? null,
                        'longitude' => $mealCoords['longitude'] ?? null
                    ]);

                    // Handle new meal photos
                    if (isset($request->file('itinerary_days')[$index]['meals'][$mealType]['photos'])) {
                        foreach ($request->file('itinerary_days')[$index]['meals'][$mealType]['photos'] as $photo) {
                            // Create image instance
                            $image = $this->imageManager->read($photo);
                            
                            // Generate unique filename
                            $filename = uniqid() . '.' . $photo->getClientOriginalExtension();
                            $path = 'meals/' . $day->id . '/' . $mealType . '/' . $filename;
                            
                            // Save original image using Laravel's store method
                            $originalPath = $photo->store('meals/' . $day->id . '/' . $mealType, 'public');
                            
                            if (!$originalPath) {
                                throw new \Exception('Failed to store meal image');
                            }
                            
                            // Create thumbnail using temporary file
                            $tempThumbPath = storage_path('app/temp/thumb_' . $filename);
                            
                            // Ensure temp directory exists
                            if (!file_exists(dirname($tempThumbPath))) {
                                mkdir(dirname($tempThumbPath), 0755, true);
                            }
                            
                            $image->cover(300, 300)->save($tempThumbPath);
                            
                            // Upload thumbnail to cloud storage
                            $thumbPath = 'meals/' . $day->id . '/' . $mealType . '/thumb_' . $filename;
                            $thumbStream = fopen($tempThumbPath, 'r');
                            Storage::disk('public')->put($thumbPath, $thumbStream);
                            fclose($thumbStream);
                            
                            // Clean up temp file
                            unlink($tempThumbPath);
                            
                            // Add new photo to existing photos
                            $meals[$mealType]['photos'][] = [
                                'path' => $originalPath,
                                'thumb_path' => $thumbPath,
                                'mime_type' => $photo->getMimeType()
                            ];
                        }
                    }
                }
            }

            // Update activity data
            if (isset($dayData['activities'])) {
                foreach ($dayData['activities'] as $activityIndex => $activityData) {
                    // Get existing activity data if it exists
                    $existingActivity = $day->activities[$activityIndex] ?? [];
                    
                    // Get coordinates for activity location if address is provided
                    $activityCoords = null;
                    if (!empty($activityData['address'])) {
                        $activityCoords = $this->geocodingService->getCoordinates(
                            $activityData['name'],
                            $itinerary->country
                        );
                    }
                    
                    // Create new activity data
                    $activities[$activityIndex] = [
                        'name' => $activityData['name'] ?? $existingActivity['name'] ?? null,
                        'address' => $activityData['address'] ?? $existingActivity['address'] ?? null,
                        'description' => $activityData['description'] ?? $existingActivity['description'] ?? null,
                        'entry_fee' => $activityData['entry_fee'] ?? $existingActivity['entry_fee'] ?? null,
                        'photos' => $existingActivity['photos'] ?? [],
                        'latitude' => $activityCoords['latitude'] ?? null,
                        'longitude' => $activityCoords['longitude'] ?? null
                    ];

                    // Handle new activity photos
                    if (isset($request->file('itinerary_days')[$index]['activities'][$activityIndex]['photos'])) {
                        foreach ($request->file('itinerary_days')[$index]['activities'][$activityIndex]['photos'] as $photo) {
                            // Create image instance
                            $image = $this->imageManager->read($photo);
                            
                            // Generate unique filename
                            $filename = uniqid() . '.' . $photo->getClientOriginalExtension();
                            
                            // Save original image using Laravel's store method
                            $originalPath = $photo->store('activities/' . $day->id . '/' . $activityIndex, 'public');
                            
                            if (!$originalPath) {
                                throw new \Exception('Failed to store activity image');
                            }
                            
                            // Create thumbnail using temporary file
                            $tempThumbPath = storage_path('app/temp/thumb_' . $filename);
                            
                            // Ensure temp directory exists
                            if (!file_exists(dirname($tempThumbPath))) {
                                mkdir(dirname($tempThumbPath), 0755, true);
                            }
                            
                            $image->cover(300, 300)->save($tempThumbPath);
                            
                            // Upload thumbnail to cloud storage
                            $thumbPath = 'activities/' . $day->id . '/' . $activityIndex . '/thumb_' . $filename;
                            $thumbStream = fopen($tempThumbPath, 'r');
                            Storage::disk('public')->put($thumbPath, $thumbStream);
                            fclose($thumbStream);
                            
                            // Clean up temp file
                            unlink($tempThumbPath);
                            
                            // Add new photo to existing photos
                            $activities[$activityIndex]['photos'][] = [
                                'path' => $originalPath,
                                'thumb_path' => $thumbPath,
                                'mime_type' => $photo->getMimeType()
                            ];
                        }
                    }
                }
            }

            // Handle new receipts
            if (isset($request->file('itinerary_days')[$index]['receipts'])) {
                foreach ($request->file('itinerary_days')[$index]['receipts'] as $receipt) {
                    $path = $receipt->store('receipts/' . $day->id, 'public');
                    
                    if (!$path) {
                        throw new \Exception('Failed to store receipt');
                    }
                    
                    $receipts[] = [
                        'path' => $path,
                        'name' => $receipt->getClientOriginalName(),
                        'mime_type' => $receipt->getMimeType()
                    ];
                }
            }

            // Update the day with all the collected data
            $day->update([
                'accommodation' => $dayData['accommodation'] ?? null,
                'accommodation_address' => $dayData['accommodation_address'] ?? null,
                'accommodation_latitude' => $accommodationCoords['latitude'] ?? null,
                'accommodation_longitude' => $accommodationCoords['longitude'] ?? null,
                'meals' => $meals,
                'activities' => $activities,
                'receipts' => $receipts,
                'notes' => $dayData['notes'] ?? null,
            ]);

            // Dispatch geocoding job for this day
            dispatch(new GeocodeDayJob($day->id));
        }

        // Delete any days that are no longer in the request
        $requestedDayNumbers = collect($request->input('itinerary_days'))->keys()->map(function($index) {
            return $index + 1;
        });
        $itinerary->days()->whereNotIn('day_number', $requestedDayNumbers)->delete();

        return redirect()->route('itineraries.show', $itinerary)
            ->with('success', 'Day-by-day itinerary updated successfully.');
    }

    // Add method to delete photos
    public function deleteDayPhoto(Request $request, Day $day, $photoPath)
    {
        $this->authorize('update', $day->itinerary);
        
        // Delete the file and its thumbnail from storage
        Storage::disk('public')->delete($photoPath);
        
        // If it's an image, also delete the thumbnail
        if (strpos($photoPath, 'thumb_') === false) {
            $pathInfo = pathinfo($photoPath);
            $thumbPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
            Storage::disk('public')->delete($thumbPath);
        }
        
        // Update the day's data to remove the photo
        $data = $day->toArray();
        
        // Check meals
        if (isset($data['meals'])) {
            foreach ($data['meals'] as $mealType => $meal) {
                if (isset($meal['photos'])) {
                    $data['meals'][$mealType]['photos'] = array_filter($meal['photos'], function($photo) use ($photoPath) {
                        return $photo['path'] !== $photoPath;
                    });
                }
            }
        }
        
        // Check activities
        if (isset($data['activities'])) {
            foreach ($data['activities'] as $index => $activity) {
                if (isset($activity['photos'])) {
                    $data['activities'][$index]['photos'] = array_filter($activity['photos'], function($photo) use ($photoPath) {
                        return $photo['path'] !== $photoPath;
                    });
                }
            }
        }
        
        // Check receipts
        if (isset($data['receipts'])) {
            $data['receipts'] = array_filter($data['receipts'], function($receipt) use ($photoPath) {
                return $receipt['path'] !== $photoPath;
            });
        }
        
        $day->update($data);
        
        return response()->json(['success' => true]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Itinerary $itinerary)
    {
        $this->authorize('update', $itinerary);

        DB::beginTransaction();

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            try {
                // Save original image using Laravel's store method
                $originalPath = $file->store('covers', 'public');
                
                if (!$originalPath) {
                    throw new \Exception('Failed to store the original image');
                }

                // Create thumbnail using Intervention Image
                $image = $this->imageManager->read($file);
                $thumbPath = 'covers/thumbnails/' . $filename;
                
                // Save thumbnail to temporary file first, then upload
                $tempThumbPath = storage_path('app/temp/' . $filename);
                
                // Ensure temp directory exists
                if (!file_exists(dirname($tempThumbPath))) {
                    mkdir(dirname($tempThumbPath), 0755, true);
                }
                
                $image->cover(800, 400)->save($tempThumbPath);
                
                // Upload thumbnail to cloud storage
                $thumbStream = fopen($tempThumbPath, 'r');
                Storage::disk('public')->put($thumbPath, $thumbStream);
                fclose($thumbStream);
                
                // Clean up temp file
                unlink($tempThumbPath);

                $itinerary->cover_image = $originalPath;
                $itinerary->save();
            } catch (\Exception $e) {
                throw new \Exception('Failed to save cover image: ' . $e->getMessage());
            }
        }

        // Handle gallery uploads
        if ($request->hasFile('gallery')) {
            $request->validate([
                'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ], [
                'gallery.*.image' => 'Each gallery file must be an image.',
                'gallery.*.mimes' => 'Each gallery image must be a JPEG, PNG, JPG, or GIF.',
                'gallery.*.max' => 'Each gallery image must not exceed 2MB.'
            ]);

            $gallery = $itinerary->gallery ?? [];
            foreach ($request->file('gallery') as $image) {
                try {
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $path = $image->store('gallery', 'public');
                    
                    if (!$path) {
                        throw new \Exception('Failed to store image: ' . $image->getClientOriginalName());
                    }

                    $gallery[] = $path;
                } catch (\Exception $e) {
                    throw new \Exception('Failed to upload gallery image: ' . $e->getMessage());
                }
            }
            $itinerary->update(['gallery' => $gallery]);
        }

        // Update other fields
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'highlights' => 'array',
            'highlights.*' => 'string',
            'included_items' => 'array',
            'included_items.*' => 'string',
            'excluded_items' => 'array',
            'excluded_items.*' => 'string',
            'requirements' => 'array',
            'requirements.*' => 'string',
            'duration_days' => 'nullable|integer|min:1',
            'transportation_type' => 'nullable|in:flight,road,both',
            'flight_duration' => 'nullable|string',
            'airfare_min' => 'nullable|numeric|min:0',
            'airfare_max' => 'nullable|numeric|min:0',
            'booking_website' => 'nullable|url',
            'road_distance' => 'nullable|string',
            'road_duration' => 'nullable|string',
            'road_type' => 'nullable|in:highway,local,mixed',
            'languages' => 'nullable|string',
            'peak_travel_times' => 'nullable|string',
            'travel_agency' => 'nullable|string',
            'agency_fees' => 'nullable|numeric|min:0',
            'travel_notes' => 'nullable|string',
            'is_published' => 'boolean'
        ], [
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'description.required' => 'The description field is required.',
            'location.required' => 'The location field is required.',
            'country.required' => 'The country field is required.',
            'price.required' => 'The price field is required.',
            'price.numeric' => 'The price must be a number.',
            'categories.required' => 'Please select at least one category.',
            'categories.*.exists' => 'One or more selected categories are invalid.',
            'highlights.array' => 'Highlights must be an array.',
            'included_items.array' => 'Included items must be an array.',
            'excluded_items.array' => 'Excluded items must be an array.',
            'requirements.array' => 'Requirements must be an array.',
            'transportation_type.in' => 'Transportation type must be flight, road, or both.',
            'airfare_min.numeric' => 'Airfare min must be a number.',
            'airfare_max.numeric' => 'Airfare max must be a number.',
            'booking_website.url' => 'Booking website must be a valid URL.',
            'road_type.in' => 'Road type must be highway, local, or mixed.',
            'agency_fees.numeric' => 'Agency fees must be a number.'
        ]);

        $itinerary->update($validated);

        // Sync categories
        $itinerary->categories()->sync($request->categories);

        DB::commit();

        // Dispatch geocoding job
        dispatch(new GeocodeItineraryJob($itinerary->id));

        return redirect()->route('itineraries.show', $itinerary)
            ->with('success', 'Itinerary updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Itinerary $itinerary)
    {
        $this->authorize('delete', $itinerary);
        $itinerary->delete();

        return redirect()->route('itineraries.index')
            ->with('success', 'Itinerary deleted successfully.');
    }

    /**
     * Publish the specified itinerary.
     */
    public function publish(Itinerary $itinerary)
    {
        $this->authorize('update', $itinerary);
        $itinerary->update(['is_published' => true]);

        return redirect()->route('itineraries.show', $itinerary)
            ->with('success', 'Itinerary published successfully.');
    }

    /**
     * Unpublish the specified itinerary.
     */
    public function unpublish(Itinerary $itinerary)
    {
        $this->authorize('update', $itinerary);
        $itinerary->update(['is_published' => false]);

        return redirect()->route('itineraries.show', $itinerary)
            ->with('success', 'Itinerary unpublished successfully.');
    }

    /**
     * Display the day-by-day itinerary view.
     */
    public function showDays(Itinerary $itinerary)
    {
        $this->authorize('view', $itinerary);
        
        // Load days with proper ordering
        $itinerary->load(['days' => function($query) {
            $query->orderBy('day_number');
        }]);
        
        // Get the days collection
        $days = $itinerary->days;
        
        return view('itineraries.day-show', compact('itinerary', 'days'));
    }

    /**
     * Display the user's itineraries.
     */
    public function myItineraries()
    {
        try {
            $itineraries = Itinerary::with(['user', 'categories', 'days'])
                ->where('user_id', auth()->id())
                ->latest()
                ->paginate(12);

            return view('itineraries.my', compact('itineraries'));
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while loading your itineraries.');
        }
    }

    /**
     * Remove a gallery image from the itinerary.
     */
    public function deleteGalleryImage(Request $request, Itinerary $itinerary)
    {
        try {
            // Ensure the user is the owner
            if (auth()->id() !== $itinerary->user_id) {
                return response()->json([
                    'success' => false, 
                    'message' => 'You are not authorized to delete this image.'
                ], 403);
            }

            $image = $request->query('image');
            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image specified for deletion.'
                ], 400);
            }

            $gallery = $itinerary->gallery ?? [];
            $key = array_search($image, $gallery);
            
            if ($key === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found in gallery.'
                ], 404);
            }

            // Remove from array
            unset($gallery[$key]);
            $gallery = array_values($gallery);

            // Delete file from storage
            if (\Storage::disk('public')->exists($image)) {
                try {
                    \Storage::disk('public')->delete($image);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to delete image file from storage.'
                    ], 500);
                }
            }

            // Update itinerary
            try {
                $itinerary->gallery = $gallery;
                $itinerary->save();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update gallery in database.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gallery image deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while deleting the image.'
            ], 500);
        }
    }

    /**
     * Copy an itinerary for the authenticated user
     */
    public function copy(Itinerary $itinerary)
    {
        // Check if user has purchased the itinerary
        if (!auth()->user()->hasPurchased($itinerary)) {
            return redirect()->back()->with('error', 'You must purchase this itinerary before copying it.');
        }

        // Create a new itinerary based on the purchased one
        $newItinerary = $itinerary->replicate();
        $newItinerary->user_id = auth()->id();
        $newItinerary->title = 'My Copy of ' . $itinerary->title;
        $newItinerary->slug = Str::slug($newItinerary->title) . '-' . time();
        $newItinerary->is_published = false;
        $newItinerary->is_featured = false;
        $newItinerary->views_count = 0;
        $newItinerary->purchases_count = 0;
        $newItinerary->save();

        // Copy the days
        foreach ($itinerary->days as $day) {
            $newDay = $day->replicate();
            $newDay->itinerary_id = $newItinerary->id;
            $newDay->save();
        }

        // Copy the categories
        $newItinerary->categories()->attach($itinerary->categories->pluck('id'));

        return redirect()->route('itineraries.edit', $newItinerary)
            ->with('success', 'Itinerary copied successfully! You can now edit your version.');
    }

    public function uploadCoverImage(Request $request, Itinerary $itinerary)
    {
        $this->authorize('update', $itinerary);

        $request->validate([
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Delete old cover image if exists
        if ($itinerary->cover_image) {
            Storage::disk('public')->delete($itinerary->cover_image);
            Storage::disk('public')->delete(str_replace('covers/', 'covers/thumbnails/', $itinerary->cover_image));
        }

        $file = $request->file('cover_image');
        $filename = time() . '_' . $file->getClientOriginalName();
        
        try {
            // Save original image using Laravel's store method
            $originalPath = $file->store('covers', 'public');
            
            if (!$originalPath) {
                throw new \Exception('Failed to store the original image');
            }

            // Create thumbnail using Intervention Image
            $image = $this->imageManager->read($file);
            $thumbPath = 'covers/thumbnails/' . $filename;
            
            // Save thumbnail to temporary file first, then upload
            $tempThumbPath = storage_path('app/temp/' . $filename);
            
            // Ensure temp directory exists
            if (!file_exists(dirname($tempThumbPath))) {
                mkdir(dirname($tempThumbPath), 0755, true);
            }
            
            $image->cover(800, 400)->save($tempThumbPath);
            
            // Upload thumbnail to cloud storage
            $thumbStream = fopen($tempThumbPath, 'r');
            Storage::disk('public')->put($thumbPath, $thumbStream);
            fclose($thumbStream);
            
            // Clean up temp file
            unlink($tempThumbPath);

            $itinerary->cover_image = $originalPath;
            $itinerary->save();

            return response()->json([
                'success' => true,
                'cover_image_url' => Storage::url($originalPath),
                'message' => 'Cover image updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload cover image: ' . $e->getMessage()
            ], 500);
        }
    }
}
