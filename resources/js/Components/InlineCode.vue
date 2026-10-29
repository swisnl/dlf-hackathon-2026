<script setup>
import { computed } from 'vue'

// Shows text from the AI or the seeders, where code is marked with `backticks` and longer code with ```fences```.
const props = defineProps({
    text: { type: String, default: '' },
})

// Split into text and fenced code blocks; the language after the opening fence (```php) is dropped.
const segments = computed(() => (props.text ?? '')
    .split(/```[a-z]*\n?([\s\S]*?)```/i)
    .map((part, index) => (index % 2 === 1
        ? { block: part.replace(/\n+$/, '') }
        : { inline: part.replace(/^\n+|\n+$/g, '').split('`') }))
    .filter(segment => segment.block || segment.inline.join('') !== ''))
</script>

<template>
    <template v-for="(segment, index) in segments" :key="index">
        <!-- A span, not a pre: this component sits inside paragraphs. -->
        <span v-if="segment.block !== undefined" class="my-2 block overflow-x-auto rounded-xl bg-code px-3 py-2 font-mono text-sm whitespace-pre text-ink [font-variant-ligatures:none]">{{ segment.block }}</span>
        <template v-else>
            <template v-for="(part, partIndex) in segment.inline" :key="partIndex">
                <code v-if="partIndex % 2 === 1" class="rounded bg-code px-1 py-0.5 font-mono text-[0.9em] [font-variant-ligatures:none]">{{ part }}</code>
                <template v-else>{{ part }}</template>
            </template>
        </template>
    </template>
</template>
