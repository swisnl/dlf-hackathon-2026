@props(['name'])
@error($name)
    <p {{ $attributes->merge(['class' => 'text-sm font-semibold text-gemist-tekst']) }}>{{ $message }}</p>
@enderror
