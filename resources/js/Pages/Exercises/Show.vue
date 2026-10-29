<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import CodeBlock from '@/Components/CodeBlock.vue'
import FieldError from '@/Components/FieldError.vue'
import InlineCode from '@/Components/InlineCode.vue'
import Owl from '@/Components/Owl.vue'
import Layout from '@/Layouts/Layout.vue'

defineOptions({ layout: Layout })

const props = defineProps({
    exercise: { type: Object, required: true },
    variants: { type: Array, required: true },
    // Null until the student answered; only then the page knows which variant is correct.
    attempt: { type: Object, default: null },
})

const answer = useForm({ choice: null, explanation: '' })
const message = useForm({ content: '' })
const next = useForm({})

function submitAnswer() {
    answer.post(`/exercises/${props.exercise.id}/answer`, { preserveScroll: true })
}

function submitMessage() {
    message.post(`/exercises/${props.exercise.id}/messages`, {
        preserveScroll: true,
        onSuccess: page => {
            // A failed AI call comes back as a flash error; keep the message so the student can try again.
            if (!page.props.flash?.error) {
                message.reset()
            }
        },
    })
}

function nextExercise() {
    next.post(`/submissions/${props.exercise.submission.id}/exercises`)
}
</script>

<template>
    <Head :title="exercise.topic" />

    <Link v-if="exercise.submission" :href="`/submissions/${exercise.submission.id}`" class="link text-sm">&larr; {{ exercise.submission.title }}</Link>
    <div class="mt-3 flex flex-col gap-2.5">
        <div class="flex flex-wrap gap-2">
            <span class="chip bg-purple-100 text-purple-800">{{ exercise.topic }}</span>
            <span v-if="attempt" class="chip bg-found-light text-[#075E5A]">Gemaakt</span>
        </div>
        <h1 class="font-display text-3xl font-extrabold tracking-tight sm:text-[40px] sm:leading-tight">{{ exercise.title }}</h1>
        <p v-if="!attempt" class="max-w-3xl text-lg text-ink-muted">Eén van deze twee versies klopt. Kies de goede en leg uit <b class="text-ink">waarom</b>.</p>
    </div>

    <form v-if="!attempt" class="mt-6" :aria-busy="answer.processing" @submit.prevent="submitAnswer">
        <fieldset>
            <legend class="sr-only">Welke variant is juist?</legend>
            <div class="grid gap-5 md:grid-cols-2">
                <label
                    v-for="variant in variants"
                    :key="variant.value"
                    class="card block min-w-0 cursor-pointer overflow-hidden border-2 has-[:checked]:border-purple-600 has-[:checked]:ring-[3px] has-[:checked]:ring-purple-100 has-[:focus-visible]:ring-[3px] has-[:focus-visible]:ring-purple-100"
                >
                    <span class="flex items-center gap-2.5 border-b border-line-light px-4 py-3.5">
                        <input v-model="answer.choice" type="radio" name="choice" :value="variant.value" required class="size-[18px] accent-purple-600">
                        <b class="font-display text-xl">{{ variant.label }}</b>
                    </span>
                    <CodeBlock :code="variant.code" class="m-3" />
                </label>
            </div>
            <FieldError :message="answer.errors.choice" class="mt-3" />
        </fieldset>

        <div class="card mt-5 flex flex-col gap-2 p-5 sm:p-6">
            <label for="explanation" class="font-display text-xl font-extrabold">Leg uit waarom jouw keuze juist is</label>
            <p class="text-ink-muted">Dit is het belangrijkste deel: laat zien dat je snapt wat er gebeurt.</p>
            <textarea id="explanation" v-model="answer.explanation" rows="5" required minlength="10" class="field mt-1" />
            <FieldError :message="answer.errors.explanation" />
            <div class="mt-2">
                <button type="submit" class="btn-primary" :disabled="answer.processing">{{ answer.processing ? 'De AI bekijkt je antwoord…' : 'Inleveren' }}</button>
            </div>
        </div>
    </form>

    <template v-else>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <section v-for="variant in variants" :key="variant.value" :class="['card min-w-0 overflow-hidden border-2', { 'border-found': variant.correct }]">
                <div class="flex flex-wrap items-center gap-2 border-b border-line-light px-4 py-3.5">
                    <b class="font-display text-xl">{{ variant.label }}</b>
                    <span v-if="variant.correct" class="chip bg-found-light text-[#075E5A]">Juist</span>
                    <span v-if="variant.value === attempt.chosen" class="chip bg-purple-100 text-purple-800">Jouw keuze</span>
                </div>
                <CodeBlock :code="variant.code" class="m-3" />
            </section>
        </div>

        <section class="card mt-5 flex flex-col gap-5 p-5 sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-[28px] font-extrabold">{{ attempt.correct ? 'Scherp gezien!' : 'Bijna! De andere versie klopt.' }}</h2>
                <span :class="['chip', attempt.stars > 0 ? 'bg-found-light text-[#075E5A]' : 'bg-missed-light text-missed-text']" title="Een inschatting van de AI">{{ attempt.stars }} van {{ exercise.stars }} sterren</span>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-ink-muted">Jouw uitleg</h3>
                <p class="mt-1 whitespace-pre-line">{{ attempt.explanation }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-ink-muted">Feedback</h3>
                <p class="mt-1 whitespace-pre-line"><InlineCode :text="attempt.feedback" /></p>
            </div>
            <div class="flex items-start gap-3 rounded-[14px] bg-star-light px-4 py-3.5">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8A6400" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="mt-0.5 shrink-0"><path d="M9 18h6M10 21h4" /><path d="M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z" /></svg>
                <div class="leading-relaxed">
                    <b>Waarom:</b>
                    <p v-for="error in attempt.errors" :key="error.id" class="whitespace-pre-line">
                        <span class="font-semibold"><InlineCode :text="error.title" /> (regel {{ error.line }} van {{ attempt.wrong_variant }}).</span> <InlineCode :text="error.explanation" />
                    </p>
                </div>
            </div>
        </section>

        <section id="gesprek" class="card mt-5 p-5 sm:p-7">
            <h2 class="font-display text-2xl font-extrabold">Praat erover door</h2>
            <p class="text-ink-muted">Reageer op de feedback of stel een vraag over deze oefening.</p>
            <div class="mt-5 flex flex-col gap-3">
                <template v-for="chat in attempt.messages" :key="chat.id">
                    <div v-if="chat.role === 'student'" class="flex justify-end">
                        <p class="max-w-[85%] rounded-2xl rounded-br-md bg-purple-600 px-4 py-2.5 whitespace-pre-line text-white">{{ chat.content }}</p>
                    </div>
                    <div v-else class="flex items-end gap-2.5">
                        <Owl class="size-8 shrink-0" />
                        <p class="max-w-[85%] rounded-2xl rounded-bl-md bg-paper px-4 py-2.5 whitespace-pre-line"><InlineCode :text="chat.content" /></p>
                    </div>
                </template>
                <div v-if="message.processing" class="flex justify-end">
                    <p class="max-w-[85%] rounded-2xl rounded-br-md bg-purple-600/70 px-4 py-2.5 whitespace-pre-line text-white">{{ message.content }}</p>
                </div>
            </div>
            <form class="mt-5 flex flex-col gap-3" :aria-busy="message.processing" @submit.prevent="submitMessage">
                <label for="content" class="sr-only">Jouw reactie</label>
                <textarea id="content" v-model="message.content" rows="3" required class="field" placeholder="Typ je reactie…" />
                <FieldError :message="message.errors.content" />
                <div>
                    <button type="submit" class="btn-primary" :disabled="message.processing">{{ message.processing ? 'De AI schrijft een reactie…' : 'Verstuur' }}</button>
                </div>
            </form>
        </section>

        <form v-if="exercise.submission" class="mt-8" :aria-busy="next.processing" @submit.prevent="nextExercise">
            <button type="submit" class="btn-secondary" :disabled="next.processing">{{ next.processing ? 'De AI maakt een vraag…' : 'Volgende vraag over deze code' }}</button>
        </form>
    </template>
</template>
