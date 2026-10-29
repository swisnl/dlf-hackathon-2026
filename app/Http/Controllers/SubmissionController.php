<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\ExerciseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    public function index(Request $request): View
    {
        return view('submissions.index', [
            'submissions' => $this->user($request)->submissions()->withCount('exercises')->latest()->get(),
            'extensions' => self::EXTENSIONS,
        ]);
    }

    public function store(Request $request, ExerciseService $exercises): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'required_without:code', 'file', 'max:200', 'extensions:'.implode(',', self::EXTENSIONS)],
            'code' => ['nullable', 'required_without:file', 'string', 'max:200000'],
        ]);

        $file = $request->file('file');
        $code = $file instanceof UploadedFile ? (string) $file->get() : $request->string('code')->toString();

        if (!mb_check_encoding($code, 'UTF-8') || trim($code) === '') {
            throw ValidationException::withMessages($file instanceof UploadedFile
                ? ['file' => 'Dit bestand bevat geen leesbare code.']
                : ['code' => 'Plak hier leesbare code.']);
        }

        $submission = $this->user($request)->submissions()->create([
            'title' => $request->string('title')->toString(),
            'filename' => $file instanceof UploadedFile ? Str::limit(basename($file->getClientOriginalName()), 255, '') : null,
            'code' => $code,
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

    public function show(Submission $submission): View
    {
        Gate::authorize('view', $submission);

        return view('submissions.show', [
            'submission' => $submission,
            'exercises' => $submission->exercises()->with('topic', 'attempts')->latest('id')->get(),
        ]);
    }
}
