<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AiChip from '@/Components/AiChip.vue'
import StarIcon from '@/Components/StarIcon.vue'
import StatusChip from '@/Components/StatusChip.vue'
import TopicChip from '@/Components/TopicChip.vue'

const props = defineProps({
    topics: { type: Array, required: true },
    statuses: { type: Array, required: true },
    sources: { type: Array, required: true },
    filters: { type: Object, required: true },
    exercises: { type: Array, required: true },
})

const topicFilters = computed(() => [{ label: 'Alle', value: null }, ...props.topics.map(name => ({ label: name, value: name }))])

const visitOptions = { preserveState: true, preserveScroll: true }

// The filters live in the URL, so they survive a refresh and can be shared.
const filterUrl = changes => {
    const params = new URLSearchParams(Object.entries({ ...props.filters, ...changes }).filter(([, value]) => value))
    const query = params.toString()

    return query ? `/exercises?${query}` : '/exercises'
}

const filterBy = (name, event) => router.visit(filterUrl({ [name]: event.target.value }), visitOptions)

const actions = {
    new: { label: 'Start', class: 'bg-purple-600 px-4 text-white shadow-[0_3px_0_var(--color-purple-800)] hover:bg-purple-800' },
    retry: { label: 'Herkansen', class: 'border-2 border-ink bg-white px-4 shadow-[0_3px_0_var(--color-ink)] hover:bg-paper' },
    done: { label: 'Oefen opnieuw', class: 'px-1 text-purple-800 hover:underline' },
}
</script>

<template>
    <AppLayout title="Oefeningen">
        <div>
            <h1 class="font-display text-[44px] leading-tight font-extrabold tracking-tight">Oefeningen</h1>
            <p class="mt-1.5 text-lg text-ink-muted">Kies er zelf een. Elke oefening: twee classes, één bevat fouten.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <nav aria-label="Filter op onderwerp" class="flex flex-wrap gap-2">
                <Link
                    v-for="topic in topicFilters"
                    :key="topic.label"
                    :href="filterUrl({ topic: topic.value })"
                    v-bind="visitOptions"
                    :aria-current="filters.topic === topic.value ? 'true' : undefined"
                    class="rounded-full border-2 px-4 py-[7px] text-[15px] font-semibold focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-purple-600"
                    :class="filters.topic === topic.value ? 'border-ink bg-ink text-white' : 'border-line bg-white hover:border-ink'"
                    >
                    {{ topic.label }}
                </Link>
            </nav>

            <div class="ml-auto flex flex-wrap items-center gap-x-5 gap-y-3">
                <div class="flex items-center gap-2.5">
                    <label for="source" class="text-[15px] font-semibold">Gemaakt door</label>
                    <select
                        id="source"
                        :value="filters.source ?? ''"
                        class="rounded-xl border-2 border-line bg-white px-3 py-2.5 text-[15px] outline-purple-100 focus:border-purple-600 focus:outline-3"
                        @change="filterBy('source', $event)"
                    >
                        <option value="">Iedereen</option>
                        <option v-for="source in sources" :key="source.value" :value="source.value">{{ source.label }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-2.5">
                    <label for="status" class="text-[15px] font-semibold">Status</label>
                    <select
                        id="status"
                        :value="filters.status ?? ''"
                        class="rounded-xl border-2 border-line bg-white px-3 py-2.5 text-[15px] outline-purple-100 focus:border-purple-600 focus:outline-3"
                        @change="filterBy('status', $event)"
                    >
                        <option value="">Alle</option>
                        <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div v-if="exercises.length" class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-5">
            <article
                v-for="exercise in exercises"
                :key="exercise.id"
                class="flex min-h-[196px] flex-col gap-3.5 rounded-[20px] border border-line-soft bg-white p-[22px]"
            >
                <div class="flex flex-wrap justify-between gap-2">
                    <div class="flex flex-wrap gap-2">
                        <TopicChip :topic="exercise.topic" />
                        <AiChip v-if="exercise.aiGenerated" />
                    </div>
                    <StatusChip :status="exercise.status" :label="exercise.statusLabel" />
                </div>

                <h2 class="flex-1 text-[19px] leading-snug font-bold">{{ exercise.title }}</h2>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-1.5 text-[15px] font-semibold whitespace-nowrap text-ink-muted">
                        <StarIcon class="size-5" />
                        <template v-if="exercise.status === 'new'">{{ exercise.stars }} te verdienen</template>
                        <template v-else>{{ exercise.bestStars }} / {{ exercise.stars }} verdiend</template>
                    </span>

                    <Link
                        :href="`/exercises/${exercise.id}`"
                        class="rounded-xl py-2.5 text-[15px] font-bold whitespace-nowrap transition focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-purple-600"
                        :class="actions[exercise.status].class"
                    >
                        {{ actions[exercise.status].label }}<span class="sr-only">: {{ exercise.title }}</span>
                    </Link>
                </div>
            </article>
        </div>

        <p v-else class="rounded-[20px] border border-line-soft bg-white p-[22px] text-ink-muted">
            Geen oefeningen gevonden met deze filters.
            <Link href="/exercises" class="font-semibold text-purple-700 hover:text-purple-800">Toon alle oefeningen</Link>
        </p>
    </AppLayout>
</template>
