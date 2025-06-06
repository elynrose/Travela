<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeocodingService
{
    public function getCoordinates(string $location, string $country): array
    {
        // Create a cache key from the location and country
        $cacheKey = 'geocode_' . md5($location . '_' . $country);
        
        // Try to get from cache first
        if (Cache::has($cacheKey)) {
            \Log::info('Retrieved coordinates from cache', [
                'location' => $location,
                'country' => $country
            ]);
            return Cache::get($cacheKey);
        }

        try {
            // Construct the query URL
            $query = urlencode($location . ', ' . $country);
            $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1";
            
            // Add a delay to respect rate limits
            sleep(1);
            
            $response = Http::withHeaders([
                'User-Agent' => 'Travela/1.0'
            ])->get($url);
            
            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];
                $coordinates = [
                    'latitude' => (float) $data['lat'],
                    'longitude' => (float) $data['lon']
                ];
                
                // Cache the results for 30 days
                Cache::put($cacheKey, $coordinates, now()->addDays(30));
                
                \Log::info('Retrieved coordinates from API', [
                    'location' => $location,
                    'country' => $country,
                    'coordinates' => $coordinates
                ]);
                
                return $coordinates;
            }
            
            \Log::warning('No coordinates found for location', [
                'location' => $location,
                'country' => $country
            ]);
            
            return [
                'latitude' => null,
                'longitude' => null
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting coordinates', [
                'error' => $e->getMessage(),
                'location' => $location,
                'country' => $country
            ]);
            
            return [
                'latitude' => null,
                'longitude' => null
            ];
        }
    }
} 