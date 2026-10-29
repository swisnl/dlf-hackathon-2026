<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmissionStoreRequest;
use App\Models\Exercise;
use App\Models\Submission;
use App\Services\ExerciseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SubmissionController extends Controller
{
    /**
     * File extensions of code files a student may upload.
     */
    public const array EXTENSIONS = [
        'php', 'js', 'ts', 'jsx', 'tsx', 'vue', 'py', 'java', 'cs', 'c', 'cpp', 'h', 'rb', 'go', 'rs',
        'kt', 'swift', 'html', 'css', 'scss', 'sql', 'json', 'txt',
    ];

    public function index(Request $request): Response
    {
        return Inertia::render('Submissions/Index', [
            'submissions' => $this->user($request)->submissions()->withCount('exercises')->latest()->get()
                ->map(fn (Submission $submission): array => [
                    'id' => $submission->id,
                    'title' => $submission->title,
                    'filename' => $submission->filename,
                    'exercises_count' => $submission->exercises_count,
                    'created_at' => $submission->created_at?->format('d-m-Y'),
                ]),
            'extensions' => self::EXTENSIONS,
        ]);
    }

    public function store(SubmissionStoreRequest $request, ExerciseService $exercises): RedirectResponse
    {
        $submission = $this->user($request)->submissions()->create([
            'title' => $request->string('title')->toString(),
            'filename' => $request->filename(),
            'code' => $request->code(),
        ]);

        $this->allowSlowAi();

        try {
            $exercise = $exercises->generate($submission);
        } catch (Throwable $e) {
            return redirect()->route('submissions.show', $submission)
                ->with('error', $this->aiFailed($e, 'Je code is opgeslagen, maar er is nog geen vraag. Klik op "Stel me een nieuwe vraag" om het opnieuw te proberen.'));
        }

        return redirect()->route('exercises.show', $exercise);
    }

    public function show(Submission $submission): Response
    {
        Gate::authorize('view', $submission);

        return Inertia::render('Submissions/Show', [
            'submission' => $submission->only('id', 'title', 'filename', 'code'),
            'exercises' => $submission->exercises()->with('topic', 'attempts')->latest('id')->get()
                ->map(fn (Exercise $exercise): array => [
                    'id' => $exercise->id,
                    'topic' => $exercise->topic->name,
                    'title' => $exercise->title,
                    'stars' => $exercise->stars,
                    'earned_stars' => $exercise->attempts->first()?->stars,
                ]),
        ]);
    }
}
