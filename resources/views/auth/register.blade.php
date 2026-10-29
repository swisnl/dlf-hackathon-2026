<x-auth-layout title="Account maken" active="register">
    <h2 class="font-display text-[34px] font-extrabold">Maak je account</h2>
    <form method="POST" action="{{ url('register') }}" class="flex flex-col gap-5">
        @csrf
        <div class="flex flex-col gap-2">
            <label for="name" class="label">Naam</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="field">
            <x-field-error name="name" />
        </div>
        <div class="flex flex-col gap-2">
            <label for="email" class="label">E-mailadres</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="field">
            <x-field-error name="email" />
        </div>
        <div class="flex flex-col gap-2">
            <label for="password" class="label">Wachtwoord</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="field">
            <x-field-error name="password" />
        </div>
        <div class="flex flex-col gap-2">
            <label for="password_confirmation" class="label">Wachtwoord nog een keer</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field">
        </div>
        <button type="submit" class="btn-primary w-full text-[17px]">Account maken</button>
    </form>
    <p class="text-center text-[15px] text-inkt-zacht">Heb je al een account? <a href="{{ route('login') }}" class="link">Log in</a></p>
</x-auth-layout>
