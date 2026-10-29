{{-- Both classes side by side with line numbers; the error lines of the wrong class are marked for the teacher. --}}
<div class="grid gap-4 lg:grid-cols-2">
    @include('filament.exercises.code', [
        'label' => \App\Enums\CodeSide::Correct->getLabel(),
        'code' => $exercise->correct_code,
        'marked' => collect(),
    ])
    @include('filament.exercises.code', [
        'label' => \App\Enums\CodeSide::Wrong->getLabel(),
        'code' => $exercise->wrong_code,
        'marked' => $exercise->errors->keyBy('line'),
    ])
</div>
