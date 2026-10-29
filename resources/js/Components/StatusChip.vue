<script setup>
defineProps({
    status: { type: String, required: true },
    label: { type: String, required: true },
})

const colors = {
    new: 'bg-purple-100 text-purple-800',
    retry: 'bg-[#FFF0DC] text-[#8A4300]',
    done: 'bg-[#E1F4F2] text-[#075E5A]',
}
</script>

<template>
    <span class="rounded-full px-2.5 py-1 text-[13px] font-semibold" :class="colors[status]">{{ label }}</span>
</template>
