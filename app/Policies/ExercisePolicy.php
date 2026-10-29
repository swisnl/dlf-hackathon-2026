<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Teachers and admins create, change and publish exercises in the backoffice.
 * Students practice the published exercises and see and answer the exercises the AI made about their own code.
 */
final class ExercisePolicy
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
    public function view(User $user, Exercise $exercise): bool
    {
        return $user->role->hasPanelAccess() || $this->ownsSubmission($user, $exercise);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role->hasPanelAccess();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
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

    public function answer(User $user, Exercise $exercise): Response
    {
        if (!$this->ownsSubmission($user, $exercise)) {
            return Response::denyAsNotFound();
        }

        return $exercise->attempts()->whereBelongsTo($user)->exists()
            ? Response::deny('Deze oefening heb je al beantwoord.')
            : Response::allow();
    }

    /**
     * Every student practices the published exercises the teachers made.
     */
    public function practice(User $user, Exercise $exercise): Response
    {
        return $exercise->published_at !== null && $exercise->submission_id === null
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function discuss(User $user, Exercise $exercise): Response
    {
        if (!$this->ownsSubmission($user, $exercise)) {
            return Response::denyAsNotFound();
        }

        return $exercise->attempts()->whereBelongsTo($user)->exists()
            ? Response::allow()
            : Response::deny('Beantwoord eerst de oefening.');
    }

    /**
     * Whether the AI made this exercise about the user's own code.
     */
    private function ownsSubmission(User $user, Exercise $exercise): bool
    {
        return $exercise->submission !== null && $exercise->submission->user_id === $user->id;
    }
}
