<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import StatusMessage from '@/Components/StatusMessage.vue'
import TextField from '@/Components/TextField.vue'

const props = defineProps({
    profile: { type: Object, required: true },
})

const page = usePage()

const details = useForm({
    name: props.profile.name,
    email: props.profile.email,
})

const password = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const saveDetails = () => details.patch('/profile', {
    preserveScroll: true,
})

const changePassword = () => password.put('/profile/password', {
    preserveScroll: true,
    onFinish: () => password.reset(),
})
</script>

<template>
    <AppLayout title="Profiel">
        <h1 class="font-display text-[44px] font-extrabold tracking-tight">Profiel</h1>

        <StatusMessage v-if="page.props.status">{{ page.props.status }}</StatusMessage>

        <div class="flex flex-wrap items-start gap-6">
            <aside class="flex flex-[1_1_300px] flex-col items-center gap-3 rounded-[22px] border border-line-soft bg-white px-6 py-7 text-center">
                <div class="flex size-24 items-center justify-center rounded-full bg-purple-600 font-display text-4xl font-extrabold text-white">
                    {{ profile.initials }}
                </div>
                <h2 class="mt-1 font-display text-[26px] font-extrabold">{{ profile.name }}</h2>
                <span v-if="profile.memberSince" class="text-[15px] text-ink-muted">Lid sinds {{ profile.memberSince }}</span>
                <div class="mt-2 flex w-full gap-2.5">
                    <div class="flex-1 rounded-[14px] bg-star-soft p-3">
                        <div class="font-display text-[28px] font-extrabold">{{ profile.stars }}</div>
                        <div class="text-sm text-ink-muted">sterren</div>
                    </div>
                    <div class="flex-1 rounded-[14px] bg-peach p-3">
                        <div class="font-display text-[22px] leading-[34px] font-extrabold">{{ profile.highestBadge ?? 'Nog geen' }}</div>
                        <div class="text-sm text-ink-muted">hoogste badge</div>
                    </div>
                </div>
            </aside>

            <div class="flex min-w-0 flex-[999_1_560px] flex-col gap-5">
                <form class="flex flex-col gap-4 rounded-[22px] border border-line-soft bg-white px-7 py-6" @submit.prevent="saveDetails">
                    <h2 class="font-display text-[22px] font-extrabold">Gegevens</h2>
                    <p class="-mt-2 text-[15px] text-ink-muted">Pas je e-mailadres aan? Dan sturen we een mail om het nieuwe adres te bevestigen.</p>
                    <div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-4">
                        <TextField v-model="details.name" label="Naam" autocomplete="name" required :error="details.errors.name" />
                        <TextField v-model="details.email" label="E-mailadres" type="email" autocomplete="email" required :error="details.errors.email" />
                    </div>
                    <div>
                        <PrimaryButton type="submit" :disabled="details.processing">Opslaan</PrimaryButton>
                    </div>
                </form>

                <form class="flex flex-col gap-4 rounded-[22px] border border-line-soft bg-white px-7 py-6" @submit.prevent="changePassword">
                    <h2 class="font-display text-[22px] font-extrabold">Wachtwoord wijzigen</h2>
                    <div class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-4">
                        <TextField v-model="password.current_password" label="Huidig wachtwoord" type="password" autocomplete="current-password" required :error="password.errors.current_password" />
                        <TextField v-model="password.password" label="Nieuw wachtwoord" type="password" autocomplete="new-password" required :error="password.errors.password" />
                        <TextField v-model="password.password_confirmation" label="Herhaal nieuw wachtwoord" type="password" autocomplete="new-password" required />
                    </div>
                    <div>
                        <button
                            type="submit"
                            :disabled="password.processing"
                            class="cursor-pointer rounded-[14px] border-2 border-ink bg-white px-[18px] py-2.5 text-base font-bold text-ink shadow-[0_4px_0_var(--color-ink)] transition focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-purple-600 active:translate-y-[2px] active:shadow-[0_2px_0_var(--color-ink)] disabled:cursor-wait disabled:opacity-70"
                        >
                            Wachtwoord wijzigen
                        </button>
                    </div>
                </form>

                <div class="flex flex-wrap items-center justify-between gap-3 px-1">
                    <span class="text-[15px] text-ink-muted">Account verwijderen? Vraag het je docent.</span>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="inline-flex cursor-pointer items-center gap-2 px-1 py-2.5 text-base font-bold text-purple-700 hover:text-purple-800"
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10" />
                        </svg>
                        Uitloggen
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
