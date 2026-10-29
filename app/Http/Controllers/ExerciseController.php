<?php

namespace App\Http\Controllers;

use App\Enums\ProgressStatus;
use App\Http\Resources\StudentExerciseResource;
use App\Models\Exercise;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

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
}
