<script setup>
import { onMounted, ref } from 'vue'
import WijsjeThinking from '@/Components/WijsjeThinking.vue'

// A clear "Wijsje is working on it" block for the slow AI steps: checking an answer or making a new question.
const TEXTS = {
    checking: [
        'Wijsje leest je uitleg…',
        'Ik vergelijk de twee versies…',
        'Even goed nadenken over je keuze…',
        'Ik zet mijn uilenbril op…',
        'Ik tel je sterren…',
        'Bijna klaar met nakijken…',
    ],
    creating: [
        'Wijsje leest je code…',
        'Ik zoek een goede vraag…',
        'Even een foutje verstoppen…',
        'Ik maak twee versies van je code…',
        'Ik blader door mijn Laravel-boek…',
        'Bijna klaar met je vraag…',
    ],
}

const LABELS = {
    checking: 'Wijsje kijkt je antwoord na',
    creating: 'Wijsje maakt een vraag over je code',
}

const props = defineProps({
    task: { type: String, required: true, validator: task => ['checking', 'creating'].includes(task) },
})

const block = ref(null)

// The block can appear below the fold (under a long form), so bring it into view.
onMounted(() => block.value?.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'nearest' }))
</script>

<template>
    <div ref="block" role="status" class="card flex flex-col gap-4 border-2 border-purple-600 bg-purple-100/40 p-5 sm:p-6">
        <WijsjeThinking :texts="TEXTS[props.task]" :label="LABELS[props.task]" large />
        <div class="h-2 overflow-hidden rounded-full bg-purple-100" aria-hidden="true">
            <div class="h-full w-1/3 rounded-full bg-purple-600 motion-safe:animate-[wijsje-progress_1.6s_ease-in-out_infinite]" />
        </div>
        <p class="text-sm text-ink-muted">Dit kan even duren. Blijf gerust op deze pagina.</p>
    </div>
</template>
