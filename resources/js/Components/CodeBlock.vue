<script setup>
defineProps({
    code: { type: String, required: true },
})
</script>

<template>
    <pre tabindex="0" class="overflow-x-auto rounded-xl bg-code py-3 font-mono text-sm leading-relaxed [font-variant-ligatures:none]"><code><span v-for="(line, index) in code.split('\n')" :key="index" class="flex gap-3 px-3"><span class="w-6 shrink-0 select-none text-right text-[#6B6580]" aria-hidden="true">{{ index + 1 }}</span><span class="whitespace-pre">{{ line }}</span></span></code></pre>
</template>
