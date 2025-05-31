<?php

namespace App\Policies;

use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayoutRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can view their own payout requests
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PayoutRequest $payoutRequest): bool
    {
        return $user->id === $payoutRequest->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create payout requests
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PayoutRequest $payoutRequest): bool
    {
        return $user->id === $payoutRequest->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PayoutRequest $payoutRequest): bool
    {
        return $user->id === $payoutRequest->user_id || $user->isAdmin();
    }
} 