<x-auth-layout title="Inloggen" active="login">
    <h2 class="font-display text-[34px] font-extrabold">Welkom terug</h2>
    <form method="POST" action="{{ url('login') }}" class="flex flex-col gap-5">
        @csrf
        <div class="flex flex-col gap-2">
            <label for="email" class="label">E-mailadres</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field">
            <x-field-error name="email" />
        </div>
        <div class="flex flex-col gap-2">
            <label for="password" class="label">Wachtwoord</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="field">
            <x-field-error name="password" />
        </div>
        <label class="flex items-center gap-2.5 text-[15px]"><input type="checkbox" name="remember" value="1" class="size-[18px] accent-paars"> Ingelogd blijven</label>
        <button type="submit" class="btn-primary w-full text-[17px]">Inloggen</button>
    </form>
    <p class="text-center text-[15px] text-inkt-zacht">Nog geen account? <a href="{{ route('register') }}" class="link">Maak er een aan</a></p>
</x-auth-layout>
