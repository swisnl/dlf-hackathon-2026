<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExercisePolicy
{
    public function view(User $user, Exercise $exercise): bool
    {
        return $exercise->user_id === $user->id;
    }

    public function answer(User $user, Exercise $exercise): Response
    {
        if (!$this->view($user, $exercise)) {
            return Response::denyAsNotFound();
        }

        return $exercise->isAnswered()
            ? Response::deny('Deze oefening heb je al beantwoord.')
            : Response::allow();
    }

    public function discuss(User $user, Exercise $exercise): Response
    {
        if (!$this->view($user, $exercise)) {
            return Response::denyAsNotFound();
        }

        return $exercise->isAnswered()
            ? Response::allow()
            : Response::deny('Beantwoord eerst de oefening.');
    }
}
