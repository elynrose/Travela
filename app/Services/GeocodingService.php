<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeocodingService
{
    public function getCoordinates(string $location, string $country): array
    {
        // Create a cache key from the location and country
        $cacheKey = 'geocode_' . md5($location . '_' . $country);
        
        // Try to get from cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Construct the query URL for Google Maps Geocoding API
            $query = urlencode($location . ', ' . $country);
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$query}&key=" . config('services.google.maps_api_key');
            
            $response = Http::get($url);
            
            if ($response->successful() && $response->json('status') === 'OK') {
                $data = $response->json('results.0.geometry.location');
                $coordinates = [
                    'latitude' => (float) $data['lat'],
                    'longitude' => (float) $data['lng']
                ];
                
                // Cache the results for 30 days
                Cache::put($cacheKey, $coordinates, now()->addDays(30));
                
                return $coordinates;
            }
            
            return [
                'latitude' => null,
                'longitude' => null
            ];
        } catch (\Exception $e) {
            return [
                'latitude' => null,
                'longitude' => null
            ];
        }
    }
} 