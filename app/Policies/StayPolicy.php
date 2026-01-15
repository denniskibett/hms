<?php

namespace App\Policies;

use App\Models\Stay;
use App\Models\User;

class StayPolicy
{
    public function before(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    /**
     * View list of stays
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['receptionist', 'guest']);
    }

    /**
     * View a specific stay
     */
    public function view(User $user, Stay $stay): bool
    {
        if ($user->hasRole('receptionist')) {
            return true;
        }

        // Guest can only view their own stay
        return $user->id === $stay->guest_id;
    }

    /**
     * Create stay (booking)
     */
    public function create(User $user): bool
    {
        return $user->hasRole('receptionist');
    }

    /**
     * Update stay
     */
    public function update(User $user, Stay $stay): bool
    {
        return $user->hasRole('receptionist');
    }

    /**
     * Delete stay
     */
    public function delete(User $user, Stay $stay): bool
    {
        return $user->hasRole('receptionist');
    }

    /**
     * Check-in
     */
    public function checkIn(User $user, Stay $stay): bool
    {
        return $user->hasRole('receptionist');
    }

    /**
     * Check-out
     */
    public function checkOut(User $user, Stay $stay): bool
    {
        return $user->hasRole('receptionist');
    }

 
    public function updateAny(User $user): bool
    {
        return $user->hasRole('receptionist');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('receptionist');
    }

}
