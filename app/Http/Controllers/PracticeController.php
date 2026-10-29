<?php

namespace App\Http\Controllers;

use App\Enums\ProgressStatus;
use App\Enums\Variant;
use App\Grading\Grade;
use App\Highlighting\CodeHighlighter;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PracticeController extends Controller
{
    public function show(Request $request, Exercise $exercise, CodeHighlighter $highlighter): Response
    {
        Gate::authorize('practice', $exercise);

        $user = $this->user($request);
        $attempts = $exercise->attempts()->whereBelongsTo($user)->count();
        $status = $exercise->progress()->whereBelongsTo($user)->value('status');

        // Only the code goes to the page: which class is wrong, the error lines and the hints stay on the server.
        return Inertia::render('Exercises/Practice', [
            'exercise' => [
                ...$exercise->only('id', 'title', 'stars'),
                'topic' => $exercise->topic->only('name', 'color', 'text_color'),
            ],
            'classes' => array_map(function (Variant $variant) use ($exercise, $user, $highlighter): array {
                $code = $exercise->codeFor($user, $variant);

                return [
                    'value' => $variant->value,
                    'file' => $this->fileName($code),
                    'lines' => $highlighter->lines($code),
                ];
            }, Variant::cases()),
            'attempt' => min($attempts + 1, Grade::MAX_ATTEMPTS),
            'maxAttempts' => Grade::MAX_ATTEMPTS,
            'completed' => $status === ProgressStatus::Done,
        ]);
    }

    /**
     * The file the class would live in, such as StorePostRequest.php.
     */
    private function fileName(string $code): ?string
    {
        return preg_match('/^\s*(?:(?:abstract|final|readonly)\s+)*(?:class|enum|interface|trait)\s+(\w+)/m', $code, $match) === 1
            ? "{$match[1]}.php"
            : null;
    }
}
