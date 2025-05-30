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
        $this->middleware('auth')->except(['index', 'show']);
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Itinerary::with(['user', 'categories'])
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
            'highlights' => 'required|array',
            'highlights.*' => 'required|string',
            'included_items' => 'required|array',
            'included_items.*' => 'required|string',
            'excluded_items' => 'required|array',
            'excluded_items.*' => 'required|string',
            'requirements' => 'required|array',
            'requirements.*' => 'required|string',
            'itinerary_days' => 'required|array',
            'itinerary_days.*.accommodation' => 'required|string',
            'itinerary_days.*.meals.breakfast' => 'nullable|string',
            'itinerary_days.*.meals.lunch' => 'nullable|string',
            'itinerary_days.*.meals.dinner' => 'nullable|string',
            'itinerary_days.*.activities' => 'required|array',
            'itinerary_days.*.activities.*' => 'required|string',
            'itinerary_days.*.notes' => 'nullable|string',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_published'] = false;
        $validated['is_featured'] = false;

        // Remove cover_image and gallery from $validated so they're not mass-assigned
        $coverImage = $request->file('cover_image');
        unset($validated['cover_image']);
        $galleryImages = $request->file('gallery');
        unset($validated['gallery']);

        $itinerary = Itinerary::create($validated);

        // Attach categories
        $itinerary->categories()->attach($request->input('categories'));

        // Handle cover image upload
        if ($coverImage) {
            try {
                $filename = time() . '_' . $coverImage->getClientOriginalName();
                
                \Log::info('Processing cover image upload in store', [
                    'original_name' => $coverImage->getClientOriginalName(),
                    'mime_type' => $coverImage->getMimeType(),
                    'size' => $coverImage->getSize()
                ]);
                
                // Create image instance using the image manager
                $image = $this->imageManager->read($coverImage);
                
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
                $itinerary->update(['cover_image' => $originalPath]);

                \Log::info('Cover image saved successfully', [
                    'original_path' => $originalPath,
                    'thumb_path' => $thumbPath,
                    'db_path' => $originalPath
                ]);
            } catch (\Exception $e) {
                \Log::error('Error processing image in store', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }

        // Handle gallery uploads
        if ($galleryImages) {
            foreach ($galleryImages as $image) {
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->store('gallery', 'public');
                $gallery[] = $path;
            }
            $itinerary->update(['gallery' => $gallery]);
        }

        return redirect()->route('itineraries.show', $itinerary)
            ->with('success', 'Itinerary created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Itinerary $itinerary)
    {
        $this->authorize('view', $itinerary);
        $itinerary->load(['user', 'categories']);
        return view('itineraries.show', compact('itinerary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Itinerary $itinerary)
    {
        $this->authorize('update', $itinerary);
        $categories = Category::where('is_active', true)->get();
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
}
