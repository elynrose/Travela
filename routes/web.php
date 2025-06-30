<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayoutRequestController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ReceivedOrdersController;
use App\Models\Order;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('itineraries.index');
    }
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $itineraries = $user->itineraries()->latest()->take(5)->get();
        $receivedOrders = Order::whereHas('itinerary', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['user', 'itinerary'])
            ->latest()
            ->take(5)
            ->get();
        $payouts = $user->payouts()->latest()->take(5)->get();
        
        return view('dashboard', compact('itineraries', 'receivedOrders', 'payouts'));
    })->name('dashboard');

    // Protected Itinerary Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/itineraries/create', [ItineraryController::class, 'create'])->name('itineraries.create');
        Route::post('/itineraries', [ItineraryController::class, 'store'])->name('itineraries.store');
        Route::get('/itineraries/{itinerary}/edit', [ItineraryController::class, 'edit'])->name('itineraries.edit');
        Route::put('/itineraries/{itinerary}', [ItineraryController::class, 'update'])->name('itineraries.update');
        Route::delete('/itineraries/{itinerary}/gallery', [ItineraryController::class, 'deleteGalleryImage'])->name('itineraries.gallery.delete');
        Route::delete('/itineraries/{itinerary}', [ItineraryController::class, 'destroy'])->name('itineraries.destroy');
        Route::post('itineraries/{itinerary}/publish', [ItineraryController::class, 'publish'])->name('itineraries.publish');
        Route::post('itineraries/{itinerary}/unpublish', [ItineraryController::class, 'unpublish'])->name('itineraries.unpublish');
        Route::get('itineraries/{itinerary}/days/edit', [ItineraryController::class, 'editDays'])->name('itineraries.days.edit');
        Route::put('itineraries/{itinerary}/days', [ItineraryController::class, 'updateDays'])->name('itineraries.days.update');
        Route::delete('itineraries/days/{day}/photos/{photoPath}', [ItineraryController::class, 'deleteDayPhoto'])
            ->name('days.photos.delete')
            ->where('photoPath', '.*');
        Route::get('itineraries/{itinerary}/days/view', [ItineraryController::class, 'showDays'])->name('itineraries.days.show');
        Route::get('/my-itineraries', [ItineraryController::class, 'myItineraries'])->name('itineraries.my');
        Route::post('/itineraries/{itinerary}/copy', [ItineraryController::class, 'copy'])->name('itineraries.copy');
        Route::post('/itineraries/{itinerary}/cover-image', [App\Http\Controllers\ItineraryController::class, 'uploadCoverImage'])->name('itineraries.uploadCoverImage');
    });

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

    // Payment routes
    Route::post('/payment/create-intent/{order}', [PaymentController::class, 'createPaymentIntent'])->name('payment.create-intent');
    Route::get('/payment/success', [PaymentController::class, 'handleSuccessfulPayment'])->name('payment.success');

    // Payout Requests
    Route::get('/payout-requests', [PayoutRequestController::class, 'index'])->name('payout-requests.index');
    Route::get('/payout-requests/create', [PayoutRequestController::class, 'create'])->name('payout-requests.create');
    Route::post('/payout-requests', [PayoutRequestController::class, 'store'])->name('payout-requests.store');
    Route::get('/payout-requests/{payoutRequest}', [PayoutRequestController::class, 'show'])->name('payout-requests.show');
    Route::post('/payout-requests/{payoutRequest}/complete', [PayoutRequestController::class, 'complete'])->name('payout-requests.complete');
    
    // Admin only routes
    Route::middleware(['web', \App\Http\Middleware\CheckAdmin::class])->group(function () {
        Route::post('/payout-requests/{payoutRequest}/approve', [PayoutRequestController::class, 'approve'])->name('payout-requests.approve');
        Route::post('/payout-requests/{payoutRequest}/reject', [PayoutRequestController::class, 'reject'])->name('payout-requests.reject');
    });

    // Received Orders Routes
    Route::get('/received-orders', [ReceivedOrdersController::class, 'index'])->name('received-orders.index');
    Route::get('/received-orders/{order}', [ReceivedOrdersController::class, 'show'])->name('received-orders.show');
    Route::post('/received-orders/{order}/complete', [ReceivedOrdersController::class, 'complete'])->name('received-orders.complete');
});

// Stripe webhook route (no auth middleware)
Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook'])->name('stripe.webhook');

// Public routes
Route::get('itineraries', [\App\Http\Controllers\ItineraryController::class, 'index'])->name('itineraries.index');
Route::get('itineraries/{itinerary:slug}', [\App\Http\Controllers\ItineraryController::class, 'show'])->name('itineraries.show');
Route::get('categories/{category:slug}', [\App\Http\Controllers\CategoryController::class, 'show'])->name('categories.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar');
});

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Admin Routes
Route::middleware(['auth', 'web', \App\Http\Middleware\CheckAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/block', [App\Http\Controllers\Admin\UserController::class, 'block'])->name('users.block');
    Route::post('/users/{user}/unblock', [App\Http\Controllers\Admin\UserController::class, 'unblock'])->name('users.unblock');
    Route::get('/users/online-count', [App\Http\Controllers\Admin\UserController::class, 'onlineCount'])->name('users.online-count');
    
    // Itinerary Management
    Route::get('/itineraries', [App\Http\Controllers\Admin\ItineraryController::class, 'index'])->name('itineraries.index');
    Route::get('/itineraries/{itinerary}', [App\Http\Controllers\Admin\ItineraryController::class, 'show'])->name('itineraries.show');
    Route::post('/itineraries/{itinerary}/toggle-featured', [App\Http\Controllers\Admin\ItineraryController::class, 'toggleFeatured'])->name('itineraries.toggle-featured');
    
    // Order Management
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    
    // Payout Management
    Route::get('/payouts', [App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/{payoutRequest}/approve', [App\Http\Controllers\Admin\PayoutController::class, 'approve'])->name('payouts.approve');
    Route::post('/payouts/{payoutRequest}/reject', [App\Http\Controllers\Admin\PayoutController::class, 'reject'])->name('payouts.reject');
    
    // Category Management
    Route::get('/categories', [App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Page Management
    Route::resource('pages', App\Http\Controllers\Admin\PageController::class);
});

// Static Pages
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/cookies', [PageController::class, 'cookies'])->name('pages.cookies');

// Dynamic Pages
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');

// Test route for email configuration with error reporting
Route::get('/test-email-debug', function () {
    try {
        Mail::raw('This is a test email from Laravel using Mailpit!', function($message) {
            $message->to('test@example.com')
                    ->subject('Test Email');
        });
        
        return 'Test email sent! Check Mailpit at http://localhost:8025';
    } catch (\Exception $e) {
        return 'Error sending email: ' . $e->getMessage() . '<br><br>Mail config: <br>' . 
               'MAIL_MAILER=' . config('mail.default') . '<br>' .
               'MAIL_HOST=' . config('mail.mailers.smtp.host') . '<br>' .
               'MAIL_PORT=' . config('mail.mailers.smtp.port') . '<br>' .
               'MAIL_FROM_ADDRESS=' . config('mail.from.address') . '<br>' .
               'MAIL_FROM_NAME=' . config('mail.from.name') . '<br>' .
               'APP_URL=' . config('app.url');
    }
});

// Test route for admin status
Route::get('/test-admin', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return response()->json([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'isAdmin()' => $user->isAdmin(),
            'role' => $user->role ?? 'not set'
        ]);
    }
    return response()->json(['error' => 'Not authenticated']);
});

require __DIR__.'/auth.php';
