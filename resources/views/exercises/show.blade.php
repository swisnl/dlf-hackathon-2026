@php($variants = \App\Enums\Variant::cases())
<x-layout :title="$exercise->concept">
    <a href="{{ route('submissions.show', $exercise->submission) }}" class="text-sm text-stone-600 hover:text-stone-900">&larr; {{ $exercise->submission->title }}</a>
    <p class="mt-4 text-sm font-medium uppercase tracking-wide text-stone-500">{{ $exercise->concept }}</p>
    <h1 class="mt-1 text-2xl font-semibold">{{ $exercise->question }}</h1>

    @if (! $exercise->isAnswered())
        <form method="POST" action="{{ route('exercises.answer', $exercise) }}" class="mt-6" data-ai="De AI bekijkt je antwoord…">
            @csrf
            <fieldset>
                <legend class="sr-only">Welke variant is juist?</legend>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($variants as $variant)
                        <label class="block min-w-0 cursor-pointer rounded-xl border-2 border-stone-200 bg-white p-4 has-[:checked]:border-stone-900">
                            <span class="mb-3 flex items-center gap-2 font-medium">
                                <input type="radio" name="choice" value="{{ $variant->value }}" required @checked(old('choice') === $variant->value)>
                                {{ $variant->label() }}
                            </span>
                            <x-code :code="$exercise->variant($variant)" />
                        </label>
                    @endforeach
                </div>
                <x-field-error name="choice" />
            </fieldset>

            <div class="mt-6">
                <label for="explanation" class="block font-medium">Leg uit waarom jouw keuze juist is</label>
                <p class="text-sm text-stone-500">Dit is het belangrijkste deel: laat zien dat je snapt wat er gebeurt.</p>
                <textarea id="explanation" name="explanation" rows="5" required minlength="10" class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2">{{ old('explanation') }}</textarea>
                <x-field-error name="explanation" />
            </div>
            <button type="submit" class="mt-4 rounded-lg bg-stone-900 px-4 py-2 font-medium text-white hover:bg-stone-700 disabled:cursor-wait disabled:opacity-60">Lever mijn antwoord in</button>
        </form>
    @else
        <div class="mt-6 grid gap-4 md:grid-cols-2">
            @foreach ($variants as $variant)
                @php($isCorrect = $variant === $exercise->correct_variant)
                <div class="min-w-0 rounded-xl border-2 bg-white p-4 {{ $isCorrect ? 'border-green-600' : 'border-stone-200' }}">
                    <p class="mb-3 flex flex-wrap items-center gap-2 font-medium">
                        {{ $variant->label() }}
                        @if ($isCorrect)
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">Juist</span>
                        @endif
                        @if ($variant === $exercise->chosen_variant)
                            <span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-700">Jouw keuze</span>
                        @endif
                    </p>
                    <x-code :code="$exercise->variant($variant)" />
                </div>
            @endforeach
        </div>

        <section class="mt-6 rounded-xl border border-stone-200 bg-white p-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-semibold">{{ $exercise->is_correct ? 'Goed gekozen!' : 'Dit was niet de juiste variant.' }}</h2>
                <span class="rounded-full px-3 py-1 text-sm font-medium {{ $exercise->is_correct ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}" title="Een inschatting van de AI">Score van de AI: {{ $exercise->score }}/10</span>
            </div>
            <h3 class="mt-4 text-sm font-medium text-stone-500">Jouw uitleg</h3>
            <p class="mt-1 whitespace-pre-line">{{ $exercise->explanation }}</p>
            <h3 class="mt-4 text-sm font-medium text-stone-500">Feedback</h3>
            <p class="mt-1 whitespace-pre-line">{{ $exercise->feedback }}</p>
            <h3 class="mt-4 text-sm font-medium text-stone-500">Waarom</h3>
            <p class="mt-1 whitespace-pre-line">{{ $exercise->reference_explanation }}</p>
        </section>

        <section id="gesprek" class="mt-6">
            <h2 class="text-lg font-semibold">Praat erover door</h2>
            <p class="text-sm text-stone-500">Reageer op de feedback of stel een vraag over deze oefening.</p>
            <div class="mt-4 space-y-3">
                @foreach ($exercise->messages as $message)
                    @php($fromStudent = $message->role === \App\Enums\ExerciseMessageRole::Student)
                    <div class="flex {{ $fromStudent ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-2xl px-4 py-2 {{ $fromStudent ? 'bg-stone-900 text-white' : 'border border-stone-200 bg-white' }}">
                            <p class="whitespace-pre-line">{{ $message->content }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('exercises.messages.store', $exercise) }}" class="mt-4" data-ai="De AI schrijft een reactie…">
                @csrf
                <label for="content" class="sr-only">Jouw reactie</label>
                <textarea id="content" name="content" rows="3" required class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2" placeholder="Typ je reactie…">{{ old('content') }}</textarea>
                <x-field-error name="content" />
                <button type="submit" class="mt-2 rounded-lg bg-stone-900 px-4 py-2 font-medium text-white hover:bg-stone-700 disabled:cursor-wait disabled:opacity-60">Verstuur</button>
            </form>
        </section>

        <form method="POST" action="{{ route('exercises.store', $exercise->submission) }}" class="mt-8" data-ai="De AI maakt een vraag…">
            @csrf
            <button type="submit" class="rounded-lg border border-stone-900 px-4 py-2 font-medium hover:bg-stone-100 disabled:cursor-wait disabled:opacity-60">Volgende vraag over deze code</button>
        </form>
    @endif
</x-layout>
