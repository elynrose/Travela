<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Itinerary;
use App\Services\GeocodingService;

class GeocodeItineraryJob implements ShouldQueue
{
    use Queueable;

    protected $itineraryId;

    /**
     * Create a new job instance.
     */
    public function __construct($itineraryId)
    {
        $this->itineraryId = $itineraryId;
    }

    /**
     * Execute the job.
     */
    public function handle(GeocodingService $geocodingService): void
    {
        $itinerary = Itinerary::find($this->itineraryId);
        if (!$itinerary) {
            \Log::warning('GeocodeItineraryJob: Itinerary not found', ['id' => $this->itineraryId]);
            return;
        }
        $coords = $geocodingService->getCoordinates($itinerary->location, $itinerary->country);
        $itinerary->latitude = $coords['latitude'];
        $itinerary->longitude = $coords['longitude'];
        $itinerary->save();
        \Log::info('GeocodeItineraryJob: Coordinates updated', [
            'id' => $this->itineraryId,
            'coords' => $coords
        ]);
    }
}
