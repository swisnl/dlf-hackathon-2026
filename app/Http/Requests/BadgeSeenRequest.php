<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BadgeSeenRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'then' => ['nullable', 'string', 'in:badges.index,exercises.index'],
        ];
    }

    /**
     * The route to go to after the pop-up, or null to stay on the page.
     */
    public function then(): ?string
    {
        return $this->filled('then') ? $this->string('then')->toString() : null;
    }
}
