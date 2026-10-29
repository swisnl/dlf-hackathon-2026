<script setup>
import { computed } from 'vue'

const props = defineProps({
    badge: { type: Object, required: true },
})

// Badges store a Lucide icon name; these are the paths for the icons the seeded badges use.
const icons = {
    'lucide-search': ['M5 11a6 6 0 1 0 12 0a6 6 0 1 0-12 0', 'M16 16l5 5'],
    'lucide-bug': ['M7 13a5 5 0 0 1 10 0v2a5 5 0 0 1-10 0z', 'M12 8v12M7 12H3M17 12h4M7 17H4M17 17h3M9 5l1.5 3M15 5l-1.5 3'],
    'lucide-book-open': ['M4 5h6a2 2 0 0 1 2 2v13a2 2 0 0 0-2-2H4z', 'M20 5h-6a2 2 0 0 0-2 2v13a2 2 0 0 1 2-2h6z'],
    'lucide-lightbulb': ['M9 18h6M10 21h4', 'M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z'],
    'lucide-crown': ['M3 8l4 4 5-7 5 7 4-4-2 11H5z'],
    'lucide-award': ['M6 9a6 6 0 1 0 12 0a6 6 0 1 0-12 0', 'M8.5 14l-1.5 7 5-3 5 3-1.5-7'],
}
const lock = ['M7 11h10a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2z', 'M8 11V8a4 4 0 0 1 8 0v3']

const paths = computed(() => (props.badge.earned ? icons[props.badge.icon] ?? icons['lucide-award'] : lock))
const style = computed(() => (props.badge.earned
    ? { backgroundColor: `color-mix(in srgb, ${props.badge.color} 20%, white)`, borderColor: props.badge.color }
    : {}))
</script>

<template>
    <div
        class="flex items-center justify-center rounded-full border-4"
        :class="badge.earned ? 'border-solid' : 'border-dashed border-[#B9B2C4] bg-[#F1EEEA]'"
        :style="style"
        :title="badge.name"
    >
        <svg
            class="size-[42%]"
            viewBox="0 0 24 24"
            fill="none"
            :stroke="badge.earned ? '#1D1A2F' : '#8A8499'"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            role="img"
            :aria-label="`${badge.name}, ${badge.earned ? 'behaald' : 'nog niet behaald'}`"
        >
            <path v-for="d in paths" :key="d" :d="d" />
        </svg>
    </div>
</template>
