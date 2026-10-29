<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An exercise with the student's own status and stars; load it with Exercise::withProgressOf().
 *
 * @mixin \App\Models\Exercise
 */
class StudentExerciseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'stars' => $this->stars,
            'bestStars' => $this->progressOfStudent()->best_stars ?? 0,
            'status' => $this->statusOfStudent()->value,
            'statusLabel' => $this->statusOfStudent()->getLabel(),
            'topic' => $this->topic?->only('name', 'color', 'text_color'),
            'aiGenerated' => $this->isAiGenerated(),
        ];
    }
}
