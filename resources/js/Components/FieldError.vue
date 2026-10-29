<script setup>
defineProps({
    message: { type: String, default: null },
})
</script>

<template>
    <p v-if="message" role="alert" class="text-sm font-semibold text-missed-text">{{ message }}</p>
</template>
