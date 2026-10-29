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

        $attempt = $exercise->attempts()->whereBelongsTo($this->user($request))->firstOrFail();
        $this->allowSlowAi();

        try {
            $exercises->reply($attempt, $request->string('content')->toString());
        } catch (Throwable $e) {
            return redirect()->to(route('exercises.show', $exercise).'#gesprek')->withInput()
                ->with('error', $this->aiFailed($e, 'Er kwam nu geen reactie. Je bericht staat nog in het tekstvak; probeer het zo opnieuw.'));
        }

        return redirect()->to(route('exercises.show', $exercise).'#gesprek');
    }
}
