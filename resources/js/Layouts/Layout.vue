<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Owl from '../Components/Owl.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const onMyCode = computed(() => page.component.startsWith('Submissions/') || page.component.startsWith('Exercises/'))
</script>

<template>
    <header class="border-b border-rand bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-4 gap-y-3 px-4 py-3.5 sm:gap-x-7 sm:px-6">
            <Link href="/" class="flex items-center gap-2.5 text-inkt" aria-label="Eigenwijs, naar de start">
                <Owl :size="36" />
                <span class="font-display text-xl font-extrabold tracking-tight sm:text-2xl">eigenwijs</span>
            </Link>
            <template v-if="user">
                <nav aria-label="Hoofdmenu" class="flex gap-1">
                    <Link
                        href="/submissions"
                        :class="['rounded-[10px] px-3.5 py-2.5', onMyCode ? 'bg-paars-licht font-bold text-paars-diep' : 'font-semibold text-inkt-zacht hover:text-inkt']"
                        :aria-current="onMyCode ? 'page' : undefined"
                    >Mijn code</Link>
                </nav>
                <div class="ml-auto flex items-center gap-3">
                    <span class="hidden size-11 items-center justify-center rounded-full bg-inkt font-bold text-white sm:flex" :title="user.name" aria-hidden="true">{{ user.initials }}</span>
                    <span class="sr-only">Ingelogd als {{ user.name }}</span>
                    <Link href="/logout" method="post" as="button" class="font-semibold text-inkt-zacht underline-offset-4 hover:text-inkt hover:underline">Uitloggen</Link>
                </div>
            </template>
            <div v-else class="ml-auto flex items-center gap-4">
                <a href="/login" class="link">Inloggen</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 pt-8 pb-16 sm:px-6 sm:pt-10">
        <div v-if="page.props.flash?.error" class="mb-6 rounded-[14px] bg-gemist-licht px-4 py-3 text-[15px] text-gemist-tekst" role="alert">{{ page.props.flash.error }}</div>
        <slot />
    </main>
</template>
