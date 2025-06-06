<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Day;
use App\Services\GeocodingService;

class GeocodeDayJob implements ShouldQueue
{
    use Queueable;

    protected $dayId;

    /**
     * Create a new job instance.
     */
    public function __construct($dayId)
    {
        $this->dayId = $dayId;
    }

    /**
     * Execute the job.
     */
    public function handle(GeocodingService $geocodingService): void
    {
        $day = Day::find($this->dayId);
        if (!$day) {
            \Log::warning('GeocodeDayJob: Day not found', ['id' => $this->dayId]);
            return;
        }
        $itinerary = $day->itinerary;
        if (!$itinerary) {
            \Log::warning('GeocodeDayJob: Itinerary not found for day', ['day_id' => $this->dayId]);
            return;
        }
        // Accommodation
        if (!empty($day->accommodation_address)) {
            $coords = $geocodingService->getCoordinates($day->accommodation, $itinerary->country);
            $day->accommodation_latitude = $coords['latitude'];
            $day->accommodation_longitude = $coords['longitude'];
        }
        // Meals
        $meals = $day->meals ?? [];
        foreach ($meals as $type => $meal) {
            if (!empty($meal['address'])) {
                $coords = $geocodingService->getCoordinates($meal['name'] ?? '', $itinerary->country);
                $meals[$type]['latitude'] = $coords['latitude'];
                $meals[$type]['longitude'] = $coords['longitude'];
            }
        }
        $day->meals = $meals;
        // Activities
        $activities = $day->activities ?? [];
        foreach ($activities as $i => $activity) {
            if (!empty($activity['address'])) {
                $coords = $geocodingService->getCoordinates($activity['name'] ?? '', $itinerary->country);
                $activities[$i]['latitude'] = $coords['latitude'];
                $activities[$i]['longitude'] = $coords['longitude'];
            }
        }
        $day->activities = $activities;
        $day->save();
        \Log::info('GeocodeDayJob: Coordinates updated', [
            'day_id' => $this->dayId
        ]);
    }
}
