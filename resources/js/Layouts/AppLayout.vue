<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import Owl from '@/Components/Owl.vue'
import StarIcon from '@/Components/StarIcon.vue'

defineProps({
    title: { type: String, required: true },
})

const page = usePage()
const user = computed(() => page.props.auth.user)

const menu = [
    { label: 'Dashboard', href: '/' },
    { label: 'Oefeningen', href: '/oefeningen' },
    { label: 'Badges', href: '/badges' },
]

const isActive = href => (href === '/' ? page.url === '/' : page.url.startsWith(href))
const onProfile = computed(() => isActive('/profiel'))
</script>

<template>
    <Head :title="title" />

    <header class="border-b border-line-soft bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-7 gap-y-3 px-4 py-3.5 sm:px-6">
            <Link href="/" aria-label="Eigenwijs, naar dashboard" class="flex items-center gap-2.5">
                <Owl class="size-9" />
                <span class="font-display text-2xl font-extrabold tracking-tight">eigenwijs</span>
            </Link>

            <nav aria-label="Hoofdmenu" class="flex flex-wrap gap-1">
                <Link
                    v-for="item in menu"
                    :key="item.href"
                    :href="item.href"
                    :aria-current="isActive(item.href) ? 'page' : undefined"
                    class="rounded-[10px] px-3.5 py-2.5 focus-visible:outline-3 focus-visible:outline-purple-600"
                    :class="isActive(item.href) ? 'bg-purple-100 font-bold text-purple-800' : 'font-semibold text-ink-muted hover:bg-purple-100/50'"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <div class="ml-auto flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-star-soft px-3.5 py-2 font-bold">
                    <StarIcon class="size-5" />
                    {{ user.stars }}
                    <span class="sr-only">sterren</span>
                </span>

                <Link
                    href="/profiel"
                    :aria-current="onProfile ? 'page' : undefined"
                    :aria-label="`Profiel van ${user.name}`"
                    class="flex size-11 items-center justify-center rounded-full font-bold text-white focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-purple-600"
                    :class="onProfile ? 'bg-purple-600 ring-3 ring-purple-100' : 'bg-ink'"
                >
                    {{ user.initials }}
                </Link>
            </div>
        </div>
    </header>

    <main class="mx-auto flex max-w-7xl flex-col gap-7 px-4 pt-10 pb-16 sm:px-6">
        <slot />
    </main>
</template>
