<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@hasSection('title')@yield('title') | @endif{{ config('app.name') }}</title>
<link rel="icon" href="{{ asset('favicon.ico') }}">
@vite(['resources/scss/app.scss', 'resources/js/app.js'])