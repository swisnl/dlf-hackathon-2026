<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BadgeMedal from '@/Components/BadgeMedal.vue'
import Owl from '@/Components/Owl.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import StarIcon from '@/Components/StarIcon.vue'
import StatusChip from '@/Components/StatusChip.vue'
import TopicChip from '@/Components/TopicChip.vue'

const props = defineProps({
    badges: { type: Array, required: true },
    nextBadge: { type: Object, default: null },
    exercisesDone: { type: Number, required: true },
    exercisesTotal: { type: Number, required: true },
    suggestions: { type: Array, required: true },
    nextExerciseId: { type: Number, default: null },
})

const user = computed(() => usePage().props.auth.user)
const firstName = computed(() => user.value.name.trim().split(/\s+/)[0])
const earnedBadges = computed(() => props.badges.filter(badge => badge.earned).length)
const starsToGo = computed(() => props.nextBadge.threshold - user.value.stars)

const tips = [
    'Lees eerst beide classes helemaal door voordat je klikt. Wat zou jij anders hebben geschreven?',
    'Twijfel je over een regel? Vraag je af wat er gebeurt als iemand die code aanroept met rare invoer.',
    'Een fout zit vaak in wat er net níet staat: een ontbrekende check, relatie of validatieregel.',
    'Fout is gewoon informatie. Bij een herkansing krijg je een hint, dus probeer het gerust.',
]
const tip = tips[Math.floor(Math.random() * tips.length)]
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-[44px] leading-tight font-extrabold tracking-tight">Hoi {{ firstName }}!</h1>
                <p class="mt-1.5 text-lg text-ink-muted">
                    <template v-if="nextBadge">
                        Nog {{ starsToGo }} {{ starsToGo === 1 ? 'ster' : 'sterren' }} en je bent {{ nextBadge.name }}.
                    </template>
                    <template v-else>Je hebt alle badges verdiend.</template>
                    Zin in een oefening?
                </p>
            </div>

            <PrimaryButton :href="nextExerciseId ? `/exercises/${nextExerciseId}` : '/oefeningen'">Start een oefening</PrimaryButton>
        </div>

        <div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-5">
            <div class="flex items-center gap-[18px] rounded-[20px] border border-line-soft bg-white px-6 py-[22px]">
                <div class="flex size-[60px] items-center justify-center rounded-2xl bg-star-soft">
                    <StarIcon class="size-[34px]" />
                </div>
                <div>
                    <div class="font-display text-[40px] leading-none font-extrabold">{{ user.stars }}</div>
                    <div class="mt-1 text-[15px] text-ink-muted">sterren verdiend</div>
                </div>
            </div>

            <div class="flex items-center gap-[18px] rounded-[20px] border border-line-soft bg-white px-6 py-[22px]">
                <div class="flex size-[60px] items-center justify-center rounded-2xl bg-purple-100">
                    <svg class="size-[30px]" viewBox="0 0 24 24" fill="none" stroke="#7E22CE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="9" r="6" />
                        <path d="M8.5 14l-1.5 7 5-3 5 3-1.5-7" />
                    </svg>
                </div>
                <div>
                    <div class="font-display text-[40px] leading-none font-extrabold">
                        {{ earnedBadges }} <span class="text-[22px] text-[#6B6680]">/ {{ badges.length }}</span>
                    </div>
                    <div class="mt-1 text-[15px] text-ink-muted">badges behaald</div>
                </div>
            </div>

            <div class="flex items-center gap-[18px] rounded-[20px] border border-line-soft bg-white px-6 py-[22px]">
                <div class="flex size-[60px] items-center justify-center rounded-2xl bg-[#E1F4F2]">
                    <svg class="size-[30px]" viewBox="0 0 24 24" fill="none" stroke="#0B7A75" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12l5 5 9-10" />
                    </svg>
                </div>
                <div>
                    <div class="font-display text-[40px] leading-none font-extrabold">
                        {{ exercisesDone }} <span class="text-[22px] text-[#6B6680]">/ {{ exercisesTotal }}</span>
                    </div>
                    <div class="mt-1 text-[15px] text-ink-muted">oefeningen gemaakt</div>
                </div>
            </div>
        </div>

        <div v-if="nextBadge" class="flex flex-col gap-3 rounded-[20px] border border-line-soft bg-white px-6 py-[22px]">
            <div class="flex flex-wrap justify-between gap-2">
                <span><b>Volgende badge: {{ nextBadge.name }}</b> bij {{ nextBadge.threshold }} sterren</span>
                <span class="text-ink-muted">{{ user.stars }} / {{ nextBadge.threshold }}</span>
            </div>
            <div
                role="progressbar"
                aria-valuemin="0"
                :aria-valuemax="nextBadge.threshold"
                :aria-valuenow="user.stars"
                :aria-label="`Voortgang naar badge ${nextBadge.name}`"
                class="h-[18px] overflow-hidden rounded-full bg-purple-100"
            >
                <div class="h-full rounded-full bg-purple-600" :style="{ width: `${(user.stars / nextBadge.threshold) * 100}%` }" />
            </div>
        </div>

        <div class="flex flex-wrap items-start gap-6">
            <section class="flex min-w-0 flex-[999_1_560px] flex-col gap-3.5">
                <div class="flex items-baseline justify-between">
                    <h2 class="font-display text-[26px] font-extrabold">Verder oefenen</h2>
                    <Link href="/oefeningen" class="font-semibold text-purple-700 hover:text-purple-800">Alle oefeningen</Link>
                </div>

                <Link
                    v-for="exercise in suggestions"
                    :key="exercise.id"
                    :href="`/exercises/${exercise.id}`"
                    class="flex flex-wrap items-center gap-x-[18px] gap-y-3 rounded-2xl border border-line-soft bg-white px-5 py-[18px] hover:border-purple-600 focus-visible:outline-3 focus-visible:outline-purple-600"
                >
                    <div class="flex min-w-0 flex-[1_1_260px] flex-col gap-1.5">
                        <b class="text-[17px]">{{ exercise.title }}</b>
                        <div class="flex flex-wrap gap-2">
                            <TopicChip :topic="exercise.topic" />
                            <StatusChip :status="exercise.status" :label="exercise.statusLabel" />
                        </div>
                    </div>

                    <div class="ml-auto flex items-center gap-[18px]">
                        <span class="inline-flex items-center gap-1.5 font-bold text-ink-muted">
                            <StarIcon class="size-5" />
                            <template v-if="exercise.status === 'retry'">
                                <span aria-hidden="true">{{ exercise.bestStars }} / {{ exercise.stars }}</span>
                                <span class="sr-only">{{ exercise.bestStars }} van {{ exercise.stars }} sterren verdiend</span>
                            </template>
                            <template v-else>
                                <span aria-hidden="true">{{ exercise.stars }}</span>
                                <span class="sr-only">{{ exercise.stars }} sterren te verdienen</span>
                            </template>
                        </span>

                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="#6B21A8" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </div>
                </Link>

                <p v-if="!suggestions.length" class="rounded-2xl border border-line-soft bg-white px-5 py-[18px] text-ink-muted">
                    <template v-if="exercisesTotal">Je hebt alle oefeningen gemaakt. Knap! In het overzicht kun je ze opnieuw oefenen.</template>
                    <template v-else>Er staan nog geen oefeningen klaar. Kom snel terug!</template>
                </p>
            </section>

            <aside class="flex min-w-0 flex-[1_1_320px] flex-col gap-4">
                <div class="flex flex-col gap-4 rounded-[20px] border border-line-soft bg-white p-[22px]">
                    <div class="flex items-baseline justify-between">
                        <h2 class="font-display text-[22px] font-extrabold">Jouw badges</h2>
                        <Link href="/badges" class="text-[15px] font-semibold text-purple-700 hover:text-purple-800">Bekijk</Link>
                    </div>
                    <div class="flex justify-between gap-2.5">
                        <BadgeMedal v-for="badge in badges" :key="badge.id" :badge="badge" class="size-[52px] border-4" />
                    </div>
                </div>

                <div class="flex items-start gap-3.5 rounded-[20px] bg-ink p-[22px] text-white">
                    <Owl class="size-11 shrink-0" />
                    <div class="flex flex-col gap-1.5">
                        <b>Tip van de uil</b>
                        <p class="text-[15px] leading-normal text-[#E9E5F5]">{{ tip }}</p>
                    </div>
                </div>
            </aside>
        </div>
    </AppLayout>
</template>
