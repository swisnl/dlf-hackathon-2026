<?php

namespace App\Http\Controllers;

use App\Enums\CodeSide;
use App\Enums\ProgressStatus;
use App\Enums\Variant;
use App\Exceptions\ExerciseAlreadyAnswered;
use App\Http\Resources\StudentExerciseResource;
use App\Models\Exercise;
use App\Models\Submission;
use App\Models\Topic;
use App\Services\ExerciseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ExerciseController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->user($request);

        $topic = $request->string('topic')->toString() ?: null;
        $status = $request->enum('status', ProgressStatus::class);

        $exercises = Exercise::query()
            ->published()
            ->withProgressOf($user)
            ->with('topic')
            ->when($topic, fn (Builder $query) => $query->whereRelation('topic', 'name', $topic))
            ->orderBy('published_at')
            ->orderBy('id')
            ->get()
            // The status comes from the student's progress, so it is filtered after loading.
            ->when($status, fn ($exercises) => $exercises->filter(fn (Exercise $exercise): bool => $exercise->statusOfStudent() === $status));

        return Inertia::render('Exercises/Index', [
            'topics' => Topic::query()->orderBy('id')->pluck('name'),
            'statuses' => collect(ProgressStatus::cases())->map(fn (ProgressStatus $status): array => [
                'value' => $status->value,
                'label' => $status->getLabel(),
            ]),
            'filters' => [
                'topic' => $topic,
                'status' => $status?->value,
            ],
            'exercises' => StudentExerciseResource::collection($exercises->values())->resolve(),
        ]);
    }

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

    public function show(Request $request, Exercise $exercise): Response
    {
        Gate::authorize('view', $exercise);

        $attempt = $exercise->attempts()->whereBelongsTo($this->user($request))->with('messages')->first();
        $submission = $exercise->submission;

        // Which variant is correct, the errors and the feedback only go to the page once the student answered.
        return Inertia::render('Exercises/Show', [
            'exercise' => [
                'id' => $exercise->id,
                'title' => $exercise->title,
                'topic' => $exercise->topic->name,
                'stars' => $exercise->stars,
                'submission' => $submission?->only('id', 'title'),
            ],
            'variants' => array_map(fn (Variant $variant): array => [
                'value' => $variant->value,
                'label' => $variant->label(),
                'code' => $exercise->code($variant),
                'correct' => $attempt !== null ? $exercise->side($variant) === CodeSide::Correct : null,
            ], Variant::cases()),
            'attempt' => $attempt === null ? null : [
                'chosen' => $attempt->chosen_side !== null ? $exercise->variant($attempt->chosen_side)->value : null,
                'correct' => $attempt->chosen_side === CodeSide::Correct,
                'stars' => $attempt->stars,
                'explanation' => $attempt->explanation,
                'feedback' => $attempt->feedback,
                'wrong_variant' => $exercise->variant(CodeSide::Wrong)->label(),
                'errors' => $exercise->errors()->orderBy('line')->get()->map->only('id', 'line', 'title', 'explanation'),
                'messages' => $attempt->messages->map->only('id', 'role', 'content'),
            ],
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
