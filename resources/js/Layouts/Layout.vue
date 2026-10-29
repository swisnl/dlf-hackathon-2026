<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Owl from '@/Components/Owl.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const initials = computed(() => (user.value?.name ?? '').split(' ').filter(Boolean).slice(0, 2).map(part => part[0].toUpperCase()).join(''))
const onMyCode = computed(() => page.component.startsWith('Submissions/') || page.component.startsWith('Exercises/'))
</script>

<template>
    <header class="border-b border-line-light bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-4 gap-y-3 px-4 py-3.5 sm:gap-x-7 sm:px-6">
            <Link href="/" class="flex items-center gap-2.5 text-ink" aria-label="Eigenwijs, naar de start">
                <Owl class="size-9" />
                <span class="font-display text-xl font-extrabold tracking-tight sm:text-2xl">eigenwijs</span>
            </Link>
            <template v-if="user">
                <nav aria-label="Hoofdmenu" class="flex gap-1">
                    <Link
                        href="/submissions"
                        :class="['rounded-[10px] px-3.5 py-2.5', onMyCode ? 'bg-purple-100 font-bold text-purple-800' : 'font-semibold text-ink-muted hover:text-ink']"
                        :aria-current="onMyCode ? 'page' : undefined"
                    >Mijn code</Link>
                </nav>
                <div class="ml-auto flex items-center gap-3">
                    <span class="hidden size-11 items-center justify-center rounded-full bg-ink font-bold text-white sm:flex" :title="user.name" aria-hidden="true">{{ initials }}</span>
                    <span class="sr-only">Ingelogd als {{ user.name }}</span>
                    <Link href="/logout" method="post" as="button" class="font-semibold text-ink-muted underline-offset-4 hover:text-ink hover:underline">Uitloggen</Link>
                </div>
            </template>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 pt-8 pb-16 sm:px-6 sm:pt-10">
        <div v-if="page.props.flash?.error" class="mb-6 rounded-[14px] bg-missed-light px-4 py-3 text-[15px] text-missed-text" role="alert">{{ page.props.flash.error }}</div>
        <slot />
    </main>
</template>
