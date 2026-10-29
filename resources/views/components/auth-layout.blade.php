@props(['title', 'active'])
<!DOCTYPE html>
<html lang="nl">
<head>
    <x-head :title="$title" />
</head>
<body class="min-h-screen bg-papier font-sans text-inkt antialiased">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <section class="flex flex-col justify-between gap-8 bg-paars px-6 py-8 text-white sm:px-12 lg:w-1/2 lg:px-16 lg:py-14">
            <div class="flex items-center gap-3">
                <x-owl :size="40" tile="#FFFFFF" eyes="#9333EA" />
                <span class="font-display text-3xl font-extrabold tracking-tight">eigenwijs</span>
            </div>
            <div class="flex max-w-lg flex-col gap-5">
                <h1 class="font-display text-4xl leading-none font-extrabold tracking-tight sm:text-6xl">Zelf nadenken.<br>Zelf snappen.</h1>
                <p class="text-lg leading-relaxed text-paars-licht">Upload je eigen code. De AI maakt er twee versies van: één klopt. Kies de goede en leg uit waarom.</p>
                <ul class="hidden flex-col gap-3 text-[17px] sm:flex">
                    @foreach (['Vragen over code die je zelf schreef', 'Fout is geen ramp: je krijgt uitleg en kunt doorvragen', 'Het gaat om het waarom, niet alleen om het goede antwoord'] as $point)
                        <li class="flex items-center gap-3">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FFB800" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0"><path d="M5 12l5 5 9-10"></path></svg>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <x-owl :size="180" :tile="null" class="hidden self-end lg:block" />
        </section>

        <section class="flex flex-1 items-center justify-center px-4 py-10 sm:px-6 lg:py-14">
            <div class="flex w-full max-w-[420px] flex-col gap-5">
                <nav aria-label="Inloggen of registreren" class="flex rounded-[14px] bg-paars-licht p-1">
                    @foreach (['login' => 'Inloggen', 'register' => 'Registreren'] as $route => $label)
                        <a href="{{ route($route) }}" @class([
                            'flex-1 rounded-[11px] p-3 text-center',
                            'bg-white font-bold text-inkt shadow-[0_1px_3px_rgba(29,26,47,0.15)]' => $active === $route,
                            'font-semibold text-paars-diep' => $active !== $route,
                        ]) @if ($active === $route) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
                {{ $slot }}
            </div>
        </section>
    </div>
</body>
</html>
