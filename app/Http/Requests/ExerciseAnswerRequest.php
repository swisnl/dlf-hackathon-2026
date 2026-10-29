<?php

namespace App\Http\Requests;

use App\Enums\Variant;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ExerciseAnswerRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('answer', $this->route('exercise'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'choice' => ['required', Rule::enum(Variant::class)],
            'explanation' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function choice(): Variant
    {
        return $this->enum('choice', Variant::class) ?? Variant::A;
    }

    public function explanation(): string
    {
        return $this->string('explanation')->toString();
    }
}
