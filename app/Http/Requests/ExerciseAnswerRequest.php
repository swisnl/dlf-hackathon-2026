<?php

namespace App\Http\Requests;

use App\Enums\Variant;
use App\Models\Exercise;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ExerciseAnswerRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('answer', $this->exercise());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $exercise = $this->exercise();
        $lineCount = max(substr_count($exercise->correct_code, "\n"), substr_count($exercise->wrong_code, "\n")) + 1;

        return [
            'choice' => ['required', Rule::enum(Variant::class)],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*' => ['required', 'integer', 'distinct', "between:1,{$lineCount}"],
            'explanation' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function choice(): Variant
    {
        return $this->enum('choice', Variant::class) ?? Variant::A;
    }

    /**
     * The lines the student clicked in the chosen variant.
     *
     * @return list<int>
     */
    public function lines(): array
    {
        return array_map(fn (int|string $key): int => $this->integer("lines.{$key}"), array_keys($this->array('lines')));
    }

    public function explanation(): string
    {
        return $this->string('explanation')->toString();
    }

    private function exercise(): Exercise
    {
        $exercise = $this->route('exercise');
        assert($exercise instanceof Exercise);

        return $exercise;
    }
}
