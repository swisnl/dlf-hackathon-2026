<script setup>
defineProps({
    // Per line the coloured pieces from the CodeHighlighter: [{ text, color }].
    lines: { type: Array, required: true },
    // Line numbers (1-based) that are marked.
    selected: { type: Array, default: () => [] },
    // Every line becomes a toggle button.
    interactive: { type: Boolean, default: false },
})

defineEmits(['toggle'])
</script>

<template>
    <div class="overflow-x-auto px-1.5 pt-2.5 pb-3.5">
        <!-- min-w-max keeps every line as wide as the longest one, so a marked line is marked all the way. -->
        <div class="flex min-w-max flex-col gap-0.5 font-mono text-sm leading-[22px] [font-variant-ligatures:none]">
            <component
                :is="interactive ? 'button' : 'div'"
                v-for="(pieces, index) in lines"
                :key="index"
                :type="interactive ? 'button' : undefined"
                :aria-pressed="interactive ? selected.includes(index + 1) : undefined"
                class="flex w-full items-start gap-2.5 rounded-md py-0.5 pr-3 pl-1.5 text-left whitespace-pre"
                :class="[selected.includes(index + 1) ? 'bg-[#FFE27A]' : interactive && 'hover:bg-paper', interactive && 'cursor-pointer focus-visible:-outline-offset-3']"
                @click="interactive && $emit('toggle', index + 1)"
            >
                <span class="sr-only">Regel {{ index + 1 }}{{ !interactive && selected.includes(index + 1) ? ', aangeklikt' : '' }}: </span>
                <span class="w-[22px] shrink-0 text-right text-[#8A8499] select-none" aria-hidden="true">{{ index + 1 }}</span>
                <span><span v-for="(piece, pieceIndex) in pieces" :key="pieceIndex" :style="{ color: piece.color }">{{ piece.text }}</span></span>
            </component>
        </div>
    </div>
</template>
