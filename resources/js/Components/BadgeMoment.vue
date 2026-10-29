<script setup>
import { computed, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import BadgeMedal from '@/Components/BadgeMedal.vue'

const page = usePage()

// New badges come one at a time, lowest first; marking one as seen brings up the next.
const badge = computed(() => page.props.newBadges?.[0] ?? null)
const dialog = ref(null)
const busy = ref(false)

// Wait a moment before the first badge, so the student sees the result that earned it.
const FIRST_DELAY = 1000

// The native modal dialog keeps focus inside, closes on Esc and returns focus afterwards.
watch(
    () => badge.value?.id,
    (id, previousId) => {
        if (dialog.value?.open) {
            dialog.value.close()
        }

        if (id !== undefined) {
            setTimeout(() => badge.value?.id === id && !dialog.value?.open && dialog.value?.showModal(), previousId === undefined ? FIRST_DELAY : 0)
        }
    },
    { immediate: true, flush: 'post' },
)

const dismiss = (then = null) => {
    if (busy.value || !badge.value) {
        return
    }

    busy.value = true
    router.post(`/badges/${badge.value.id}/seen`, { then }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => (busy.value = false),
    })
}

const ring = computed(() => ({ boxShadow: `0 0 0 10px color-mix(in srgb, ${badge.value.color} 8%, white)` }))

// The design's confetti: [x, y, colour, rectangle rotation or null for a dot].
const confetti = [
    [66, 50, '#9333EA', -20], [120, 90, '#FFB800', null], [436, 70, '#FFB800', 25], [470, 120, '#9333EA', null],
    [395, 28, '#0B7A75', -35], [90, 150, '#0B7A75', null], [165, 38, '#FF8A3D', 40], [360, 100, '#FF8A3D', null],
    [35, 108, '#FFB800', 60], [420, 170, '#FFB800', null],
]
</script>

<template>
    <dialog
        ref="dialog"
        aria-labelledby="badge-moment-title"
        class="m-auto w-[calc(100%-2rem)] max-w-[520px] overflow-hidden rounded-[28px] bg-white text-ink shadow-[0_24px_60px_rgba(29,26,47,0.35)] backdrop:bg-ink/60"
        @cancel.prevent="dismiss()"
    >
        <div v-if="badge" :key="badge.id" class="relative flex flex-col items-center gap-4 px-6 pt-12 pb-9 text-center sm:px-10">
            <svg viewBox="0 0 520 220" aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 h-auto w-full">
                <g
                    v-for="([x, y, color, rotation], index) in confetti"
                    :key="index"
                    class="motion-safe:animate-[badge-confetti_900ms_ease-out_both]"
                    :style="{ animationDelay: `${index * 40}ms` }"
                >
                    <rect v-if="rotation !== null" :x="x - 6" :y="y - 10" width="12" height="20" rx="3" :fill="color" :transform="`rotate(${rotation} ${x} ${y})`" />
                    <circle v-else :cx="x" :cy="y" r="6" :fill="color" />
                </g>
            </svg>

            <button
                type="button"
                class="absolute top-4 right-4 flex size-11 items-center justify-center rounded-full text-ink-muted hover:bg-paper"
                aria-label="Sluiten"
                :disabled="busy"
                @click="dismiss()"
            >
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>

            <BadgeMedal
                :badge="{ ...badge, earned: true }"
                class="relative size-[150px] border-[10px] motion-safe:animate-[badge-pop_600ms_cubic-bezier(0.34,1.56,0.64,1)_both]"
                :style="ring"
            />

            <span class="relative mt-2 text-sm font-bold tracking-[1.5px] text-purple-800 uppercase">Nieuwe badge</span>
            <h2 id="badge-moment-title" class="relative font-display text-4xl leading-[1.05] font-extrabold tracking-tight sm:text-[44px]">
                Je bent nu {{ badge.name }}!
            </h2>
            <p class="relative max-w-[400px] text-lg leading-normal text-ink-muted">
                {{ badge.threshold }} sterren, allemaal zelf verdiend.
                <template v-if="badge.next">De volgende badge is {{ badge.next.name }}, bij {{ badge.next.threshold }} sterren.</template>
                <template v-else>Je hebt nu alle badges!</template>
            </p>

            <div class="relative mt-3 flex flex-wrap justify-center gap-3">
                <button type="button" class="btn-primary" autofocus :disabled="busy" @click="dismiss('badges.index')">Bekijk je badges</button>
                <button type="button" class="btn-secondary" :disabled="busy" @click="dismiss('exercises.index')">Verder oefenen</button>
            </div>
        </div>
    </dialog>
</template>
