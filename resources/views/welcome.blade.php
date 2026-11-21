<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main class="container py-5">
        <img src="{{ asset('images/logo-rumusin.png') }}" alt="Logo Rumusin" width="96">
        <h1 class="mt-4">{{ config('app.name') }}</h1>
        <p>Fondasi aplikasi siap. Fitur menyusul di prompt berikutnya.</p>
    </main>
</body>
</html>
