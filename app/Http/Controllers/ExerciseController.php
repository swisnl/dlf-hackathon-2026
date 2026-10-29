<?php

namespace App\Http\Controllers;

use App\Enums\Variant;
use App\Models\Exercise;
use App\Models\Submission;
use App\Services\ExerciseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Throwable;

class ExerciseController extends Controller
{
    public function store(Submission $submission, ExerciseService $exercises): RedirectResponse
    {
        Gate::authorize('view', $submission);

        try {
            $exercise = $exercises->generate($submission);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'De AI kon geen nieuwe vraag maken. Probeer het zo opnieuw.');
        }

        return redirect()->route('exercises.show', $exercise);
    }

    public function show(Exercise $exercise): View
    {
        Gate::authorize('view', $exercise);

        return view('exercises.show', [
            'exercise' => $exercise->load('submission', 'messages'),
        ]);
    }

    public function answer(Request $request, Exercise $exercise, ExerciseService $exercises): RedirectResponse
    {
        Gate::authorize('answer', $exercise);

        $request->validate([
            'choice' => ['required', Rule::enum(Variant::class)],
            'explanation' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        try {
            $exercises->answer($exercise, $request->enum('choice', Variant::class) ?? Variant::A, $request->string('explanation')->toString());
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'De AI kon je antwoord nu niet beoordelen. Probeer het zo opnieuw.');
        }

        return redirect()->route('exercises.show', $exercise);
    }
}
