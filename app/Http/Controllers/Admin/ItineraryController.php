<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    public function index()
    {
        $itineraries = Itinerary::with(['user', 'categories'])
            ->withCount('orders')
            ->latest()
            ->paginate(10);

        $activeCount = Itinerary::where('is_published', true)->count();
        $draftCount = Itinerary::where('is_published', false)->count();
        $purchasedCount = Itinerary::whereHas('orders', function($q) {
            $q->where('payment_status', 'completed');
        })->count();
        $copiedCount = Itinerary::where('title', 'like', 'My Copy of%')->count();

        return view('admin.itineraries.index', compact('itineraries', 'activeCount', 'draftCount', 'purchasedCount', 'copiedCount'));
    }

    public function show(Itinerary $itinerary)
    {
        $itinerary->load(['user', 'categories', 'days', 'orders']);
        
        return view('admin.itineraries.show', compact('itinerary'));
    }

    public function toggleFeatured(Itinerary $itinerary)
    {
        $itinerary->update([
            'is_featured' => !$itinerary->is_featured
        ]);

        return back()->with('success', 'Itinerary featured status updated successfully.');
    }
} 