<x-layout title="Mijn code">
    <div class="grid gap-8 lg:grid-cols-5">
        <section class="lg:col-span-3">
            <h1 class="text-2xl font-semibold">Upload je code</h1>
            <p class="mt-1 text-stone-600">De AI stelt je vragen over jouw code. Kies steeds welke variant juist is en leg uit waarom.</p>

            <form method="POST" action="{{ route('submissions.store') }}" enctype="multipart/form-data" data-ai="De AI maakt een vraag…" class="mt-6 space-y-4 rounded-xl border border-stone-200 bg-white p-6">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium">Waar gaat je code over?</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required placeholder="Bijvoorbeeld: opdracht 3, winkelmandje" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2">
                    <x-field-error name="title" />
                </div>
                <div>
                    <label for="file" class="block text-sm font-medium">Bestand</label>
                    <input id="file" name="file" type="file" accept="{{ collect($extensions)->map(fn ($e) => '.'.$e)->implode(',') }}" class="mt-1 block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-stone-200 file:px-3 file:py-2">
                    <p class="mt-1 text-xs text-stone-500">Maximaal 200 KB. Bijvoorbeeld .php, .js, .py of .java.</p>
                    <x-field-error name="file" />
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium">Of plak je code hier</label>
                    <textarea id="code" name="code" rows="10" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 font-mono text-sm">{{ old('code') }}</textarea>
                    <x-field-error name="code" />
                </div>
                <button type="submit" class="rounded-lg bg-stone-900 px-4 py-2 font-medium text-white hover:bg-stone-700 disabled:cursor-wait disabled:opacity-60">Upload en start de overhoring</button>
                <p class="text-xs text-stone-500">De AI heeft even tijd nodig om een vraag te maken (soms een minuut).</p>
            </form>
        </section>

        <section class="lg:col-span-2">
            <h2 class="text-lg font-semibold">Eerder geüpload</h2>
            @forelse ($submissions as $submission)
                <a href="{{ route('submissions.show', $submission) }}" class="mt-3 block rounded-xl border border-stone-200 bg-white p-4 hover:border-stone-400">
                    <span class="block font-medium">{{ $submission->title }}</span>
                    <span class="block text-sm text-stone-500">
                        {{ $submission->filename ?? 'Geplakte code' }} · {{ $submission->exercises_count }} {{ $submission->exercises_count === 1 ? 'vraag' : 'vragen' }} · {{ $submission->created_at?->format('d-m-Y') }}
                    </span>
                </a>
            @empty
                <p class="mt-3 text-sm text-stone-500">Je hebt nog geen code geüpload.</p>
            @endforelse
        </section>
    </div>
</x-layout>
