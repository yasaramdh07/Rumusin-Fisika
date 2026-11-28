<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <a class="rf-skip" href="#konten">Lewati ke konten</a>

    <main id="konten" tabindex="-1">
        @yield('content')
    </main>

    <footer class="rf-footer">
        <div class="container rf-footer__inner">
            <p class="mb-0">Dikembangkan oleh <a href="https://ilyasaramadhany.my.id" target="_blank" rel="noopener noreferrer">Ilyasa Ramadhany</a></p>
            <p class="mb-0">Rumusin Fisika, versi prototype</p>
        </div>
    </footer>

    @include('layouts.partials.toasts')

    @unless (trim($__env->yieldContent('hide_bottombar')) !== '')
        <x-bottombar />
    @endunless
</body>
</html>
