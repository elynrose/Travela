<?php

use App\Models\Itinerary;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = 0;
foreach (Itinerary::all() as $itinerary) {
    $itinerary->gallery = [];
    $itinerary->save();
    echo "Cleared gallery for: {$itinerary->slug}\n";
    $count++;
}
echo "Done. Cleared gallery images for {$count} itineraries.\n"; 