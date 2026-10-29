<script setup>
import { useId } from 'vue'

defineOptions({ inheritAttrs: false })

defineProps({
    label: { type: String, required: true },
    error: { type: String, default: null },
})

const model = defineModel({ type: String })
const id = useId()
</script>

<template>
    <div class="flex flex-col gap-2">
        <div class="flex items-baseline justify-between">
            <label :for="id" class="text-[15px] font-semibold">{{ label }}</label>
            <slot name="aside" />
        </div>
        <input
            :id="id"
            v-model="model"
            v-bind="$attrs"
            :aria-invalid="error ? 'true' : undefined"
            :aria-describedby="error ? `${id}-error` : undefined"
            class="rounded-xl border-2 bg-white px-3.5 py-[13px] text-base text-ink outline-purple-100 focus:border-purple-600 focus:outline-3"
            :class="error ? 'border-missed' : 'border-line'"
        >
        <p v-if="error" :id="`${id}-error`" class="text-sm font-medium text-missed">{{ error }}</p>
    </div>
</template>
