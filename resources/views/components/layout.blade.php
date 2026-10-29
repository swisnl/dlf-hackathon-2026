@props(['title' => null])
<!DOCTYPE html>
<html lang="nl">
<head>
    <x-head :title="$title" />
</head>
<body class="min-h-screen bg-papier font-sans text-inkt antialiased">
    <header class="border-b border-rand bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-4 gap-y-3 px-4 sm:gap-x-7 py-3.5 sm:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 text-inkt" aria-label="Eigenwijs, naar mijn code">
                <x-owl :size="36" />
                <span class="font-display text-xl font-extrabold tracking-tight sm:text-2xl">eigenwijs</span>
            </a>
            @auth
                <nav aria-label="Hoofdmenu" class="flex gap-1">
                    <a href="{{ route('submissions.index') }}" @class([
                        'rounded-[10px] px-3.5 py-2.5',
                        'bg-paars-licht font-bold text-paars-diep' => request()->routeIs('submissions.*', 'exercises.*'),
                        'font-semibold text-inkt-zacht hover:text-inkt' => ! request()->routeIs('submissions.*', 'exercises.*'),
                    ]) @if (request()->routeIs('submissions.*', 'exercises.*')) aria-current="page" @endif>Mijn code</a>
                </nav>
                <div class="ml-auto flex items-center gap-3">
                    <span class="hidden size-11 items-center sm:flex justify-center rounded-full bg-inkt font-bold text-white" title="{{ auth()->user()?->name }}" aria-hidden="true">{{ Str::of(auth()->user()?->name ?? '')->explode(' ')->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') }}</span>
                    <span class="sr-only">Ingelogd als {{ auth()->user()?->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="font-semibold text-inkt-zacht underline-offset-4 hover:text-inkt hover:underline">Uitloggen</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 pt-8 pb-16 sm:px-6 sm:pt-10">
        @if (session('error'))
            <div class="mb-6 rounded-[14px] bg-gemist-licht px-4 py-3 text-[15px] text-gemist-tekst" role="alert">{{ session('error') }}</div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
