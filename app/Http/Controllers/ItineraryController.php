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

class ItineraryController extends Controller
{
    protected $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
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
                $gallery = [];
                foreach ($request->file('gallery') as $image) {
                    \Log::info('Processing gallery image', [
                        'name' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'mime' => $image->getMimeType()
                    ]);
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $path = $image->store('gallery', 'public');
                    $gallery[] = $path;
                    \Log::info('Gallery image saved', ['path' => $path]);
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
                'slug' => $itinerary->slug
            ]);

            $this->authorize('view', $itinerary);
            
            \Log::info('Authorization passed');
            
            $itinerary->load(['user', 'categories', 'days']);
            
            \Log::info('Relationships loaded', [
                'has_user' => $itinerary->user ? true : false,
                'categories_count' => $itinerary->categories->count(),
                'days_count' => $itinerary->days->count()
            ]);

            return view('itineraries.show', compact('itinerary'));
        } catch (\Exception $e) {
            \Log::error('Error in itinerary show: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'itinerary_id' => $itinerary->id ?? null,
                'slug' => $itinerary->slug ?? null
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

                // Update meal data
                if (isset($dayData['meals'])) {
                    foreach ($dayData['meals'] as $mealType => $mealData) {
                        // Preserve existing meal data
                        $existingMeal = $meals[$mealType] ?? [];
                        
                        // Update meal info
                        $meals[$mealType] = array_merge($existingMeal, [
                            'name' => $mealData['name'] ?? $existingMeal['name'] ?? null,
                            'address' => $mealData['address'] ?? $existingMeal['address'] ?? null,
                            'description' => $mealData['description'] ?? $existingMeal['description'] ?? null,
                            'photos' => $existingMeal['photos'] ?? []
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
                        
                        // Create new activity data
                        $activities[$activityIndex] = [
                            'name' => $activityData['name'] ?? $existingActivity['name'] ?? null,
                            'address' => $activityData['address'] ?? $existingActivity['address'] ?? null,
                            'description' => $activityData['description'] ?? $existingActivity['description'] ?? null,
                            'entry_fee' => $activityData['entry_fee'] ?? $existingActivity['entry_fee'] ?? null,
                            'photos' => $existingActivity['photos'] ?? []
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
                    'meals' => $meals,
                    'activities' => $activities,
                    'receipts' => $receipts,
                    'notes' => $dayData['notes'] ?? null,
                ]);
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
        \Log::info('Starting itinerary update', [
            'has_file' => $request->hasFile('cover_image'),
            'all_data' => $request->all()
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'location' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'highlights' => 'required|array|min:1',
            'highlights.*' => 'required|string|max:255',
            'included_items' => 'required|array|min:1',
            'included_items.*' => 'required|string|max:255',
            'excluded_items' => 'required|array|min:1',
            'excluded_items.*' => 'required|string|max:255',
            'requirements' => 'required|array|min:1',
            'requirements.*' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                \Log::info('Processing cover image upload', [
                    'original_name' => $request->file('cover_image')->getClientOriginalName(),
                    'mime_type' => $request->file('cover_image')->getMimeType(),
                    'size' => $request->file('cover_image')->getSize()
                ]);

                // Delete old cover image if exists
                if ($itinerary->cover_image) {
                    \Log::info('Deleting old cover image', [
                        'old_path' => $itinerary->cover_image
                    ]);
                    Storage::disk('public')->delete($itinerary->cover_image);
                    Storage::disk('public')->delete(str_replace('covers/', 'covers/thumbnails/', $itinerary->cover_image));
                }

                $file = $request->file('cover_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                try {
                    // Create image instance using the image manager
                    $image = $this->imageManager->read($file);
                    
                    // Save original image
                    $originalPath = 'covers/' . $filename;
                    $originalFullPath = storage_path('app/public/' . $originalPath);
                    
                    \Log::info('Saving original image', [
                        'path' => $originalFullPath
                    ]);
                    
                    // Ensure directory exists
                    if (!file_exists(dirname($originalFullPath))) {
                        mkdir(dirname($originalFullPath), 0755, true);
                    }
                    
                    // Save the image
                    $image->save($originalFullPath);
                    
                    // Create and save thumbnail
                    $thumbPath = 'covers/thumbnails/' . $filename;
                    $thumbFullPath = storage_path('app/public/' . $thumbPath);
                    
                    \Log::info('Saving thumbnail', [
                        'path' => $thumbFullPath
                    ]);
                    
                    // Ensure thumbnail directory exists
                    if (!file_exists(dirname($thumbFullPath))) {
                        mkdir(dirname($thumbFullPath), 0755, true);
                    }
                    
                    // Create and save the thumbnail
                    $image->cover(800, 400)->save($thumbFullPath);

                    // Update the cover_image field in the database
                    $validated['cover_image'] = $originalPath;

                    \Log::info('Cover image saved successfully', [
                        'original_path' => $originalPath,
                        'thumb_path' => $thumbPath,
                        'db_path' => $validated['cover_image']
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error processing image', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            // Handle gallery uploads
            if ($request->hasFile('gallery')) {
                \Log::info('Processing gallery uploads in update', ['count' => count($request->file('gallery'))]);
                $gallery = $itinerary->gallery ?? [];
                foreach ($request->file('gallery') as $image) {
                    \Log::info('Processing gallery image', [
                        'name' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'mime' => $image->getMimeType()
                    ]);
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $path = $image->store('gallery', 'public');
                    $gallery[] = $path;
                    \Log::info('Gallery image saved', ['path' => $path]);
                }
                \Log::info('Updating itinerary with gallery', ['gallery' => $gallery]);
                $itinerary->update(['gallery' => $gallery]);
            }

            // Update the itinerary
            $itinerary->update($validated);

            DB::commit();

            \Log::info('Itinerary updated successfully', [
                'itinerary_id' => $itinerary->id,
                'cover_image' => $itinerary->cover_image
            ]);

            return redirect()->route('itineraries.show', $itinerary)
                ->with('success', 'Itinerary updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Itinerary update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to update itinerary: ' . $e->getMessage());
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
        // Ensure the user is the owner
        if (auth()->id() !== $itinerary->user_id) {
            return redirect()->back()->with('error', 'You are not authorized to delete this image.');
        }

        $image = $request->query('image');
        $gallery = $itinerary->gallery ?? [];
        $key = array_search($image, $gallery);
        if ($key === false) {
            return redirect()->back()->with('error', 'Image not found in gallery.');
        }

        // Remove from array
        unset($gallery[$key]);
        $gallery = array_values($gallery);

        // Delete file from storage
        if (\Storage::disk('public')->exists($image)) {
            \Storage::disk('public')->delete($image);
        }

        // Update itinerary
        $itinerary->gallery = $gallery;
        $itinerary->save();

        return redirect()->back()->with('success', 'Gallery image deleted successfully.');
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
}
