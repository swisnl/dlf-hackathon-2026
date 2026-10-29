<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import StatusMessage from '@/Components/StatusMessage.vue'
import TextField from '@/Components/TextField.vue'

const page = usePage()

const form = useForm({
    email: '',
})

const submit = () => form.post('/forgot-password')
</script>

<template>
    <AuthLayout title="Wachtwoord vergeten">
        <h1 class="font-display text-[34px] font-extrabold">Wachtwoord vergeten?</h1>

        <p class="text-[17px] leading-relaxed text-ink-muted">
            Geen probleem. Vul je e-mailadres in, dan sturen we je een link om een nieuw wachtwoord in te stellen.
        </p>

        <StatusMessage v-if="page.props.status">{{ page.props.status }}</StatusMessage>

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

            <PrimaryButton type="submit" :disabled="form.processing">Stuur de link</PrimaryButton>
        </form>

        <p class="text-center text-[15px] text-ink-muted">
            Weet je het weer?
            <Link href="/login" class="font-semibold text-purple-700 hover:text-purple-800">Terug naar inloggen</Link>
        </p>
    </AuthLayout>
</template>
