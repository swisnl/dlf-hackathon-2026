<?php

namespace App\Policies;

use App\Models\User;

/**
 * Teachers and admins follow the progress of students and may delete their accounts.
 */
final class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->hasPanelAccess();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $user->role->hasPanelAccess();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->role->hasPanelAccess();
    }

    /**
     * Determine whether the user can delete models in bulk.
     */
    public function deleteAny(User $user): bool
    {
        return $user->role->hasPanelAccess();
    }
}
