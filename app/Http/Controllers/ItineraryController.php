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
            \Log::info('Starting itineraries index method');
            
            $query = Itinerary::with(['user', 'categories', 'days'])
                ->where('is_published', true);

            \Log::info('Base query built', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

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

            \Log::info('Filters applied to query');

            $itineraries = $query->latest()->paginate(12);
            
            \Log::info('Itineraries retrieved', [
                'count' => $itineraries->count(),
                'total' => $itineraries->total()
            ]);

            $categories = Category::where('is_active', true)->get();
            
            \Log::info('Categories retrieved', [
                'count' => $categories->count()
            ]);

            return view('itineraries.index', compact('itineraries', 'categories'));
        } catch (\Exception $e) {
            \Log::error('Error in itineraries index: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request' => request()->all(),
                'user' => auth()->id()
            ]);
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
        \Log::info('Starting itinerary creation', [
            'has_file' => $request->hasFile('cover_image'),
            'all_data' => $request->all(),
            'user_id' => auth()->id(),
            'has_csrf' => $request->hasHeader('X-CSRF-TOKEN'),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'files' => $request->allFiles(),
            'headers' => $request->headers->all()
        ]);

        try {
            \Log::info('Starting validation');
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

            \Log::info('Validation passed', [
                'validated_data' => $validated,
                'has_cover_image' => isset($validated['cover_image']),
                'has_gallery' => isset($validated['gallery']),
                'has_categories' => isset($validated['categories']),
            ]);

            \Log::info('Starting database transaction');
            DB::beginTransaction();

            $validated['user_id'] = auth()->id();
            $validated['slug'] = Str::slug($validated['title']) . '-' . time();
            $validated['is_published'] = $request->has('is_published') ? true : false;
            $validated['is_featured'] = false;

            \Log::info('Prepared data for creation', [
                'data' => $validated,
                'user_id' => $validated['user_id'],
                'slug' => $validated['slug']
            ]);

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                \Log::info('Processing cover image upload', [
                    'original_name' => $request->file('cover_image')->getClientOriginalName(),
                    'mime_type' => $request->file('cover_image')->getMimeType(),
                    'size' => $request->file('cover_image')->getSize(),
                    'is_valid' => $request->file('cover_image')->isValid()
                ]);

                $file = $request->file('cover_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                try {
                    \Log::info('Creating image manager instance');
                    $image = $this->imageManager->read($file);
                    
                    // Save original image
                    $originalPath = 'covers/' . $filename;
                    $originalFullPath = storage_path('app/public/' . $originalPath);
                    
                    \Log::info('Preparing to save original image', [
                        'path' => $originalFullPath,
                        'directory_exists' => file_exists(dirname($originalFullPath))
                    ]);
                    
                    // Ensure directory exists
                    if (!file_exists(dirname($originalFullPath))) {
                        \Log::info('Creating directory for original image');
                        mkdir(dirname($originalFullPath), 0755, true);
                    }
                    
                    \Log::info('Saving original image');
                    $image->save($originalFullPath);
                    
                    // Create and save thumbnail
                    $thumbPath = 'covers/thumbnails/' . $filename;
                    $thumbFullPath = storage_path('app/public/' . $thumbPath);
                    
                    \Log::info('Preparing to save thumbnail', [
                        'path' => $thumbFullPath,
                        'directory_exists' => file_exists(dirname($thumbFullPath))
                    ]);
                    
                    // Ensure thumbnail directory exists
                    if (!file_exists(dirname($thumbFullPath))) {
                        \Log::info('Creating directory for thumbnail');
                        mkdir(dirname($thumbFullPath), 0755, true);
                    }
                    
                    \Log::info('Saving thumbnail');
                    $image->cover(800, 400)->save($thumbFullPath);

                    // Update the cover_image field in the database
                    $validated['cover_image'] = $originalPath;

                    \Log::info('Cover image saved successfully', [
                        'original_path' => $originalPath,
                        'thumb_path' => $thumbPath,
                        'db_path' => $validated['cover_image'],
                        'file_exists' => file_exists($originalFullPath)
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error processing image', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'file_info' => [
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'mime' => $file->getMimeType()
                        ]
                    ]);
                    throw $e;
                }
            }

            \Log::info('Creating itinerary record', ['data' => $validated]);
            $itinerary = Itinerary::create($validated);
            \Log::info('Itinerary created', ['itinerary_id' => $itinerary->id]);

            \Log::info('Attaching categories', ['categories' => $request->input('categories')]);
            $itinerary->categories()->attach($request->input('categories'));
            \Log::info('Categories attached successfully');

            // Handle gallery uploads
            if ($request->hasFile('gallery')) {
                \Log::info('Processing gallery uploads', ['count' => count($request->file('gallery'))]);
                
                // Validate each gallery image
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
                        \Log::info('Processing gallery image', [
                            'name' => $image->getClientOriginalName(),
                            'size' => $image->getSize(),
                            'mime' => $image->getMimeType()
                        ]);

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
                        \Log::info('Gallery image saved', ['path' => $path]);
                    } catch (\Exception $e) {
                        \Log::error('Error processing gallery image', [
                            'error' => $e->getMessage(),
                            'file' => $image->getClientOriginalName()
                        ]);
                        throw new \Exception('Failed to upload gallery image: ' . $e->getMessage());
                    }
                }
                \Log::info('Updating itinerary with gallery', ['gallery' => $gallery]);
                $itinerary->update(['gallery' => $gallery]);
            }

            \Log::info('Committing transaction');
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

            \Log::info('Itinerary created successfully', [
                'itinerary_id' => $itinerary->id,
                'cover_image' => $itinerary->cover_image,
                'has_gallery' => !empty($itinerary->gallery)
            ]);

            // Dispatch geocoding job
            dispatch(new GeocodeItineraryJob($itinerary->id));

            return redirect()->route('itineraries.show', $itinerary)
                ->with('success', 'Itinerary created successfully.');
        } catch (\Exception $e) {
            \Log::error('Itinerary creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'validation_errors' => $request->validator ? $request->validator->errors()->toArray() : null,
                'file_uploads' => [
                    'has_cover' => $request->hasFile('cover_image'),
                    'has_gallery' => $request->hasFile('gallery'),
                    'cover_valid' => $request->hasFile('cover_image') ? $request->file('cover_image')->isValid() : false
                ]
            ]);
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create itinerary: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Itinerary $itinerary)
    {
        try {
            \Log::info('Starting itinerary show method', [
                'itinerary_id' => $itinerary->id,
                'slug' => $itinerary->slug,
                'title' => $itinerary->title
            ]);

            $this->authorize('view', $itinerary);
            
            \Log::info('Authorization passed');
            
            // Load relationships with error handling
            try {
                $itinerary->load(['user', 'categories', 'days']);
                \Log::info('Relationships loaded successfully', [
                    'has_user' => $itinerary->user ? true : false,
                    'categories_count' => $itinerary->categories->count(),
                    'days_count' => $itinerary->days->count()
                ]);
            } catch (\Exception $e) {
                \Log::error('Error loading relationships: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue without the relationships rather than failing completely
            }

            // Verify critical data
            if (!$itinerary->user) {
                \Log::warning('Itinerary has no associated user', [
                    'itinerary_id' => $itinerary->id
                ]);
            }

            return view('itineraries.show', compact('itinerary'));
        } catch (\Exception $e) {
            \Log::error('Error in itinerary show: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'itinerary_id' => $itinerary->id ?? null,
                'slug' => $itinerary->slug ?? null,
                'request_url' => request()->url(),
                'request_method' => request()->method()
            ]);
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

        try {
            \Log::info('Starting day update with files', [
                'has_files' => $request->hasFile('itinerary_days'),
                'all_data' => $request->all()
            ]);

            // First validate that we have at least one day
            $request->validate([
                'itinerary_days' => 'required|array|min:1',
            ]);

            // Then validate each day's data
            foreach ($request->input('itinerary_days') as $index => $dayData) {
                $rules = [
                    'itinerary_days.' . $index . '.accommodation' => $index === 0 ? 'required|string|max:255' : 'nullable|string|max:255',
                    'itinerary_days.' . $index . '.accommodation_address' => 'nullable|string|max:255',
                    'itinerary_days.' . $index . '.meals' => 'nullable|array',
                    'itinerary_days.' . $index . '.meals.*.name' => 'nullable|string|max:255',
                    'itinerary_days.' . $index . '.meals.*.address' => 'nullable|string|max:255',
                    'itinerary_days.' . $index . '.meals.*.description' => 'nullable|string',
                    'itinerary_days.' . $index . '.activities' => 'nullable|array',
                    'itinerary_days.' . $index . '.activities.*.name' => 'nullable|string|max:255',
                    'itinerary_days.' . $index . '.activities.*.address' => 'nullable|string|max:255',
                    'itinerary_days.' . $index . '.activities.*.description' => 'nullable|string',
                    'itinerary_days.' . $index . '.activities.*.entry_fee' => 'nullable|numeric|min:0',
                    'itinerary_days.' . $index . '.notes' => 'nullable|string',
                ];

                // Add photo validation rules
                if ($request->hasFile('itinerary_days.' . $index . '.meals.*.photos.*')) {
                    $rules['itinerary_days.' . $index . '.meals.*.photos.*'] = 'nullable|image|max:2048';
                }
                if ($request->hasFile('itinerary_days.' . $index . '.activities.*.photos.*')) {
                    $rules['itinerary_days.' . $index . '.activities.*.photos.*'] = 'nullable|image|max:2048';
                }
                if ($request->hasFile('itinerary_days.' . $index . '.receipts.*')) {
                    $rules['itinerary_days.' . $index . '.receipts.*'] = 'nullable|file|mimes:jpeg,png,pdf|max:2048';
                }

                $request->validate($rules);
            }

            // Get existing days
            $existingDays = $itinerary->days()->get()->keyBy('day_number');

            // Update or create days
            foreach ($request->input('itinerary_days') as $index => $dayData) {
                \Log::info('Processing day', ['day_number' => $index + 1]);

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
                                // Create directories if they don't exist
                                $mealPath = storage_path('app/public/meals/' . $day->id . '/' . $mealType);
                                if (!file_exists($mealPath)) {
                                    mkdir($mealPath, 0755, true);
                                }

                                // Create image instance
                                $image = $this->imageManager->read($photo);
                                
                                // Generate unique filename
                                $filename = uniqid() . '.' . $photo->getClientOriginalExtension();
                                $path = 'meals/' . $day->id . '/' . $mealType . '/' . $filename;
                                
                                // Save original image
                                $image->save(storage_path('app/public/' . $path));
                                
                                // Create and save thumbnail
                                $thumbPath = 'meals/' . $day->id . '/' . $mealType . '/thumb_' . $filename;
                                $image->cover(300, 300)->save(storage_path('app/public/' . $thumbPath));
                                
                                // Add new photo to existing photos
                                $meals[$mealType]['photos'][] = [
                                    'path' => $path,
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
                                // Create directories if they don't exist
                                $activityPath = storage_path('app/public/activities/' . $day->id . '/' . $activityIndex);
                                if (!file_exists($activityPath)) {
                                    mkdir($activityPath, 0755, true);
                                }

                                // Create image instance
                                $image = $this->imageManager->read($photo);
                                
                                // Generate unique filename
                                $filename = uniqid() . '.' . $photo->getClientOriginalExtension();
                                $path = 'activities/' . $day->id . '/' . $activityIndex . '/' . $filename;
                                
                                // Save original image
                                $image->save(storage_path('app/public/' . $path));
                                
                                // Create and save thumbnail
                                $thumbPath = 'activities/' . $day->id . '/' . $activityIndex . '/thumb_' . $filename;
                                $image->cover(300, 300)->save(storage_path('app/public/' . $thumbPath));
                                
                                // Add new photo to existing photos
                                $activities[$activityIndex]['photos'][] = [
                                    'path' => $path,
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
                        // Create directory if it doesn't exist
                        $receiptPath = storage_path('app/public/receipts/' . $day->id);
                        if (!file_exists($receiptPath)) {
                            mkdir($receiptPath, 0755, true);
                        }

                        $path = $receipt->store('receipts/' . $day->id, 'public');
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
        } catch (\Exception $e) {
            \Log::error('Error updating days', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to update itinerary: ' . $e->getMessage());
        }
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

        try {
            DB::beginTransaction();

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                \Log::info('Processing cover image upload', [
                    'original_name' => $request->file('cover_image')->getClientOriginalName(),
                    'mime_type' => $request->file('cover_image')->getMimeType(),
                    'size' => $request->file('cover_image')->getSize(),
                    'is_valid' => $request->file('cover_image')->isValid()
                ]);

                // Validate the image
                $request->validate([
                    'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
                ], [
                    'cover_image.required' => 'A cover image is required.',
                    'cover_image.image' => 'The cover image must be an image file.',
                    'cover_image.mimes' => 'The cover image must be a JPEG, PNG, JPG, or GIF.',
                    'cover_image.max' => 'The cover image must not exceed 2MB.'
                ]);

                // Delete old cover image if exists
                if ($itinerary->cover_image) {
                    \Log::info('Deleting old cover image', [
                        'old_path' => $itinerary->cover_image
                    ]);
                    try {
                        Storage::disk('public')->delete($itinerary->cover_image);
                        Storage::disk('public')->delete(str_replace('covers/', 'covers/thumbnails/', $itinerary->cover_image));
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete old cover image', [
                            'error' => $e->getMessage(),
                            'path' => $itinerary->cover_image
                        ]);
                    }
                }

                try {
                    // Save new cover image
                    $path = $request->file('cover_image')->store('covers', 'public');
                    
                    if (!$path) {
                        throw new \Exception('Failed to store the image file');
                    }

                    // Verify the file exists
                    if (!Storage::disk('public')->exists($path)) {
                        throw new \Exception('File was not saved correctly');
                    }

                    $itinerary->cover_image = $path;
                    $itinerary->save();

                    \Log::info('Cover image saved successfully', [
                        'new_path' => $path,
                        'file_exists' => Storage::disk('public')->exists($path)
                    ]);

                    // If this is an AJAX request, return JSON response
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'cover_image_url' => Storage::url($path),
                            'message' => 'Cover image updated successfully'
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error saving cover image', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw new \Exception('Failed to save cover image: ' . $e->getMessage());
                }
            }

            // Handle gallery uploads
            if ($request->hasFile('gallery')) {
                \Log::info('Processing gallery uploads', ['count' => count($request->file('gallery'))]);
                
                // Validate each gallery image
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
                        \Log::info('Processing gallery image', [
                            'name' => $image->getClientOriginalName(),
                            'size' => $image->getSize(),
                            'mime' => $image->getMimeType()
                        ]);

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
                        \Log::info('Gallery image saved', ['path' => $path]);
                    } catch (\Exception $e) {
                        \Log::error('Error processing gallery image', [
                            'error' => $e->getMessage(),
                            'file' => $image->getClientOriginalName()
                        ]);
                        throw new \Exception('Failed to upload gallery image: ' . $e->getMessage());
                    }
                }
                \Log::info('Updating itinerary with gallery', ['gallery' => $gallery]);
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

            \Log::info('Itinerary updated successfully', [
                'itinerary_id' => $itinerary->id
            ]);

            // Dispatch geocoding job
            dispatch(new GeocodeItineraryJob($itinerary->id));

            // If this is an AJAX request, return JSON response
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Itinerary updated successfully'
                ]);
            }

            return redirect()->route('itineraries.show', $itinerary)
                ->with('success', 'Itinerary updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating itinerary', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'itinerary_id' => $itinerary->id
            ]);

            // Show the real error for debugging
            return back()->withInput()
                ->with('error', 'Error updating itinerary: ' . $e->getMessage());
        }
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
            \Log::error('Error in my itineraries: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while loading your itineraries.');
        }
    }

    /**
     * Remove a gallery image from the itinerary.
     */
    public function deleteGalleryImage(Request $request, Itinerary $itinerary)
    {
        try {
            \Log::info('deleteGalleryImage called', [
                'itinerary_id' => $itinerary->id,
                'user_id' => auth()->id(),
                'gallery' => $itinerary->gallery,
                'query_image' => $request->query('image')
            ]);

            // Ensure the user is the owner
            if (auth()->id() !== $itinerary->user_id) {
                \Log::warning('Unauthorized gallery image delete attempt', [
                    'user_id' => auth()->id(),
                    'itinerary_id' => $itinerary->id
                ]);
                return response()->json([
                    'success' => false, 
                    'message' => 'You are not authorized to delete this image.'
                ], 403);
            }

            $image = $request->query('image');
            if (!$image) {
                \Log::warning('No image specified for deletion');
                return response()->json([
                    'success' => false,
                    'message' => 'No image specified for deletion.'
                ], 400);
            }

            $gallery = $itinerary->gallery ?? [];
            $key = array_search($image, $gallery);
            
            if ($key === false) {
                \Log::warning('Image not found in gallery', [
                    'image' => $image,
                    'gallery' => $gallery
                ]);
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
                    \Log::info('Deleted gallery image from storage', ['image' => $image]);
                } catch (\Exception $e) {
                    \Log::error('Error deleting gallery image from storage', [
                        'error' => $e->getMessage(),
                        'image' => $image
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to delete image file from storage.'
                    ], 500);
                }
            } else {
                \Log::warning('Gallery image file not found in storage', ['image' => $image]);
            }

            // Update itinerary
            try {
                $itinerary->gallery = $gallery;
                $itinerary->save();
                \Log::info('Gallery updated after deletion', ['gallery' => $gallery]);
            } catch (\Exception $e) {
                \Log::error('Error updating gallery in database', [
                    'error' => $e->getMessage(),
                    'gallery' => $gallery
                ]);
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
            \Log::error('Unexpected error in deleteGalleryImage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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

        $path = $request->file('cover_image')->store('covers', 'public');
        $itinerary->cover_image = $path;
        $itinerary->save();

        return response()->json([
            'success' => true,
            'cover_image_url' => Storage::url($path),
            'message' => 'Cover image updated successfully'
        ]);
    }
}
