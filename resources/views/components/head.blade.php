@props(['title' => null])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#9333EA">
<title>{{ $title ? $title.' · ' : '' }}Eigenwijs</title>
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
