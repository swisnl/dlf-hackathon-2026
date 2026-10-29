<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExerciseMessageStoreRequest;
use App\Models\Exercise;
use App\Services\ExerciseService;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ExerciseMessageController extends Controller
{
    public function store(ExerciseMessageStoreRequest $request, Exercise $exercise, ExerciseService $exercises): RedirectResponse
    {
        $attempt = $exercise->attempts()->whereBelongsTo($this->user($request))->firstOrFail();
        $this->allowSlowAi();

        try {
            $exercises->reply($attempt, $request->content());
        } catch (Throwable $e) {
            return redirect()->to(route('exercises.show', $exercise).'#gesprek')->withInput()
                ->with('error', $this->aiFailed($e, 'Er kwam nu geen reactie. Je bericht staat nog in het tekstvak; probeer het zo opnieuw.'));
        }

        return redirect()->to(route('exercises.show', $exercise).'#gesprek');
    }
}
