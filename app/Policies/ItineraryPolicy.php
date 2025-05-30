<?php

namespace App\Policies;

use App\Models\Itinerary;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItineraryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view the list of itineraries
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Itinerary $itinerary): bool
    {
        return $itinerary->is_published || ($user && $user->id === $itinerary->user_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create an itinerary
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Itinerary $itinerary): bool
    {
        return $user->id === $itinerary->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Itinerary $itinerary): bool
    {
        return $user->id === $itinerary->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Itinerary $itinerary): bool
    {
        return $user->id === $itinerary->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Itinerary $itinerary): bool
    {
        return $user->id === $itinerary->user_id;
    }
}
