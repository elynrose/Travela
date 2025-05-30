<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Send welcome notification
        $user->notify(new WelcomeNotification());
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Handle avatar changes
        if ($user->isDirty('avatar') && $user->getOriginal('avatar')) {
            // Delete old avatar file
            Storage::disk('public')->delete($user->getOriginal('avatar'));
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Delete user's avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // If needed, handle any logic when a user is restored
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Delete user's avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
    }
} 