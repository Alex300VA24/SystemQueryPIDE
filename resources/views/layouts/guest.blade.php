<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login - Sistema PIDE</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="icon" href="{{ asset('assets/images/logo_pide.png') }}" type="image/png">
        <link rel="stylesheet" href="{{ asset('assets/css/fonts.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    </head>
    <body>
        <main aria-label="Acceso al Sistema PIDE">{{ $slot }}</main>
    </body>
</html>
