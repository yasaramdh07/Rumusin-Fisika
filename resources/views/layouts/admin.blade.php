<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <a class="rf-skip" href="#konten">Lewati ke konten</a>

    <div class="container-xl rf-admin">
        <x-pagehead :title=" trim($__env->yieldContent('heading')) " :homeUrl="url('/admin')">
            @yield('actions')
        </x-pagehead>

        <main class="rf-admin__main" id="konten" tabindex="-1">
            @yield('content')
        </main>
    </div>

    <footer class="rf-footer">
        <div class="container-xl rf-footer__inner">
            <p class="mb-0">Dikembangkan oleh <a href="https://ilyasaramadhany.my.id" target="_blank" rel="noopener noreferrer">Ilyasa Ramadhany</a></p>
            <p class="mb-0">Panel Admin Rumusin Fisika</p>
        </div>
    </footer>

    @include('layouts.partials.toasts')

    <x-bottombar context="admin" />
</body>
</html>
