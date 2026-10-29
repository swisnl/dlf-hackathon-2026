<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import AuthTabs from '@/Components/AuthTabs.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextField from '@/Components/TextField.vue'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => form.post('/login', {
    onFinish: () => form.reset('password'),
})
</script>

<template>
    <AuthLayout title="Inloggen">
        <AuthTabs active="login" />

        <h1 class="font-display text-[34px] font-extrabold">Welkom terug</h1>

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <TextField
                v-model="form.email"
                label="E-mailadres"
                type="email"
                autocomplete="username"
                required
                autofocus
                :error="form.errors.email"
            />

            <TextField
                v-model="form.password"
                label="Wachtwoord"
                type="password"
                autocomplete="current-password"
                required
                :error="form.errors.password"
            >
                <template #aside>
                    <Link href="/forgot-password" class="text-sm font-semibold text-purple-700 hover:text-purple-800">
                        Wachtwoord vergeten?
                    </Link>
                </template>
            </TextField>

            <label class="flex items-center gap-2.5 text-[15px]">
                <input v-model="form.remember" type="checkbox" class="size-[18px] accent-purple-600">
                Ingelogd blijven
            </label>

            <PrimaryButton type="submit" :disabled="form.processing">Inloggen</PrimaryButton>
        </form>

        <p class="text-center text-[15px] text-ink-muted">
            Nog geen account?
            <Link href="/register" class="font-semibold text-purple-700 hover:text-purple-800">Maak er een aan</Link>
        </p>
    </AuthLayout>
</template>
