<script setup>
import { Head, Link } from '@inertiajs/vue3'
import Owl from '@/Components/Owl.vue'
</script>

<template>
    <Head title="Privacyverklaring" />

    <div class="min-h-screen px-4 py-10 sm:px-6 lg:py-14">
        <article class="mx-auto flex max-w-[680px] flex-col gap-8 text-[17px] leading-relaxed [&_h2]:font-display [&_h2]:text-2xl [&_h2]:font-extrabold [&_section]:flex [&_section]:flex-col [&_section]:gap-3">
            <Link href="/login" class="flex items-center gap-3 self-start">
                <Owl class="size-10" />
                <span class="font-display text-3xl font-extrabold tracking-tight">eigenwijs</span>
            </Link>

            <header class="flex flex-col gap-3">
                <h1 class="font-display text-[34px] leading-tight font-extrabold">Privacyverklaring</h1>
                <p class="text-lg text-ink-muted">Wat Eigenwijs van je bewaart, waarom, en hoe je het laat verwijderen.</p>
            </header>

            <section>
                <h2>Wie is verantwoordelijk?</h2>
                <p>Je school biedt Eigenwijs aan en is verantwoordelijk voor je gegevens. Heb je een vraag over je gegevens? Stel die aan je docent.</p>
            </section>

            <section>
                <h2>Welke gegevens bewaren we?</h2>
                <ul class="flex list-disc flex-col gap-1.5 ps-5">
                    <li>Je naam en e-mailadres.</li>
                    <li>Je wachtwoord, versleuteld. Niemand kan het teruglezen, ook je docent niet.</li>
                    <li>Je oefenresultaten: je pogingen, de regels die je aanklikte, je sterren en je badges.</li>
                </ul>
                <p>Om je ingelogd te houden, bewaren we ook een inlogsessie. Daarin staat niet je IP-adres of welke browser je gebruikt. Vraag je een nieuw wachtwoord aan, dan bewaren we de code uit de e-mail 60 minuten.</p>
                <p>Meer bewaren we niet. We gebruiken geen tracking, geen advertenties en geen diensten van andere bedrijven die je gegevens krijgen.</p>
            </section>

            <section>
                <h2>Waarvoor gebruiken we ze?</h2>
                <p>Om je te laten inloggen, je oefeningen na te kijken en je voortgang bij te houden. Je docent gebruikt je voortgang om je te helpen.</p>
            </section>

            <section>
                <h2>Wie ziet je gegevens?</h2>
                <p>Jij, je docenten en de beheerders van Eigenwijs op je school. Andere studenten zien niets van jou: er is geen ranglijst.</p>
            </section>

            <section>
                <h2>Hoe lang bewaren we ze?</h2>
                <p>Zolang je op school zit en Eigenwijs gebruikt. Ga je van school, bijvoorbeeld na je diploma, dan verwijdert de school je account.</p>
            </section>

            <section>
                <h2>Je account laten verwijderen</h2>
                <p>Vraag het aan je docent. Als je account wordt verwijderd, gaan je pogingen, voortgang, badges en inlogsessies meteen mee. Er blijft niets van je achter.</p>
            </section>

            <Link href="/login" class="self-start font-semibold text-purple-700 hover:text-purple-800">Terug naar inloggen</Link>
        </article>
    </div>
</template>
