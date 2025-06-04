<?php

namespace Database\Seeders;

use App\Models\Day;
use App\Models\Itinerary;
use Illuminate\Database\Seeder;

class DaySeeder extends Seeder
{
    public function run(): void
    {
        // Get all published itineraries
        $itineraries = Itinerary::where('is_published', true)->get();

        foreach ($itineraries as $itinerary) {
            // Create 3-7 days for each itinerary
            $numberOfDays = rand(3, 7);
            
            for ($dayNumber = 1; $dayNumber <= $numberOfDays; $dayNumber++) {
                Day::create([
                    'itinerary_id' => $itinerary->id,
                    'day_number' => $dayNumber,
                    'accommodation' => $this->getRandomAccommodation(),
                    'accommodation_address' => $this->getRandomAddress(),
                    'meals' => $this->getRandomMeals(),
                    'activities' => $this->getRandomActivities(),
                    'notes' => $this->getRandomNotes(),
                    'receipts' => []
                ]);
            }
        }
    }

    private function getRandomAccommodation(): string
    {
        $accommodations = [
            'Luxury Hotel',
            'Boutique Hotel',
            'Resort',
            'Guest House',
            'Villa',
            'Apartment',
            'Hostel',
            'Bed & Breakfast'
        ];

        return $accommodations[array_rand($accommodations)];
    }

    private function getRandomAddress(): string
    {
        $streets = ['Main Street', 'Park Avenue', 'Beach Road', 'Mountain View', 'Sunset Boulevard'];
        $cities = ['Paris', 'London', 'New York', 'Tokyo', 'Rome', 'Barcelona', 'Amsterdam'];
        
        return rand(1, 100) . ' ' . $streets[array_rand($streets)] . ', ' . $cities[array_rand($cities)];
    }

    private function getRandomMeals(): array
    {
        $mealTypes = ['breakfast', 'lunch', 'dinner'];
        $meals = [];

        foreach ($mealTypes as $type) {
            $meals[$type] = [
                'name' => $this->getRandomRestaurant(),
                'address' => $this->getRandomAddress(),
                'description' => $this->getRandomMealDescription($type),
                'photos' => []
            ];
        }

        return $meals;
    }

    private function getRandomRestaurant(): string
    {
        $restaurants = [
            'Local Cafe',
            'Fine Dining Restaurant',
            'Street Food Market',
            'Traditional Restaurant',
            'Rooftop Bar',
            'Seaside Restaurant',
            'Mountain View Cafe'
        ];

        return $restaurants[array_rand($restaurants)];
    }

    private function getRandomMealDescription(string $type): string
    {
        $descriptions = [
            'breakfast' => [
                'Continental breakfast with fresh pastries and coffee',
                'Full English breakfast with eggs, bacon, and toast',
                'Healthy breakfast with fruits and yogurt'
            ],
            'lunch' => [
                'Local cuisine with traditional dishes',
                'Light lunch with salad and soup',
                'Street food experience with local specialties'
            ],
            'dinner' => [
                'Fine dining experience with gourmet dishes',
                'Traditional dinner with local wine',
                'Casual dining with international cuisine'
            ]
        ];

        return $descriptions[$type][array_rand($descriptions[$type])];
    }

    private function getRandomActivities(): array
    {
        $numberOfActivities = rand(2, 4);
        $activities = [];

        for ($i = 0; $i < $numberOfActivities; $i++) {
            $activities[] = [
                'name' => $this->getRandomActivityTitle(),
                'description' => $this->getRandomActivityDescription(),
                'address' => $this->getRandomAddress(),
                'entry_fee' => rand(0, 50),
                'photos' => []
            ];
        }

        return $activities;
    }

    private function getRandomActivityTitle(): string
    {
        $activities = [
            'City Tour',
            'Museum Visit',
            'Beach Time',
            'Hiking Adventure',
            'Shopping Spree',
            'Cultural Experience',
            'Local Market Visit',
            'Sunset Viewing',
            'Cooking Class',
            'Wine Tasting'
        ];

        return $activities[array_rand($activities)];
    }

    private function getRandomActivityDescription(): string
    {
        $descriptions = [
            'Explore the local culture and traditions',
            'Enjoy the beautiful scenery and nature',
            'Learn about the history and heritage',
            'Experience local cuisine and flavors',
            'Discover hidden gems and local spots',
            'Relax and unwind in a beautiful setting'
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function getRandomNotes(): string
    {
        $notes = [
            'Don\'t forget to bring comfortable walking shoes',
            'Remember to bring a camera for beautiful views',
            'Local currency is recommended for small purchases',
            'Weather-appropriate clothing is advised',
            'Book in advance for popular attractions',
            'Local transportation card is recommended'
        ];

        return $notes[array_rand($notes)];
    }
} 