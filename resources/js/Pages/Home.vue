<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'

const page = usePage()
</script>

<template>
    <Head title="Dashboard" />

    <main class="mx-auto flex max-w-3xl flex-col gap-4 px-4 py-14">
        <h1 class="font-display text-4xl font-extrabold">Hoi {{ page.props.auth.user.name }}</h1>

        <!-- Temporary until the profile page (#25) takes over logging out. -->
        <Link href="/logout" method="post" as="button" class="cursor-pointer self-start font-semibold text-ink-muted underline">
            Uitloggen
        </Link>
    </main>
</template>
