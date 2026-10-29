<script setup>
defineProps({
    topic: { type: Object, required: true },
})
</script>

<template>
    <span class="rounded-full px-2.5 py-1 text-[13px] font-semibold" :style="{ backgroundColor: topic.color, color: topic.text_color }">
        {{ topic.name }}
    </span>
</template>
