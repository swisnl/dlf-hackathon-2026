<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import StatusMessage from '@/Components/StatusMessage.vue'

const page = usePage()

const form = useForm({})

const submit = () => form.post('/email/verification-notification')
</script>

<template>
    <AuthLayout title="Bevestig je e-mailadres">
        <h1 class="font-display text-[34px] font-extrabold">Bevestig je e-mailadres</h1>

        <p class="text-[17px] leading-relaxed text-ink-muted">
            We hebben een mail gestuurd naar <strong class="font-semibold text-ink">{{ page.props.auth.user.email }}</strong>.
            Klik op de link in die mail, dan kun je beginnen met oefenen.
        </p>

        <StatusMessage v-if="page.props.status">{{ page.props.status }}</StatusMessage>

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <PrimaryButton type="submit" :disabled="form.processing">Stuur de mail opnieuw</PrimaryButton>
        </form>

        <p class="text-center text-[15px] text-ink-muted">
            Verkeerd account?
            <Link href="/logout" method="post" as="button" class="cursor-pointer font-semibold text-purple-700 underline hover:text-purple-800">
                Uitloggen
            </Link>
        </p>
    </AuthLayout>
</template>
