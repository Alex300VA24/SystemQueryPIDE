<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Sistema de Consultas PIDE' }}</title>
        <link rel="stylesheet" href="{{ asset('assets/css/fonts.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/fontawesome/css/all.min.css') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-ink">
        <script>window.PIDE_LOGIN_URL = "{{ route('login') }}";</script>
        <a href="#contenido" class="skip-link">Saltar al contenido</a>
        {{ $slot }}
    </body>
</html>
