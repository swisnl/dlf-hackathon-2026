<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    // Renders an Inertia link instead of a button.
    href: { type: String, default: null },
})
</script>

<template>
    <component
        :is="href ? Link : 'button'"
        :href="href"
        class="inline-block rounded-[14px] bg-purple-600 px-[22px] py-[15px] text-center text-[17px] font-bold text-white shadow-[0_4px_0_var(--color-purple-800)] transition hover:bg-purple-800 focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-purple-600 active:translate-y-[2px] active:shadow-[0_2px_0_var(--color-purple-800)] disabled:cursor-wait disabled:opacity-70"
    >
        <slot />
    </component>
</template>
