<script setup>
defineProps({
    message: { type: String, default: null },
})
</script>

<template>
    <p v-if="message" class="text-sm font-semibold text-missed-text">{{ message }}</p>
</template>
