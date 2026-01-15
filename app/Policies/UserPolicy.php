<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admin can do everything.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null; // continue to normal checks
    }

    /**
     * Determine whether the user can view any users (list guests).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('receptionist');
    }

    /**
     * Determine whether the user can view a specific guest.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole('receptionist');
    }

    /**
     * Determine whether the user can create a new guest.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('receptionist');
    }

    /**
     * Determine whether the user can update a guest.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('receptionist');
    }

    /**
     * Determine whether the user can delete a guest.
     */
    public function delete(User $user, User $model): bool
    {
        return false; // admin already allowed via before()
    }

    public function checkIn(User $user): bool
    {
        return $user->hasRole('receptionist');
    }

    public function checkOut(User $user): bool
    {
        return $user->hasRole('receptionist');
    }

    public function extendStay(User $user): bool
    {
        return $user->hasRole('receptionist');
    }
}
