@props(['name'])
@error($name)
    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
@enderror
