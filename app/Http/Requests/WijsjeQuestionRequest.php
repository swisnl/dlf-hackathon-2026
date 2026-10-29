<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class WijsjeQuestionRequest extends FormRequest
{
    /**
     * The longest conversation sent back to the AI; older messages are dropped in the browser.
     */
    public const int MAX_HISTORY = 10;

    /**
     * Only an exercise the student may open can be given as context.
     */
    public function authorize(): Response
    {
        return $this->exercise() instanceof Exercise
            ? Gate::inspect('view', $this->exercise())
            : Response::allow();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:1000'],
            'exercise_id' => ['nullable', 'integer', Rule::exists(Exercise::class, 'id')],
            'history' => ['array', 'max:'.self::MAX_HISTORY],
            'history.*.role' => ['required', Rule::in(['student', 'wijsje'])],
            'history.*.content' => ['required', 'string', 'max:2000'],
        ];
    }

    public function question(): string
    {
        return $this->string('question')->toString();
    }

    public function exercise(): ?Exercise
    {
        return $this->filled('exercise_id') ? Exercise::query()->find($this->integer('exercise_id')) : null;
    }

    /**
     * @return list<array{role: 'student'|'wijsje', content: string}>
     */
    public function history(): array
    {
        $history = [];

        foreach ($this->array('history') as $message) {
            if (is_array($message) && is_string($message['content'] ?? null)) {
                $history[] = [
                    'role' => ($message['role'] ?? null) === 'student' ? 'student' : 'wijsje',
                    'content' => $message['content'],
                ];
            }
        }

        return $history;
    }
}
