<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Services\ExerciseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ExerciseMessageController extends Controller
{
    public function store(Request $request, Exercise $exercise, ExerciseService $exercises): RedirectResponse
    {
        Gate::authorize('discuss', $exercise);

        $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $exercises->reply($exercise, $request->string('content')->toString());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'De AI kon nu niet reageren. Probeer het zo opnieuw.');
        }

        return redirect()->to(route('exercises.show', $exercise).'#gesprek');
    }
}
