<script setup>
// The Eigenwijs owl, looking sceptically to the side.
defineProps({
    size: { type: Number, default: 36 },
    tile: { type: String, default: '#9333EA' },
    eyes: { type: String, default: '#FFFFFF' },
    label: { type: String, default: null },
})
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 48 48" :role="label ? 'img' : undefined" :aria-label="label ?? undefined" :aria-hidden="label ? undefined : 'true'">
        <rect v-if="tile" width="48" height="48" rx="14" :fill="tile" />
        <circle cx="16" cy="23" r="8.5" :fill="eyes" /><circle cx="32" cy="23" r="8.5" :fill="eyes" />
        <circle cx="19" cy="24" r="4" fill="#1D1A2F" /><circle cx="35" cy="24" r="4" fill="#1D1A2F" />
        <path d="M8 12.5 L22 13" stroke="#1D1A2F" stroke-width="2.6" stroke-linecap="round" /><path d="M26 13 L40 9.5" stroke="#1D1A2F" stroke-width="2.6" stroke-linecap="round" />
        <path d="M21 33 L27 33 L24 38 Z" fill="#FFB800" />
    </svg>
</template>
