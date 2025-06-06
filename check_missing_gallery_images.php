<?php

use App\Models\Itinerary;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$disk = 'public';

foreach (Itinerary::all() as $itinerary) {
    $missing = [];
    // Check cover image
    if ($itinerary->cover_image && !\Storage::disk($disk)->exists($itinerary->cover_image)) {
        $missing[] = $itinerary->cover_image;
    }
    // Check gallery images
    if (is_array($itinerary->gallery)) {
        foreach ($itinerary->gallery as $img) {
            if ($img && !\Storage::disk($disk)->exists($img)) {
                $missing[] = $img;
            }
        }
    }
    if ($missing) {
        echo "Itinerary: {$itinerary->slug}\n";
        foreach ($missing as $file) {
            echo "  MISSING: storage/app/public/{$file}\n";
        }
    }
}

echo "Done.\n"; 