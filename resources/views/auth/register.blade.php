<x-layout title="Account maken">
    <div class="mx-auto max-w-sm">
        <h1 class="mb-6 text-2xl font-semibold">Account maken</h1>
        <form method="POST" action="{{ url('register') }}" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium">Naam</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2">
                <x-field-error name="name" />
            </div>
            <div>
                <label for="email" class="block text-sm font-medium">E-mailadres</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2">
                <x-field-error name="email" />
            </div>
            <div>
                <label for="password" class="block text-sm font-medium">Wachtwoord</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2">
                <x-field-error name="password" />
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium">Wachtwoord nog een keer</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2">
            </div>
            <button type="submit" class="w-full rounded-lg bg-stone-900 px-4 py-2 font-medium text-white hover:bg-stone-700">Account maken</button>
        </form>
        <p class="mt-4 text-center text-sm text-stone-600">Heb je al een account? <a href="{{ route('login') }}" class="underline">Log in</a>.</p>
    </div>
</x-layout>
