<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    $featuredItineraries = \App\Models\Itinerary::with(['user', 'categories'])
        ->where('is_published', true)
        ->where('is_featured', true)
        ->latest()
        ->take(6)
        ->get();

    $categories = \App\Models\Category::where('is_active', true)->get();

    return view('welcome', compact('featuredItineraries', 'categories'));
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $itineraries = $user->itineraries()->latest()->take(5)->get();
        $orders = $user->orders()->latest()->take(5)->get();
        $payouts = $user->payouts()->latest()->take(5)->get();
        
        return view('dashboard', compact('itineraries', 'orders', 'payouts'));
    })->name('dashboard');

    // Itineraries
    Route::resource('itineraries', \App\Http\Controllers\ItineraryController::class);
    Route::post('itineraries/{itinerary}/publish', [\App\Http\Controllers\ItineraryController::class, 'publish'])->name('itineraries.publish');
    Route::post('itineraries/{itinerary}/unpublish', [\App\Http\Controllers\ItineraryController::class, 'unpublish'])->name('itineraries.unpublish');
    Route::delete('/itineraries/{itinerary}/gallery/{media}', [\App\Http\Controllers\ItineraryController::class, 'deleteGalleryImage'])
        ->name('itineraries.gallery.delete')
        ->middleware(['auth']);
    Route::get('itineraries/{itinerary}/days/edit', [\App\Http\Controllers\ItineraryController::class, 'editDays'])->name('itineraries.days.edit');
    Route::put('itineraries/{itinerary}/days', [\App\Http\Controllers\ItineraryController::class, 'updateDays'])->name('itineraries.days.update');
    Route::delete('days/{day}/photos/{mediaId}', [\App\Http\Controllers\ItineraryController::class, 'deleteDayPhoto'])
        ->name('days.photos.delete')
        ->middleware(['auth']);
    Route::get('itineraries/{itinerary}/days/view', [ItineraryController::class, 'showDays'])->name('itineraries.days.show')->middleware(['auth', 'verified']);

    // Orders
    Route::resource('orders', \App\Http\Controllers\OrderController::class)->only(['index', 'show', 'store']);

    // Payouts
    Route::resource('payouts', \App\Http\Controllers\PayoutController::class)->only(['index', 'store']);
    Route::get('payouts/request', [\App\Http\Controllers\PayoutController::class, 'create'])->name('payouts.create');

    // Messages
    Route::resource('messages', \App\Http\Controllers\MessageController::class)->only(['index', 'store', 'show']);
    Route::get('messages/with/{user}', [\App\Http\Controllers\MessageController::class, 'conversation'])->name('messages.conversation');

    // Two Factor Authentication Routes
    Route::post('/two-factor-authentication', [App\Http\Controllers\TwoFactorAuthenticationController::class, 'store'])
        ->name('two-factor.enable');
    Route::delete('/two-factor-authentication', [App\Http\Controllers\TwoFactorAuthenticationController::class, 'destroy'])
        ->name('two-factor.disable');
    Route::post('/two-factor-recovery-codes', [App\Http\Controllers\TwoFactorAuthenticationController::class, 'regenerate'])
        ->name('two-factor.recovery-codes');

    // Chat routes
    Route::get('/itineraries/{itinerary}/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/itineraries/{itinerary}/messages', [ChatController::class, 'sendMessage'])->name('chat.send');
});

// Public routes
Route::get('itineraries', [\App\Http\Controllers\ItineraryController::class, 'index'])->name('itineraries.index');
Route::get('itineraries/{itinerary:slug}', [\App\Http\Controllers\ItineraryController::class, 'show'])->name('itineraries.show');
Route::get('categories/{category:slug}', [\App\Http\Controllers\CategoryController::class, 'show'])->name('categories.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar');
});

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

require __DIR__.'/auth.php';
