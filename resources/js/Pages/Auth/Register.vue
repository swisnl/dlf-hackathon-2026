<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import AuthTabs from '@/Components/AuthTabs.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextField from '@/Components/TextField.vue'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = () => form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
})
</script>

<template>
    <AuthLayout title="Registreren">
        <AuthTabs active="register" />

        <h1 class="font-display text-[34px] font-extrabold">Account maken</h1>

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <TextField
                v-model="form.name"
                label="Naam"
                type="text"
                autocomplete="name"
                required
                autofocus
                :error="form.errors.name"
            />

            <TextField
                v-model="form.email"
                label="E-mailadres"
                type="email"
                autocomplete="username"
                required
                :error="form.errors.email"
            />

            <TextField
                v-model="form.password"
                label="Wachtwoord"
                type="password"
                autocomplete="new-password"
                required
                :error="form.errors.password"
            />

            <TextField
                v-model="form.password_confirmation"
                label="Wachtwoord herhalen"
                type="password"
                autocomplete="new-password"
                required
                :error="form.errors.password_confirmation"
            />

            <PrimaryButton type="submit" :disabled="form.processing">Account maken</PrimaryButton>
        </form>

        <p class="text-center text-[15px] text-ink-muted">
            Heb je al een account?
            <Link href="/login" class="font-semibold text-purple-700 hover:text-purple-800">Log in</Link>
        </p>
    </AuthLayout>
</template>
