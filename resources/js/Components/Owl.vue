<script setup>
import { computed } from 'vue'

const props = defineProps({
    // mark: purple tile with white eyes, inverted: white tile with purple eyes, bare: white eyes without tile
    variant: { type: String, default: 'mark' },
})

const tile = computed(() => ({ mark: '#9333EA', inverted: '#FFFFFF', bare: null })[props.variant])
const eyes = computed(() => (props.variant === 'inverted' ? '#9333EA' : '#FFFFFF'))
</script>

<template>
    <svg viewBox="0 0 48 48" aria-hidden="true">
        <rect v-if="tile" width="48" height="48" rx="14" :fill="tile" />
        <circle cx="16" cy="23" r="8.5" :fill="eyes" />
        <circle cx="32" cy="23" r="8.5" :fill="eyes" />
        <circle cx="19" cy="24" r="4" fill="#1D1A2F" />
        <circle cx="35" cy="24" r="4" fill="#1D1A2F" />
        <path d="M8 12.5 L22 13" stroke="#1D1A2F" stroke-width="2.6" stroke-linecap="round" />
        <path d="M26 13 L40 9.5" stroke="#1D1A2F" stroke-width="2.6" stroke-linecap="round" />
        <path d="M21 33 L27 33 L24 38 Z" fill="#FFB800" />
    </svg>
</template>
