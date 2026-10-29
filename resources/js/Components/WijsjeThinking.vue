<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import Owl from '@/Components/Owl.vue'

const props = defineProps({
    // Defaults to Wijsje thinking about a question; pages pass their own for checking answers or making questions.
    texts: { type: Array, default: null },
    label: { type: String, default: 'Wijsje denkt na' },
    large: { type: Boolean, default: false },
})

// Shown while Wijsje waits for the AI; a new text every few seconds so a slow answer still feels alive.
const THINKING = [
    'Wijsje denkt na…',
    'Even mijn veren schudden…',
    'Ik zet mijn uilenbril op…',
    'Oehoe, even denken…',
    'Ik draai mijn kop een rondje…',
    'Even in mijn boom zoeken…',
    'Ik blader door mijn Laravel-boek…',
    'Even de code lezen…',
    'Ik zoek de makkelijkste woorden…',
    'Ik maak het simpel voor je…',
    'Goede vraag! Even denken…',
    'Hmm, slimme vraag…',
    'Ik bedenk een goed voorbeeld…',
]
const INTERVAL = 4000

const text = ref(null)
let timer = null

const texts = props.texts ?? THINKING

const pick = () => {
    const others = texts.filter(candidate => candidate !== text.value)
    text.value = others[Math.floor(Math.random() * others.length)]
}

onMounted(() => {
    text.value = texts[0]
    timer = setInterval(pick, INTERVAL)
})

onBeforeUnmount(() => clearInterval(timer))
</script>

<template>
    <div class="flex items-end" :class="large ? 'gap-3' : 'gap-2'">
        <Owl class="shrink-0" :class="large ? 'size-14' : 'size-7'" />
        <p class="flex items-center rounded-2xl rounded-bl-md bg-paper" :class="large ? 'gap-3 px-5 py-3.5 text-lg font-semibold text-ink' : 'gap-2.5 px-3.5 py-2.5 text-ink-muted'">
            <span class="flex items-center gap-1" aria-hidden="true">
                <span v-for="delay in ['0ms', '150ms', '300ms']" :key="delay" class="rounded-full bg-purple-600 motion-safe:animate-bounce" :class="large ? 'size-2.5' : 'size-2'" :style="{ animationDelay: delay }" />
            </span>
            <!-- The rotating text is for the eye only; a screen reader hears one steady message. -->
            <span aria-hidden="true">{{ text }}</span>
            <span class="sr-only">{{ label }}</span>
        </p>
    </div>
</template>
