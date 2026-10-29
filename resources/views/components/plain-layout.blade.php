{{-- Neutral layout for the pages that do not use the Eigenwijs house style yet (login, registration). --}}
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-[Instrument_Sans,ui-sans-serif,system-ui,sans-serif] text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4">
            <a href="{{ url('/') }}" class="font-semibold tracking-tight">Code-overhoring</a>
            @auth
                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('submissions.index') }}" class="text-stone-600 hover:text-stone-900">Mijn code</a>
                    <span class="hidden text-stone-400 sm:inline">{{ auth()->user()?->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-stone-600 hover:text-stone-900">Uitloggen</button>
                    </form>
                </nav>
            @endauth
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('error') }}</div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
