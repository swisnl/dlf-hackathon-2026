<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ExerciseMessageStoreRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('discuss', $this->route('exercise'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
        ];
    }

    public function content(): string
    {
        return $this->string('content')->toString();
    }
}
