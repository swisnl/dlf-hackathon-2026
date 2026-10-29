<?php

namespace App\Http\Controllers;

use App\Enums\Variant;
use App\Exceptions\ExerciseAlreadyAnswered;
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
        $this->allowSlowAi();

        try {
            $exercise = $exercises->generate($submission);
        } catch (Throwable $e) {
            return back()->with('error', $this->aiFailed($e, 'Er kon geen nieuwe vraag gemaakt worden. Probeer het zo opnieuw.'));
        }

        return redirect()->route('exercises.show', $exercise);
    }

    public function show(Request $request, Exercise $exercise): View
    {
        Gate::authorize('view', $exercise);

        return view('exercises.show', [
            'exercise' => $exercise->load('submission', 'topic', 'errors'),
            'attempt' => $exercise->attempts()->whereBelongsTo($this->user($request))->with('messages')->first(),
        ]);
    }

    public function answer(Request $request, Exercise $exercise, ExerciseService $exercises): RedirectResponse
    {
        Gate::authorize('answer', $exercise);

        $request->validate([
            'choice' => ['required', Rule::enum(Variant::class)],
            'explanation' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $this->allowSlowAi();

        try {
            $exercises->answer($exercise, $this->user($request), $request->enum('choice', Variant::class) ?? Variant::A, $request->string('explanation')->toString());
        } catch (ExerciseAlreadyAnswered) {
            return redirect()->route('exercises.show', $exercise)->with('error', 'Deze oefening had je al beantwoord.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $this->aiFailed($e, 'Je antwoord kon nu niet beoordeeld worden. Probeer het zo opnieuw.'));
        }

        return redirect()->route('exercises.show', $exercise);
    }
}
