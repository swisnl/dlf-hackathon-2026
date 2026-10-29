<?php

namespace App\Http\Controllers;

use App\Exceptions\ExerciseAlreadyCompleted;
use App\Grading\AttemptGrader;
use App\Http\Requests\AttemptStoreRequest;
use App\Models\Exercise;
use Illuminate\Http\RedirectResponse;

class AttemptController extends Controller
{
    public function store(AttemptStoreRequest $request, Exercise $exercise, AttemptGrader $grader): RedirectResponse
    {
        $user = $this->user($request);

        try {
            $grader->grade($user, $exercise, $exercise->sideFor($user, $request->choice()), $request->lines());
        } catch (ExerciseAlreadyCompleted) {
            return redirect()->route('exercises.practice', $exercise)->with('error', 'Deze oefening heb je al afgerond.');
        }

        return redirect()->route('exercises.practice', $exercise);
    }
}
