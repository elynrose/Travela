<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileController extends Controller
{
    protected $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'max:1024'], // Max 1MB
            ]);

            $user = $request->user();
            $file = $request->file('avatar');
            
            // Delete old avatar if exists
            if ($user->avatar) {
                // Delete both original and thumbnail
                Storage::disk('public')->delete($user->avatar);
                Storage::disk('public')->delete(str_replace('avatars/', 'avatars/thumbnails/', $user->avatar));
            }

            // Create image instance using the image manager
            $image = $this->imageManager->read($file);
            
            // Generate unique filename
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Save original image
            $originalPath = 'avatars/' . $filename;
            
            // Ensure directory exists
            Storage::disk('public')->makeDirectory(dirname($originalPath));
            
            // Save the image
            $image->save(Storage::disk('public')->path($originalPath));
            
            // Create and save thumbnail
            $thumbPath = 'avatars/thumbnails/' . $filename;
            
            // Ensure thumbnail directory exists
            Storage::disk('public')->makeDirectory(dirname($thumbPath));
            
            // Create and save the thumbnail
            $image->cover(200, 200)->save(Storage::disk('public')->path($thumbPath));

            // Update the avatar field in the database
            $user->update(['avatar' => $originalPath]);

            return back()->with('status', 'avatar-updated');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update avatar: ' . $e->getMessage());
        }
    }

    /**
     * Remove the user's avatar.
     */
    public function removeAvatar(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();
            
            if ($user->avatar) {
                // Delete both original and thumbnail
                Storage::disk('public')->delete($user->avatar);
                Storage::disk('public')->delete(str_replace('avatars/', 'avatars/thumbnails/', $user->avatar));
                
                // Clear the avatar field
                $user->update(['avatar' => null]);
            }
            
            return back()->with('status', 'avatar-removed');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to remove avatar: ' . $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        $user = $request->user();
        
        // Load relationships and counts
        $user->loadCount([
            'itineraries',
            'reviews',
            'favorites'
        ]);

        // Get recent activities
        $user->recent_activities = collect()
            ->merge($user->itineraries()->latest()->take(3)->get()->map(function ($itinerary) {
                return (object)[
                    'icon' => 'fa-route',
                    'description' => "Created itinerary: {$itinerary->title}",
                    'created_at' => $itinerary->created_at
                ];
            }))
            ->merge($user->reviews()->latest()->take(3)->get()->map(function ($review) {
                return (object)[
                    'icon' => 'fa-star',
                    'description' => "Reviewed: {$review->itinerary->title}",
                    'created_at' => $review->created_at
                ];
            }))
            ->sortByDesc('created_at')
            ->take(5);

        return view('profile.show', compact('user'));
    }
}
