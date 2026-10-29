<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import Owl from '@/Components/Owl.vue'
import StarIcon from '@/Components/StarIcon.vue'

defineProps({
    title: { type: String, required: true },
    // Where the student is in the exercise, such as "Poging 1 van 2".
    status: { type: String, required: true },
})

const page = usePage()
const user = computed(() => page.props.auth.user)
</script>

<template>
    <Head :title="title" />

    <!-- A focused header without the menu: the student is in the middle of an exercise. -->
    <header class="border-b border-line-soft bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3.5 sm:px-6">
            <Link href="/" aria-label="Eigenwijs, naar dashboard" class="flex items-center gap-2.5">
                <Owl class="size-9" />
                <span class="font-display text-2xl font-extrabold tracking-tight">eigenwijs</span>
            </Link>

            <span class="font-semibold text-ink-muted">{{ status }}</span>

            <div class="ml-auto flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-star-soft px-3.5 py-2 font-bold">
                    <StarIcon class="size-5" />
                    {{ user.stars }}
                    <span class="sr-only">sterren</span>
                </span>

                <Link
                    href="/oefeningen"
                    aria-label="Oefening sluiten"
                    class="inline-flex items-center gap-1.5 rounded-[10px] px-3 py-2.5 font-semibold text-ink-muted hover:bg-paper hover:text-ink focus-visible:outline-3 focus-visible:outline-purple-600"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg>
                    Sluiten
                </Link>
            </div>
        </div>
    </header>

    <main class="mx-auto flex max-w-7xl flex-col gap-[22px] px-4 pt-8 pb-16 sm:px-6">
        <p v-if="page.props.flash?.error" role="alert" class="rounded-[14px] bg-missed-light px-4 py-3 text-[15px] text-missed-text">{{ page.props.flash.error }}</p>
        <slot />
    </main>
</template>
