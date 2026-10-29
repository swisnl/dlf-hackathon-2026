<x-layout :title="$submission->title">
    <a href="{{ route('submissions.index') }}" class="link text-sm">&larr; Mijn code</a>
    <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-extrabold tracking-tight">{{ $submission->title }}</h1>
            <p class="mt-1 font-mono text-sm text-inkt-zacht">{{ $submission->filename ?? 'Geplakte code' }}</p>
        </div>
        <form method="POST" action="{{ route('exercises.store', $submission) }}" data-ai="De AI maakt een vraag…">
            @csrf
            <button type="submit" class="btn-primary">Stel me een nieuwe vraag</button>
        </form>
    </div>

    <h2 class="mt-9 font-display text-2xl font-extrabold">Vragen</h2>
    @forelse ($exercises as $exercise)
        <a href="{{ route('exercises.show', $exercise) }}" class="card mt-3 flex flex-wrap items-center justify-between gap-3 p-5 transition-colors hover:border-paars">
            <span class="min-w-0">
                <span class="block text-lg font-bold">{{ $exercise->topic->name }}</span>
                <span class="block text-inkt-zacht">{{ $exercise->title }}</span>
            </span>
            @if ($attempt = $exercise->attempts->first())
                <span class="chip shrink-0 bg-gevonden-licht text-[#075E5A]">Gemaakt · {{ $attempt->stars }} van {{ $exercise->stars }} sterren</span>
            @else
                <span class="chip shrink-0 bg-paars-licht text-paars-diep">Nieuw</span>
            @endif
        </a>
    @empty
        <p class="card mt-3 p-5 text-inkt-zacht">Nog geen vragen. Klik op de knop en de AI maakt er een.</p>
    @endforelse

    <details class="card mt-8 p-5">
        <summary class="cursor-pointer font-semibold">Bekijk je code</summary>
        <x-code :code="$submission->code" class="mt-4" />
    </details>
</x-layout>
