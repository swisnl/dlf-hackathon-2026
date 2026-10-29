<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    active: { type: String, required: true },
})

const tabs = [
    { key: 'login', label: 'Inloggen', href: '/login' },
    { key: 'register', label: 'Registreren', href: '/register' },
]
</script>

<template>
    <nav aria-label="Inloggen of registreren" class="flex rounded-[14px] bg-purple-100 p-1">
        <Link
            v-for="tab in tabs"
            :key="tab.key"
            :href="tab.href"
            :aria-current="tab.key === active ? 'page' : undefined"
            class="flex-1 rounded-[11px] p-3 text-center text-base focus-visible:outline-3 focus-visible:outline-purple-600"
            :class="tab.key === active
                ? 'bg-white font-bold text-ink shadow-[0_1px_3px_rgba(29,26,47,0.15)]'
                : 'font-semibold text-purple-800 hover:bg-white/50'"
        >
            {{ tab.label }}
        </Link>
    </nav>
</template>
