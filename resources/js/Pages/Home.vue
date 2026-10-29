<script setup>
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import Owl from '../Components/Owl.vue'

const user = computed(() => usePage().props.auth?.user)
</script>

<template>
    <Head title="Start" />

    <section class="card flex flex-col items-start gap-6 p-6 sm:flex-row sm:items-center sm:p-10">
        <Owl :size="96" class="shrink-0" />
        <div class="flex flex-col gap-3">
            <h1 class="font-display text-4xl font-extrabold tracking-tight sm:text-5xl">Snap je je eigen code?</h1>
            <p class="max-w-2xl text-lg text-inkt-zacht">
                Upload je code. De AI maakt er een vraag over met twee versies: één klopt, één niet.
                Kies de goede en leg uit <b class="text-inkt">waarom</b>. Daarna kun je erover doorpraten.
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-4">
                <Link v-if="user" href="/submissions" class="btn-primary">Naar mijn code</Link>
                <template v-else>
                    <a href="/login" class="btn-primary">Inloggen</a>
                    <a href="/register" class="btn-secondary">Account maken</a>
                </template>
            </div>
        </div>
    </section>
</template>
