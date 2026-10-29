<x-plain-layout title="Inloggen">
    <div class="mx-auto max-w-sm">
        <h1 class="mb-6 text-2xl font-semibold">Inloggen</h1>
        <form method="POST" action="{{ url('login') }}" class="space-y-4 rounded-xl border border-stone-200 bg-white p-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium">E-mailadres</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2">
                @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium">Wachtwoord</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2">
                @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Ingelogd blijven</label>
            <button type="submit" class="w-full rounded-lg bg-stone-900 px-4 py-2 font-medium text-white hover:bg-stone-700">Inloggen</button>
        </form>
        <p class="mt-4 text-center text-sm text-stone-600">Nog geen account? <a href="{{ route('register') }}" class="underline">Maak er een</a>.</p>
    </div>
</x-plain-layout>
