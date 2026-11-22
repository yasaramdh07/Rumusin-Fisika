<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <a class="rf-skip" href="#konten">Lewati ke konten</a>

    <header class="rf-navbar">
        <nav aria-label="Menu utama">
            <div class="rf-navbar__bar">
                <a class="rf-navbar__brand" href="{{ url('/') }}" aria-label="Rumusin, ke beranda">
                    <img src="{{ asset('images/logo-rumusin.png') }}" alt="Rumusin" width="41" height="44">
                </a>

                <button class="rf-btn rf-btn--tertiary rf-navbar__toggle" type="button"
                        data-bs-toggle="collapse" data-bs-target="#rf-nav"
                        aria-controls="rf-nav" aria-expanded="false" aria-label="Buka menu">
                    <x-icon name="menu" /> Menu
                </button>

                <div class="collapse rf-navbar__panel" id="rf-nav">
                    <ul class="rf-navbar__menu">
                        @auth
                            @if (auth()->user()->isAdmin())
                                <li><a class="rf-navbar__link" href="{{ route('admin.dashboard') }}" @if (request()->is('admin*')) aria-current="page" @endif>Admin</a></li>
                            @else
                                <li><a class="rf-navbar__link" href="{{ route('home') }}" @if (request()->is('beranda')) aria-current="page" @endif>Beranda</a></li>
                            @endif
                        @endauth
                        <li><a class="rf-navbar__link" href="{{ url('/materi') }}" @if (request()->is('materi*')) aria-current="page" @endif>Materi</a></li>
                        <li><a class="rf-navbar__link" href="{{ url('/tryout') }}" @if (request()->is('tryout*')) aria-current="page" @endif>Try out</a></li>
                        <li><a class="rf-navbar__link" href="{{ url('/leaderboard') }}" @if (request()->is('leaderboard*')) aria-current="page" @endif>Leaderboard</a></li>
                    </ul>

                    <ul class="rf-navbar__account">
                        @auth
                            <li>
                                <a class="rf-navbar__user" href="{{ route('profile.show') }}" @if (request()->is('profil*')) aria-current="page" @endif>
                                    <x-ruka-avatar size="sm" />
                                    <span>{{ auth()->user()->name }}</span>
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="rf-navbar__link"><x-icon name="logout" /> Keluar</button>
                                </form>
                            </li>
                        @else
                            <li><a class="rf-navbar__link" href="{{ route('login') }}" @if (request()->is('masuk')) aria-current="page" @endif>Masuk</a></li>
                            <li><a class="rf-btn rf-btn--secondary" href="{{ route('register') }}" @if (request()->is('daftar')) aria-current="page" @endif>Buat akun</a></li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
    </header>

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
</body>
</html>