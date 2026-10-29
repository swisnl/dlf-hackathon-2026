@props(['size' => 36, 'tile' => '#9333EA', 'eyes' => '#FFFFFF', 'label' => null])
{{-- The Eigenwijs owl, looking sceptically to the side. --}}
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 48 48" {{ $attributes }} @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif>
    @if ($tile)<rect width="48" height="48" rx="14" fill="{{ $tile }}"></rect>@endif
    <circle cx="16" cy="23" r="8.5" fill="{{ $eyes }}"></circle><circle cx="32" cy="23" r="8.5" fill="{{ $eyes }}"></circle>
    <circle cx="19" cy="24" r="4" fill="#1D1A2F"></circle><circle cx="35" cy="24" r="4" fill="#1D1A2F"></circle>
    <path d="M8 12.5 L22 13" stroke="#1D1A2F" stroke-width="2.6" stroke-linecap="round"></path><path d="M26 13 L40 9.5" stroke="#1D1A2F" stroke-width="2.6" stroke-linecap="round"></path>
    <path d="M21 33 L27 33 L24 38 Z" fill="#FFB800"></path>
</svg>
