<script>
import { reactive } from 'vue'

// Shared by every page, so the conversation survives navigating between pages. It is never stored on the server (#31).
const conversation = reactive({ open: false, messages: [] })
</script>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Owl from '@/Components/Owl.vue'

const MAX_HISTORY = 10

// Shown while Wijsje waits for the AI; a new one every few seconds so a slow answer still feels alive.
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
const THINKING_INTERVAL = 4000

const page = usePage()
const toggle = ref(null)
const input = ref(null)
const log = ref(null)
const question = ref('')
const busy = ref(false)
const thinking = ref(THINKING[0])
let thinkingTimer = null

const pickThinking = () => {
    const others = THINKING.filter(text => text !== thinking.value)
    thinking.value = others[Math.floor(Math.random() * others.length)]
}

watch(busy, isBusy => {
    clearInterval(thinkingTimer)

    if (isBusy) {
        pickThinking()
        thinkingTimer = setInterval(pickThinking, THINKING_INTERVAL)
    }
})

onBeforeUnmount(() => clearInterval(thinkingTimer))
const error = ref(null)

const exercise = computed(() => (page.component === 'Exercises/Show' ? page.props.exercise : null))

const suggestions = computed(() => (exercise.value
    ? ['Leg deze oefening simpel uit', `Wat betekent ${exercise.value.topic}?`, 'Waar moet ik op letten?']
    : ['Wat is een migration?', 'Wat betekent mass assignment?', 'Wat doet een controller?']))

const open = async () => {
    conversation.open = true
    await nextTick()
    log.value?.scrollTo({ top: log.value.scrollHeight })
    input.value?.focus()
}

const close = () => {
    conversation.open = false
    nextTick(() => toggle.value?.focus())
}

// Keep the newest message (or the thinking dots) in view.
watch([() => conversation.messages.length, busy, error], async () => {
    await nextTick()
    log.value?.scrollTo({ top: log.value.scrollHeight })
})

const xsrfToken = () => decodeURIComponent(document.cookie.split('; ').find(cookie => cookie.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? '')

const ask = async (text = question.value) => {
    text = text.trim()

    if (text === '' || busy.value) {
        return
    }

    const history = conversation.messages.slice(-MAX_HISTORY)
    conversation.messages.push({ role: 'student', content: text })
    question.value = ''
    error.value = null
    busy.value = true

    try {
        const response = await fetch('/wijsje', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
            body: JSON.stringify({ question: text, history, exercise_id: exercise.value?.id ?? null }),
        })
        const data = await response.json().catch(() => ({}))

        if (!response.ok) {
            throw new Error(response.status === 429 ? 'Je stelt veel vragen achter elkaar. Wacht even en probeer het dan opnieuw.' : (data.message ?? 'Wijsje kon nu geen antwoord geven. Probeer het zo opnieuw.'))
        }

        conversation.messages.push({ role: 'wijsje', content: data.reply })
    } catch (e) {
        conversation.messages.pop()
        question.value = text
        error.value = e.message
    } finally {
        busy.value = false
        await nextTick()
        input.value?.focus()
    }
}

const onKeydown = event => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault()
        ask()
    }
}
</script>

<template>
    <div class="fixed right-4 bottom-4 z-40 flex flex-col items-end gap-3 sm:right-6 sm:bottom-6" @keydown.esc="close">
        <section
            v-if="conversation.open"
            id="wijsje"
            role="dialog"
            aria-labelledby="wijsje-title"
            class="card flex max-h-[min(560px,calc(100vh-8rem))] w-[min(380px,calc(100vw-2rem))] flex-col shadow-[0_12px_40px_rgba(29,26,47,0.18)]"
        >
            <div class="flex items-center gap-3 border-b border-line-light px-4 py-3">
                <Owl class="size-9 shrink-0" />
                <div class="min-w-0 flex-1">
                    <h2 id="wijsje-title" class="font-display text-xl font-extrabold">Wijsje</h2>
                    <p class="text-sm text-ink-muted">Ik leg moeilijke woorden simpel uit.</p>
                </div>
                <button type="button" class="rounded-full p-2 text-ink-muted hover:bg-paper" aria-label="Wijsje sluiten" @click="close">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>

            <div ref="log" role="log" tabindex="0" aria-label="Gesprek met Wijsje" class="flex flex-1 flex-col gap-3 overflow-y-auto px-4 py-4">
                <template v-if="conversation.messages.length === 0">
                    <p>Hoi! Snap je een woord of stukje code niet? Vraag het mij. Ik verklap geen antwoorden, maar ik help je wel op weg.</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="suggestion in suggestions"
                            :key="suggestion"
                            type="button"
                            class="rounded-full border-2 border-purple-600 px-3 py-1.5 text-sm font-semibold text-purple-800 hover:bg-purple-100"
                            @click="ask(suggestion)"
                        >
                            {{ suggestion }}
                        </button>
                    </div>
                </template>

                <template v-for="(message, index) in conversation.messages" :key="index">
                    <p v-if="message.role === 'student'" class="max-w-[85%] self-end rounded-2xl rounded-br-md bg-purple-600 px-3.5 py-2 whitespace-pre-line text-white">
                        <span class="sr-only">Jij: </span>{{ message.content }}
                    </p>
                    <div v-else class="flex items-end gap-2">
                        <Owl class="size-7 shrink-0" />
                        <p class="max-w-[85%] rounded-2xl rounded-bl-md bg-paper px-3.5 py-2 whitespace-pre-line">
                            <span class="sr-only">Wijsje: </span>{{ message.content }}
                        </p>
                    </div>
                </template>

                <div v-if="busy" class="flex items-end gap-2">
                    <Owl class="size-7 shrink-0" />
                    <p class="flex items-center gap-2.5 rounded-2xl rounded-bl-md bg-paper px-3.5 py-2.5 text-ink-muted">
                        <span class="flex items-center gap-1" aria-hidden="true">
                            <span v-for="delay in ['0ms', '150ms', '300ms']" :key="delay" class="size-2 rounded-full bg-purple-600 motion-safe:animate-bounce" :style="{ animationDelay: delay }" />
                        </span>
                        <!-- The rotating text is for the eye only; a screen reader hears one steady message. -->
                        <span aria-hidden="true">{{ thinking }}</span>
                        <span class="sr-only">Wijsje denkt na</span>
                    </p>
                </div>
                <p v-if="error" class="rounded-[14px] bg-missed-light px-3.5 py-2.5 text-[15px] text-missed-text" role="alert">{{ error }}</p>
            </div>

            <form class="flex items-end gap-2 border-t border-line-light p-3" @submit.prevent="ask()">
                <label for="wijsje-question" class="sr-only">Je vraag aan Wijsje</label>
                <textarea
                    id="wijsje-question"
                    ref="input"
                    v-model="question"
                    rows="2"
                    maxlength="1000"
                    placeholder="Stel je vraag…"
                    class="field min-h-0 flex-1 resize-none py-2.5"
                    @keydown="onKeydown"
                />
                <button type="submit" class="btn-primary px-4 py-2.5" :disabled="busy || question.trim() === ''">Vraag</button>
            </form>
        </section>

        <button
            ref="toggle"
            type="button"
            class="group flex items-center gap-2.5 rounded-[20px] bg-white py-1.5 pr-4 pl-1.5 font-bold shadow-[0_6px_24px_rgba(29,26,47,0.18)] hover:bg-purple-100"
            :aria-expanded="conversation.open"
            aria-controls="wijsje"
            @click="conversation.open ? close() : open()"
        >
            <Owl class="size-12 motion-safe:transition-transform motion-safe:group-hover:-rotate-6" />
            <span>{{ conversation.open ? 'Sluiten' : 'Vraag het Wijsje' }}</span>
        </button>
    </div>
</template>
