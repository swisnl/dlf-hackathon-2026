<script setup>
import WijsjeBusy from '@/Components/WijsjeBusy.vue'
import { Link, useForm } from '@inertiajs/vue3'
import CodeBlock from '@/Components/CodeBlock.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    submission: { type: Object, required: true },
    exercises: { type: Array, required: true },
})

const form = useForm({})

function newExercise() {
    form.post(`/submissions/${props.submission.id}/exercises`)
}
</script>

<template>
    <AppLayout :title="submission.title">
        <div>
            <Link href="/submissions" class="link text-sm">&larr; Mijn code</Link>
            <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="font-display text-4xl font-extrabold tracking-tight">{{ submission.title }}</h1>
                    <p class="mt-1 font-mono text-sm text-ink-muted">{{ submission.filename ?? 'Geplakte code' }}</p>
                </div>
                <form :aria-busy="form.processing" @submit.prevent="newExercise">
                    <button type="submit" class="btn-primary" :disabled="form.processing">{{ form.processing ? 'Wijsje maakt een vraag…' : 'Stel me een nieuwe vraag' }}</button>
                </form>
            </div>
            <WijsjeBusy v-if="form.processing" task="creating" class="mt-5" />

            <h2 class="mt-9 font-display text-2xl font-extrabold">Vragen</h2>
            <Link v-for="exercise in exercises" :key="exercise.id" :href="`/exercises/${exercise.id}`" class="card mt-3 flex flex-wrap items-center justify-between gap-3 p-5 transition-colors hover:border-purple-600">
                <span class="min-w-0">
                    <span class="block text-lg font-bold">{{ exercise.topic }}</span>
                    <span class="block text-ink-muted">{{ exercise.title }}</span>
                </span>
                <span v-if="exercise.earned_stars !== null" class="chip shrink-0 bg-found-light text-[#075E5A]"><span aria-hidden="true">✓</span> Gemaakt · {{ exercise.earned_stars }} van {{ exercise.stars }} sterren</span>
                <span v-else class="chip shrink-0 bg-purple-100 text-purple-800">Nieuw</span>
            </Link>
            <p v-if="exercises.length === 0" class="card mt-3 p-5 text-ink-muted">Nog geen vragen. Klik op de knop en de AI maakt er een.</p>

            <details class="card mt-8 p-5">
                <summary class="cursor-pointer font-semibold">Bekijk je code</summary>
                <CodeBlock :code="submission.code" class="mt-4" />
            </details>
        </div>
    </AppLayout>
</template>
