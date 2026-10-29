<script setup>
import { computed } from 'vue'

// Shows text from the AI or the seeders, where code is marked with `backticks`.
const props = defineProps({
    text: { type: String, default: '' },
})

const parts = computed(() => (props.text ?? '').split('`'))
</script>

<template>
    <template v-for="(part, index) in parts" :key="index">
        <code v-if="index % 2 === 1" class="rounded bg-code px-1 py-0.5 font-mono text-[0.9em]">{{ part }}</code>
        <template v-else>{{ part }}</template>
    </template>
</template>
