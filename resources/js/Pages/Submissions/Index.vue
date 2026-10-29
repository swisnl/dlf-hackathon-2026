<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import FieldError from '../../Components/FieldError.vue'

const props = defineProps({
    submissions: { type: Array, required: true },
    extensions: { type: Array, required: true },
})

const form = useForm({
    title: '',
    file: null,
    code: '',
})

const accept = props.extensions.map(extension => `.${extension}`).join(',')

function submit() {
    form.post('/submissions')
}
</script>

<template>
    <Head title="Mijn code" />

    <div class="grid gap-8 lg:grid-cols-5">
        <section class="lg:col-span-3">
            <form class="card flex flex-col gap-5 p-6 sm:p-7" :aria-busy="form.processing" @submit.prevent="submit">
                <div>
                    <h1 class="font-display text-[28px] font-extrabold tracking-tight">Upload je code</h1>
                    <p class="mt-1 text-inkt-zacht">De AI stelt je vragen over jouw code. Kies steeds welke variant juist is en leg uit waarom.</p>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="title" class="label">Waar gaat je code over?</label>
                    <input id="title" v-model="form.title" type="text" required placeholder="Bijvoorbeeld: opdracht 3, winkelmandje" class="field">
                    <FieldError :message="form.errors.title" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="file" class="label">Bestand</label>
                    <input
                        id="file"
                        type="file"
                        :accept="accept"
                        class="block w-full text-[15px] file:mr-3 file:cursor-pointer file:rounded-xl file:border-0 file:bg-paars-licht file:px-4 file:py-2.5 file:font-semibold file:text-paars-diep"
                        @input="form.file = $event.target.files[0] ?? null"
                    >
                    <p class="text-sm text-inkt-zacht">Maximaal 200 KB. Bijvoorbeeld .php, .js, .py of .java.</p>
                    <FieldError :message="form.errors.file" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="code" class="label">Of plak je code hier</label>
                    <textarea id="code" v-model="form.code" rows="9" class="field font-mono text-sm [font-variant-ligatures:none]" />
                    <FieldError :message="form.errors.code" />
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <button type="submit" class="btn-primary" :disabled="form.processing">{{ form.processing ? 'De AI maakt een vraag…' : 'Upload en start' }}</button>
                    <p class="text-sm text-inkt-zacht">De AI heeft even nodig om een vraag te maken.</p>
                </div>
            </form>
        </section>

        <section class="lg:col-span-2">
            <h2 class="font-display text-2xl font-extrabold">Eerder geüpload</h2>
            <Link v-for="submission in submissions" :key="submission.id" :href="`/submissions/${submission.id}`" class="card mt-3 block p-5 transition-colors hover:border-paars">
                <span class="block text-lg font-bold">{{ submission.title }}</span>
                <span class="mt-2 flex flex-wrap items-center gap-2 text-sm text-inkt-zacht">
                    <span class="font-mono">{{ submission.filename ?? 'Geplakte code' }}</span>
                    <span class="chip bg-paars-licht text-paars-diep">{{ submission.exercises_count }} {{ submission.exercises_count === 1 ? 'vraag' : 'vragen' }}</span>
                    <span>{{ submission.created_at }}</span>
                </span>
            </Link>
            <p v-if="submissions.length === 0" class="card mt-3 p-5 text-inkt-zacht">Nog niks hier. Upload je eerste code en de AI stelt je een vraag.</p>
        </section>
    </div>
</template>
