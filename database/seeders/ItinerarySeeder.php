<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Itinerary;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;

class ItinerarySeeder extends Seeder
{
    /**
     * Create a sample image file
     */
    protected function createSampleImage(string $path): void
    {
        $fullPath = storage_path('app/public/' . $path);
        $directory = dirname($fullPath);
        
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Create a simple colored image
        $image = imagecreatetruecolor(800, 400);
        $bgColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imagefill($image, 0, 0, $bgColor);
        
        // Add some text
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $text = basename($path);
        imagestring($image, 5, 10, 10, $text, $textColor);
        
        // Save the image
        imagejpeg($image, $fullPath, 90);
        imagedestroy($image);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();

        $itineraries = [
            [
                'title' => 'Bali Adventure & Culture',
                'description' => 'Experience the best of Bali with this comprehensive itinerary covering temples, beaches, and cultural experiences.',
                'price' => 899.99,
                'location' => 'Bali',
                'country' => 'Indonesia',
                'accommodation' => 'Luxury Resort & Spa',
                'accommodation_address' => 'Jl. Pantai Kuta No. 1, Kuta, Bali, Indonesia',
                'duration_days' => 7,
                'highlights' => [
                    'Visit ancient temples',
                    'Beach hopping',
                    'Traditional dance show',
                    'Rice terrace trekking',
                ],
                'included_items' => [
                    'Accommodation',
                    'Daily breakfast',
                    'Airport transfers',
                    'Local guide',
                ],
                'excluded_items' => [
                    'International flights',
                    'Travel insurance',
                    'Personal expenses',
                ],
                'requirements' => [
                    'Valid passport',
                    'Travel insurance',
                    'Comfortable walking shoes',
                ],
                'is_published' => true,
                'is_featured' => true,
                'cover_image' => 'covers/bali-adventure.jpg',
                'gallery' => [
                    'gallery/bali-1.jpg',
                    'gallery/bali-2.jpg',
                    'gallery/bali-3.jpg',
                ],
            ],
            [
                'title' => 'Paris City Break',
                'description' => 'A perfect 4-day itinerary to explore the City of Light, including iconic landmarks and hidden gems.',
                'price' => 1299.99,
                'location' => 'Paris',
                'country' => 'France',
                'accommodation' => 'Boutique Hotel',
                'accommodation_address' => '15 Rue de Rivoli, 75004 Paris, France',
                'duration_days' => 4,
                'highlights' => [
                    'Eiffel Tower visit',
                    'Louvre Museum tour',
                    'Seine River cruise',
                    'Montmartre exploration',
                ],
                'included_items' => [
                    'Hotel accommodation',
                    'Breakfast daily',
                    'Museum passes',
                    'Metro cards',
                ],
                'excluded_items' => [
                    'International flights',
                    'Travel insurance',
                    'Meals not specified',
                ],
                'requirements' => [
                    'Valid passport',
                    'Travel insurance',
                    'Comfortable walking shoes',
                ],
                'is_published' => true,
                'is_featured' => true,
                'cover_image' => 'covers/paris-city.jpg',
                'gallery' => [
                    'gallery/paris-1.jpg',
                    'gallery/paris-2.jpg',
                    'gallery/paris-3.jpg',
                ],
            ],
            [
                'title' => 'Safari Adventure in Tanzania',
                'description' => 'Experience the ultimate African safari adventure in Tanzania\'s most famous national parks.',
                'price' => 2499.99,
                'location' => 'Serengeti',
                'country' => 'Tanzania',
                'accommodation' => 'Luxury Safari Lodge',
                'accommodation_address' => 'Serengeti National Park, Tanzania',
                'duration_days' => 8,
                'highlights' => [
                    'Serengeti game drives',
                    'Ngorongoro Crater visit',
                    'Maasai village tour',
                    'Hot air balloon safari',
                ],
                'included_items' => [
                    'Luxury lodge accommodation',
                    'All meals',
                    'Game drives',
                    'Park fees',
                ],
                'excluded_items' => [
                    'International flights',
                    'Travel insurance',
                    'Personal expenses',
                ],
                'requirements' => [
                    'Valid passport',
                    'Yellow fever vaccination',
                    'Travel insurance',
                ],
                'is_published' => true,
                'is_featured' => true,
                'cover_image' => 'covers/safari-adventure.jpg',
                'gallery' => [
                    'gallery/safari-1.jpg',
                    'gallery/safari-2.jpg',
                    'gallery/safari-3.jpg',
                ],
            ],
        ];

        foreach ($itineraries as $itineraryData) {
            // Create cover image
            $this->createSampleImage($itineraryData['cover_image']);
            
            // Create gallery images
            foreach ($itineraryData['gallery'] as $galleryImage) {
                $this->createSampleImage($galleryImage);
            }

            $itinerary = Itinerary::create([
                'user_id' => $users->random()->id,
                'title' => $itineraryData['title'],
                'slug' => Str::slug($itineraryData['title']) . '-' . time(),
                'description' => $itineraryData['description'],
                'price' => $itineraryData['price'],
                'location' => $itineraryData['location'],
                'country' => $itineraryData['country'],
                'accommodation' => $itineraryData['accommodation'],
                'accommodation_address' => $itineraryData['accommodation_address'],
                'duration_days' => $itineraryData['duration_days'],
                'highlights' => $itineraryData['highlights'],
                'included_items' => $itineraryData['included_items'],
                'excluded_items' => $itineraryData['excluded_items'],
                'requirements' => $itineraryData['requirements'],
                'is_published' => $itineraryData['is_published'],
                'is_featured' => $itineraryData['is_featured'],
                'cover_image' => $itineraryData['cover_image'],
                'gallery' => $itineraryData['gallery'],
            ]);

            // Attach random categories (2-3 per itinerary)
            $randomCategories = $categories->random(rand(2, 3));
            $itinerary->categories()->attach($randomCategories->pluck('id'));
        }
    }
} 