<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Adventure',
                'slug' => 'adventure',
                'description' => 'Thrilling outdoor activities and expeditions',
                'is_active' => true,
            ],
            [
                'name' => 'Beach & Coastal',
                'slug' => 'beach-coastal',
                'description' => 'Relaxing beach getaways and coastal adventures',
                'is_active' => true,
            ],
            [
                'name' => 'Cultural',
                'slug' => 'cultural',
                'description' => 'Immerse yourself in local traditions and heritage',
                'is_active' => true,
            ],
            [
                'name' => 'Food & Wine',
                'slug' => 'food-wine',
                'description' => 'Culinary experiences and wine tasting tours',
                'is_active' => true,
            ],
            [
                'name' => 'Hiking & Trekking',
                'slug' => 'hiking-trekking',
                'description' => 'Scenic trails and mountain adventures',
                'is_active' => true,
            ],
            [
                'name' => 'Historical',
                'slug' => 'historical',
                'description' => 'Explore ancient sites and historical landmarks',
                'is_active' => true,
            ],
            [
                'name' => 'Nature & Wildlife',
                'slug' => 'nature-wildlife',
                'description' => 'Wildlife safaris and nature exploration',
                'is_active' => true,
            ],
            [
                'name' => 'City Break',
                'slug' => 'city-break',
                'description' => 'Urban exploration and city experiences',
                'is_active' => true,
            ],
            [
                'name' => 'Luxury',
                'slug' => 'luxury',
                'description' => 'Premium travel experiences and high-end accommodations',
                'is_active' => true,
            ],
            [
                'name' => 'Road Trip',
                'slug' => 'road-trip',
                'description' => 'Scenic drives and self-guided adventures',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
} 