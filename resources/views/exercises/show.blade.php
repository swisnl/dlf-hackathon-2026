@php($variants = \App\Enums\Variant::cases())
<x-layout :title="$exercise->topic->name">
    <a href="{{ route('submissions.show', $exercise->submission) }}" class="link text-sm">&larr; {{ $exercise->submission->title }}</a>
    <div class="mt-3 flex flex-col gap-2.5">
        <div class="flex flex-wrap gap-2">
            <span class="chip bg-paars-licht text-paars-diep">{{ $exercise->topic->name }}</span>
            @if ($attempt)
                <span class="chip bg-gevonden-licht text-[#075E5A]">Gemaakt</span>
            @endif
        </div>
        <h1 class="font-display text-3xl font-extrabold tracking-tight sm:text-[40px] sm:leading-tight">{{ $exercise->title }}</h1>
        @unless ($attempt)
            <p class="max-w-3xl text-lg text-inkt-zacht">Eén van deze twee versies klopt. Kies de goede en leg uit <b class="text-inkt">waarom</b>.</p>
        @endunless
    </div>

    @if (! $attempt)
        <form method="POST" action="{{ route('exercises.answer', $exercise) }}" class="mt-6" data-ai="De AI bekijkt je antwoord…">
            @csrf
            <fieldset>
                <legend class="sr-only">Welke variant is juist?</legend>
                <div class="grid gap-5 md:grid-cols-2">
                    @foreach ($variants as $variant)
                        <label class="card block min-w-0 cursor-pointer overflow-hidden border-2 has-[:checked]:border-paars has-[:checked]:ring-[3px] has-[:checked]:ring-paars-licht has-[:focus-visible]:ring-[3px] has-[:focus-visible]:ring-paars-licht">
                            <span class="flex items-center gap-2.5 border-b border-rand px-4 py-3.5">
                                <input type="radio" name="choice" value="{{ $variant->value }}" required @checked(old('choice') === $variant->value) class="size-[18px] accent-paars">
                                <b class="font-display text-xl">{{ $variant->label() }}</b>
                            </span>
                            <x-code :code="$exercise->code($variant)" class="m-3" />
                        </label>
                    @endforeach
                </div>
                <x-field-error name="choice" class="mt-3" />
            </fieldset>

            <div class="card mt-5 flex flex-col gap-2 p-5 sm:p-6">
                <label for="explanation" class="font-display text-xl font-extrabold">Leg uit waarom jouw keuze juist is</label>
                <p class="text-inkt-zacht">Dit is het belangrijkste deel: laat zien dat je snapt wat er gebeurt.</p>
                <textarea id="explanation" name="explanation" rows="5" required minlength="10" class="field mt-1">{{ old('explanation') }}</textarea>
                <x-field-error name="explanation" />
                <div class="mt-2">
                    <button type="submit" class="btn-primary">Inleveren</button>
                </div>
            </div>
        </form>
    @else
        <div class="mt-6 grid gap-5 md:grid-cols-2">
            @foreach ($variants as $variant)
                @php($isCorrect = $exercise->side($variant) === \App\Enums\CodeSide::Correct)
                <section @class(['card min-w-0 overflow-hidden border-2', 'border-gevonden' => $isCorrect])>
                    <div class="flex flex-wrap items-center gap-2 border-b border-rand px-4 py-3.5">
                        <b class="font-display text-xl">{{ $variant->label() }}</b>
                        @if ($isCorrect)
                            <span class="chip bg-gevonden-licht text-[#075E5A]">Juist</span>
                        @endif
                        @if ($attempt->chosen_side === $exercise->side($variant))
                            <span class="chip bg-paars-licht text-paars-diep">Jouw keuze</span>
                        @endif
                    </div>
                    <x-code :code="$exercise->code($variant)" class="m-3" />
                </section>
            @endforeach
        </div>

        <section class="card mt-5 flex flex-col gap-5 p-5 sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-[28px] font-extrabold">{{ $attempt->chosen_side === \App\Enums\CodeSide::Correct ? 'Scherp gezien!' : 'Bijna! De andere versie klopt.' }}</h2>
                <span @class(['chip', 'bg-gevonden-licht text-[#075E5A]' => $attempt->stars > 0, 'bg-gemist-licht text-gemist-tekst' => $attempt->stars === 0]) title="Een inschatting van de AI">{{ $attempt->stars }} van {{ $exercise->stars }} sterren</span>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-inkt-zacht">Jouw uitleg</h3>
                <p class="mt-1 whitespace-pre-line">{{ $attempt->explanation }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-inkt-zacht">Feedback</h3>
                <p class="mt-1 whitespace-pre-line">{{ $attempt->feedback }}</p>
            </div>
            <div class="flex items-start gap-3 rounded-[14px] bg-ster-licht px-4 py-3.5">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8A6400" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mt-0.5 shrink-0"><path d="M9 18h6M10 21h4"></path><path d="M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z"></path></svg>
                <div class="leading-relaxed">
                    <b>Waarom:</b>
                    @foreach ($exercise->errors as $error)
                        <p class="whitespace-pre-line"><span class="font-semibold">{{ $error->title }} (regel {{ $error->line }} van {{ $exercise->variant(\App\Enums\CodeSide::Wrong)->label() }}).</span> {{ $error->explanation }}</p>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="gesprek" class="card mt-5 p-5 sm:p-7">
            <h2 class="font-display text-2xl font-extrabold">Praat erover door</h2>
            <p class="text-inkt-zacht">Reageer op de feedback of stel een vraag over deze oefening.</p>
            <div class="mt-5 flex flex-col gap-3">
                @foreach ($attempt->messages as $message)
                    @if ($message->role === \App\Enums\AttemptMessageRole::Student)
                        <div class="flex justify-end">
                            <p class="max-w-[85%] rounded-2xl rounded-br-md bg-paars px-4 py-2.5 whitespace-pre-line text-white">{{ $message->content }}</p>
                        </div>
                    @else
                        <div class="flex items-end gap-2.5">
                            <x-owl :size="32" class="shrink-0" />
                            <p class="max-w-[85%] rounded-2xl rounded-bl-md bg-papier px-4 py-2.5 whitespace-pre-line">{{ $message->content }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
            <form method="POST" action="{{ route('exercises.messages.store', $exercise) }}" class="mt-5 flex flex-col gap-3" data-ai="De AI schrijft een reactie…">
                @csrf
                <label for="content" class="sr-only">Jouw reactie</label>
                <textarea id="content" name="content" rows="3" required class="field" placeholder="Typ je reactie…">{{ old('content') }}</textarea>
                <x-field-error name="content" />
                <div>
                    <button type="submit" class="btn-primary">Verstuur</button>
                </div>
            </form>
        </section>

        <form method="POST" action="{{ route('exercises.store', $exercise->submission) }}" class="mt-8" data-ai="De AI maakt een vraag…">
            @csrf
            <button type="submit" class="btn-secondary">Volgende vraag over deze code</button>
        </form>
    @endif
</x-layout>
