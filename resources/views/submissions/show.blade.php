<x-layout :title="$submission->title">
    <a href="{{ route('submissions.index') }}" class="text-sm text-stone-600 hover:text-stone-900">&larr; Mijn code</a>
    <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $submission->title }}</h1>
            <p class="text-stone-600">{{ $submission->filename ?? 'Geplakte code' }}</p>
        </div>
        <form method="POST" action="{{ route('exercises.store', $submission) }}" data-ai="De AI maakt een vraag…">
            @csrf
            <button type="submit" class="rounded-lg bg-stone-900 px-4 py-2 font-medium text-white hover:bg-stone-700 disabled:cursor-wait disabled:opacity-60">Stel me een nieuwe vraag</button>
        </form>
    </div>

    <h2 class="mt-8 text-lg font-semibold">Vragen</h2>
    @forelse ($exercises as $exercise)
        <a href="{{ route('exercises.show', $exercise) }}" class="mt-3 flex items-center justify-between gap-4 rounded-xl border border-stone-200 bg-white p-4 hover:border-stone-400">
            <span>
                <span class="block font-medium">{{ $exercise->concept }}</span>
                <span class="block text-sm text-stone-500">{{ $exercise->question }}</span>
            </span>
            @if ($exercise->isAnswered())
                <span class="shrink-0 rounded-full px-3 py-1 text-sm {{ $exercise->is_correct ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $exercise->score }}/10</span>
            @else
                <span class="shrink-0 rounded-full bg-stone-100 px-3 py-1 text-sm text-stone-600">Open</span>
            @endif
        </a>
    @empty
        <p class="mt-3 text-sm text-stone-500">Nog geen vragen. Klik op de knop om er een te krijgen.</p>
    @endforelse

    <details class="mt-8">
        <summary class="cursor-pointer text-sm font-medium text-stone-600">Bekijk je code</summary>
        <x-code :code="$submission->code" class="mt-3" />
    </details>
</x-layout>
