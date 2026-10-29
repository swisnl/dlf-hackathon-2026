<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BadgeMedal from '@/Components/BadgeMedal.vue'

const props = defineProps({
    badges: { type: Array, required: true },
})

const stars = computed(() => usePage().props.auth.user.stars)
const maxThreshold = computed(() => props.badges.at(-1)?.threshold ?? 0)
const nextBadge = computed(() => props.badges.find(badge => badge.threshold > stars.value))

const starsTo = badge => {
    const count = Math.max(0, badge.threshold - stars.value)

    return `${count} ${count === 1 ? 'ster' : 'sterren'}`
}

const percentage = threshold => `${Math.min(threshold / maxThreshold.value, 1) * 100}%`

const awardedOn = date => new Date(date).toLocaleDateString('nl-NL', { day: 'numeric', month: 'long', timeZone: 'UTC' })
</script>

<template>
    <AppLayout title="Badges">
        <div>
            <h1 class="font-display text-[44px] leading-tight font-extrabold tracking-tight">Badges</h1>
            <p class="mt-1.5 text-lg text-ink-muted">Elke badge staat voor een aantal zelf verdiende sterren. Alleen jij ziet je voortgang.</p>
        </div>

        <div v-if="badges.length" class="flex flex-col gap-3.5 rounded-[20px] border border-line-soft bg-white p-6">
            <div class="flex flex-wrap justify-between gap-2">
                <span><b>{{ stars }} {{ stars === 1 ? 'ster' : 'sterren' }}</b> verdiend</span>
                <span class="text-ink-muted">
                    <template v-if="nextBadge">Nog {{ nextBadge.threshold - stars }} tot {{ nextBadge.name }}</template>
                    <template v-else>Alle badges behaald!</template>
                </span>
            </div>
            <div
                role="progressbar"
                aria-valuemin="0"
                :aria-valuemax="maxThreshold"
                :aria-valuenow="Math.min(stars, maxThreshold)"
                aria-label="Sterren op weg naar alle badges"
                class="h-[18px] overflow-hidden rounded-full bg-purple-100"
            >
                <div class="h-full rounded-full bg-purple-600" :style="{ width: percentage(stars) }" />
            </div>
            <div class="relative h-5 text-[13px] font-semibold text-ink-muted" aria-hidden="true">
                <span
                    v-for="badge in badges"
                    :key="badge.id"
                    class="absolute"
                    :class="badge.threshold === maxThreshold ? '-translate-x-full' : '-translate-x-1/2'"
                    :style="{ left: percentage(badge.threshold) }"
                >
                    {{ badge.threshold }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-[repeat(auto-fit,minmax(210px,1fr))] gap-5">
            <article
                v-for="badge in badges"
                :key="badge.id"
                class="flex flex-col items-center gap-2.5 rounded-[22px] bg-white text-center"
                :class="badge === nextBadge ? 'border-2 border-purple-600 px-[19px] py-[25px]' : 'border border-line-soft px-5 py-[26px]'"
            >
                <BadgeMedal :badge="badge" :lock="false" class="size-28 border-8" />
                <h2 class="mt-1 font-display text-[22px] font-extrabold">{{ badge.name }}</h2>
                <span class="text-[15px] text-ink-muted">{{ badge.threshold }} sterren</span>

                <span v-if="badge.earned" class="rounded-full bg-[#E1F4F2] px-3 py-1 text-[13px] font-bold text-[#075E5A]">
                    Behaald op {{ awardedOn(badge.awardedAt) }}
                </span>
                <span v-else-if="badge === nextBadge" class="rounded-full bg-purple-100 px-3 py-1 text-[13px] font-bold text-purple-800">
                    Volgende: nog {{ starsTo(badge) }}
                </span>
                <span v-else class="rounded-full bg-[#F1EEEA] px-3 py-1 text-[13px] font-bold text-ink-muted">
                    Nog {{ starsTo(badge) }}
                </span>
            </article>
        </div>
    </AppLayout>
</template>
