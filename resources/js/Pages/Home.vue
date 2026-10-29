<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import Layout from '@/Layouts/Layout.vue'
import Owl from '@/Components/Owl.vue'

defineOptions({ layout: Layout })

const page = usePage()
</script>

<template>
    <Head title="Start" />

    <section class="card flex flex-col items-start gap-6 p-6 sm:flex-row sm:items-center sm:p-10">
        <Owl class="size-24 shrink-0" />
        <div class="flex flex-col gap-3">
            <h1 class="font-display text-4xl font-extrabold tracking-tight sm:text-5xl">Hoi {{ page.props.auth.user.name }}</h1>
            <p class="max-w-2xl text-lg text-ink-muted">
                Snap je je eigen code? Upload je code. De AI maakt er een vraag over met twee versies: één klopt, één niet.
                Kies de goede en leg uit <b class="text-ink">waarom</b>. Daarna kun je erover doorpraten.
            </p>
            <div class="mt-2">
                <Link href="/submissions" class="btn-primary">Naar mijn code</Link>
            </div>
        </div>
    </section>
</template>
