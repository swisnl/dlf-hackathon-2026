<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ExerciseLayout from '@/Layouts/ExerciseLayout.vue'
import TopicChip from '@/Components/TopicChip.vue'

const props = defineProps({
    exercise: { type: Object, required: true },
    // Both classes in the order this student sees them; the page does not know which one is wrong.
    classes: { type: Array, required: true },
    attempt: { type: Number, required: true },
    maxAttempts: { type: Number, required: true },
    completed: { type: Boolean, required: true },
})

const form = useForm({ choice: null, lines: [] })

const label = value => `Class ${value.toUpperCase()}`
const status = computed(() => (props.completed ? 'Afgerond' : `Poging ${props.attempt} van ${props.maxAttempts}`))
const isPicked = (value, line) => form.choice === value && form.lines.includes(line)

// Lines are picked in one class at a time: a click in the other class starts over there.
const toggle = (value, line) => {
    if (form.choice !== value) {
        form.choice = value
        form.lines = [line]

        return
    }

    form.lines = isPicked(value, line) ? form.lines.filter(picked => picked !== line) : [...form.lines, line]

    if (!form.lines.length) {
        form.choice = null
    }
}

const selection = computed(() => {
    const count = form.lines.length

    if (!count) {
        return 'Nog niets aangeklikt. Kies een class en klik op de foute regels.'
    }

    return `${count} ${count === 1 ? 'regel' : 'regels'} aangeklikt in class ${form.choice.toUpperCase()}.`
})

const submit = () => form.post(`/oefeningen/${props.exercise.id}/pogingen`, { preserveScroll: true })
</script>

<template>
    <ExerciseLayout :title="exercise.title" :status="status">
        <div class="flex flex-col gap-2.5">
            <div class="flex flex-wrap gap-2">
                <TopicChip :topic="exercise.topic" />
                <span v-if="attempt === 1 && !completed" class="rounded-full bg-star-soft px-2.5 py-1 text-[13px] font-semibold text-[#6B4B00]">
                    {{ exercise.stars }} {{ exercise.stars === 1 ? 'ster' : 'sterren' }} te verdienen
                </span>
            </div>
            <h1 class="font-display text-3xl font-extrabold tracking-tight sm:text-[40px] sm:leading-tight">{{ exercise.title }}</h1>
            <p class="max-w-[820px] text-lg text-ink-muted">
                Eén van deze twee classes klopt, in de andere zitten fouten. Klik in de foute class op <b class="text-ink">elke regel</b> waar het misgaat en lever in.
            </p>
        </div>

        <div class="flex flex-wrap gap-5">
            <section
                v-for="codeClass in classes"
                :key="codeClass.value"
                :aria-labelledby="`class-${codeClass.value}`"
                class="min-w-0 grow basis-[540px] overflow-hidden rounded-[20px] bg-white"
                :class="form.choice === codeClass.value ? 'border-2 border-purple-600' : 'border border-line-soft'"
            >
                <div class="flex items-center justify-between gap-2.5 border-b border-line-soft px-[18px] py-3.5">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <h2 :id="`class-${codeClass.value}`" class="font-display text-xl font-bold">{{ label(codeClass.value) }}</h2>
                        <span v-if="codeClass.file" class="truncate font-mono text-[13px] text-[#6B6680]">{{ codeClass.file }}</span>
                    </div>
                    <span v-if="form.choice === codeClass.value" class="shrink-0 rounded-full bg-purple-100 px-2.5 py-1 text-[13px] font-bold text-purple-800">Jouw keuze</span>
                </div>

                <div class="overflow-x-auto px-1.5 pt-2.5 pb-3.5">
                    <!-- min-w-max keeps every line as wide as the longest one, so a marked line is marked all the way. -->
                    <div class="flex min-w-max flex-col gap-0.5">
                        <button
                            v-for="(pieces, index) in codeClass.lines"
                            :key="index"
                            type="button"
                            :disabled="completed"
                            :aria-pressed="isPicked(codeClass.value, index + 1)"
                            class="flex w-full items-start gap-2.5 rounded-md py-0.5 pr-3 pl-1.5 text-left font-mono text-sm leading-[22px] whitespace-pre [font-variant-ligatures:none] focus-visible:outline-2 focus-visible:outline-purple-600 enabled:cursor-pointer"
                            :class="isPicked(codeClass.value, index + 1) ? 'bg-[#FFE27A]' : 'enabled:hover:bg-paper'"
                            @click="toggle(codeClass.value, index + 1)"
                        >
                            <span class="sr-only">Regel {{ index + 1 }}: </span>
                            <span class="w-[22px] shrink-0 text-right text-[#8A8499]" aria-hidden="true">{{ index + 1 }}</span>
                            <span><span v-for="(piece, pieceIndex) in pieces" :key="pieceIndex" :style="{ color: piece.color }">{{ piece.text }}</span></span>
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <div v-if="completed" class="rounded-[18px] border border-line-soft bg-white px-5 py-4 text-ink-muted">
            Je hebt deze oefening afgerond.
        </div>

        <form v-else class="flex flex-wrap items-center gap-4 rounded-[18px] border border-line-soft bg-white px-5 py-4" @submit.prevent="submit">
            <div class="flex flex-col gap-1">
                <p class="text-ink-muted" aria-live="polite">{{ selection }}</p>
                <p v-if="form.errors.choice || form.errors.lines" class="text-[15px] text-missed-text" role="alert">{{ form.errors.choice ?? form.errors.lines }}</p>
            </div>
            <button
                type="submit"
                :disabled="!form.lines.length || form.processing"
                class="ml-auto rounded-[14px] px-[26px] py-3.5 text-[17px] font-bold transition focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-purple-600"
                :class="form.lines.length ? 'bg-purple-600 text-white shadow-[0_4px_0_var(--color-purple-800)] hover:bg-purple-800 active:translate-y-[2px] active:shadow-[0_2px_0_var(--color-purple-800)] disabled:cursor-wait disabled:opacity-70' : 'cursor-not-allowed bg-[#E4E1EA] text-[#6B6680]'"
            >
                Inleveren
            </button>
        </form>
    </ExerciseLayout>
</template>
