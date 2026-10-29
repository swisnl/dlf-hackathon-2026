<script setup>
import { Head } from '@inertiajs/vue3'
import Owl from '@/Components/Owl.vue'

defineProps({
    title: { type: String, required: true },
})

const benefits = [
    'Eloquent, routes, validatie, migrations en policies',
    'Fout is geen ramp: je krijgt een hint en een tweede kans',
    'Spaar sterren en verdien vijf badges',
]
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen flex-col lg:flex-row">
        <section class="flex flex-col justify-between gap-8 bg-purple-600 px-4 py-8 text-white sm:px-10 lg:w-1/2 lg:gap-12 lg:px-16 lg:py-14">
            <div class="flex items-center gap-3">
                <Owl variant="inverted" class="size-10" />
                <span class="font-display text-3xl font-extrabold tracking-tight">eigenwijs</span>
            </div>

            <div class="flex max-w-[520px] flex-col gap-5">
                <p class="font-display text-4xl leading-none font-extrabold tracking-tight lg:text-6xl">
                    Zelf nadenken.<br>Zelf snappen.
                </p>
                <p class="hidden text-lg leading-relaxed text-purple-100 lg:block">
                    Twee Laravel-classes. Eén klopt, in de ander zitten fouten. Vind ze zelf en verdien sterren.
                </p>
                <ul class="hidden flex-col gap-3 text-[17px] lg:flex">
                    <li v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3">
                        <svg class="size-[22px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="#FFB800" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12l5 5 9-10" />
                        </svg>
                        {{ benefit }}
                    </li>
                </ul>
            </div>

            <Owl variant="bare" class="hidden size-[180px] self-end lg:block" />
        </section>

        <main class="flex flex-1 items-center justify-center px-4 py-10 sm:px-6 lg:py-14">
            <div class="flex w-full max-w-[420px] flex-col gap-5">
                <slot />
            </div>
        </main>
    </div>
</template>
