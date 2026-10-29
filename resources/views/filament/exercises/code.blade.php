{{-- One class with line numbers; $marked holds the errors keyed by line number. --}}
<div class="overflow-hidden rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
    <div class="bg-gray-50 px-3 py-2 text-sm font-medium dark:bg-white/5">{{ $label }}</div>
    <ol class="overflow-x-auto py-2 font-mono text-xs leading-6">
        @foreach (explode("\n", $code) as $index => $line)
            @php($error = $marked->get($index + 1))
            <li @class(['flex gap-3 px-3', 'bg-danger-50 dark:bg-danger-400/10' => $error])>
                <span class="w-6 shrink-0 select-none text-end text-gray-400">{{ $index + 1 }}</span>
                <span class="whitespace-pre">{{ $line }}</span>
                @if ($error)
                    <span class="ms-auto max-w-56 truncate ps-3 font-sans text-danger-600 dark:text-danger-400" title="{{ $error->title }}">{{ $error->title }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</div>
