<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextField from '@/Components/TextField.vue'

const props = defineProps({
    token: { type: String, required: true },
    email: { type: String, required: true },
})

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const submit = () => form.post('/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
})
</script>

<template>
    <AuthLayout title="Nieuw wachtwoord">
        <h1 class="font-display text-[34px] font-extrabold">Nieuw wachtwoord</h1>

        <form class="flex flex-col gap-5" @submit.prevent="submit">
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
                label="Nieuw wachtwoord"
                type="password"
                autocomplete="new-password"
                required
                autofocus
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

            <PrimaryButton type="submit" :disabled="form.processing">Wachtwoord opslaan</PrimaryButton>
        </form>
    </AuthLayout>
</template>
