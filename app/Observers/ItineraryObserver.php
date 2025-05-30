<?php

namespace App\Observers;

use App\Models\Itinerary;
use Illuminate\Support\Facades\Storage;

class ItineraryObserver
{
    /**
     * Handle the Itinerary "created" event.
     */
    public function created(Itinerary $itinerary): void
    {
        // Generate slug if not provided
        if (!$itinerary->slug) {
            $itinerary->slug = \Str::slug($itinerary->title);
            $itinerary->saveQuietly();
        }
    }

    /**
     * Handle the Itinerary "updated" event.
     */
    public function updated(Itinerary $itinerary): void
    {
        // Handle cover image changes
        if ($itinerary->isDirty('cover_image') && $itinerary->getOriginal('cover_image')) {
            // Delete old cover image file
            Storage::disk('public')->delete($itinerary->getOriginal('cover_image'));
        }

        // Update slug if title changed
        if ($itinerary->isDirty('title')) {
            $itinerary->slug = \Str::slug($itinerary->title);
            $itinerary->saveQuietly();
        }
    }

    /**
     * Handle the Itinerary "deleted" event.
     */
    public function deleted(Itinerary $itinerary): void
    {
        // Delete cover image
        if ($itinerary->cover_image) {
            Storage::disk('public')->delete($itinerary->cover_image);
        }

        // Delete associated media files
        $itinerary->clearMediaCollection('gallery');
    }

    /**
     * Handle the Itinerary "restored" event.
     */
    public function restored(Itinerary $itinerary): void
    {
        // If needed, handle any logic when an itinerary is restored
    }

    /**
     * Handle the Itinerary "force deleted" event.
     */
    public function forceDeleted(Itinerary $itinerary): void
    {
        // Delete cover image
        if ($itinerary->cover_image) {
            Storage::disk('public')->delete($itinerary->cover_image);
        }

        // Delete associated media files
        $itinerary->clearMediaCollection('gallery');
    }
} 