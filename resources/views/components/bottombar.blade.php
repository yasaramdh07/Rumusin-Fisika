@props([
    'context' => null,
    'static' => false,
    'forceActive' => null,
    'openMore' => false,
])

@php
    // Tentukan konteks (guest / user / admin) sesuai DESIGN-UPDATE §3:
    // - /masuk dan /daftar selalu memakai set Tamu.
    // - Admin di halaman publik memakai set User.
    // - Halaman /admin* memakai set Admin.
    $resolvedContext = $context;
    if (! in_array($resolvedContext, ['guest', 'user', 'admin'], true)) {
        if (request()->is('masuk') || request()->is('daftar')) {
            $resolvedContext = 'guest';
        } elseif (request()->is('admin*') && auth()->check() && auth()->user()->isAdmin()) {
            $resolvedContext = 'admin';
        } elseif (auth()->check()) {
            $resolvedContext = 'user';
        } else {
            $resolvedContext = 'guest';
        }
    }

    $isHomeActive = $forceActive === 'beranda' || (! $forceActive && (request()->is('/') || request()->is('beranda')));
    $isLessonsActive = $forceActive === 'materi' || (! $forceActive && request()->is('materi*'));
    $isTryoutsActive = $forceActive === 'tryout' || (! $forceActive && request()->is('tryout*'));
    $isLeaderboardActive = $forceActive === 'leaderboard' || (! $forceActive && request()->is('leaderboard*'));
    $isLoginActive = $forceActive === 'masuk' || (! $forceActive && (request()->is('masuk') || request()->is('daftar')));
    $isProfileActive = $forceActive === 'profil' || (! $forceActive && request()->is('profil*'));

    $isAdminDashboardActive = $forceActive === 'dasbor' || (! $forceActive && request()->is('admin'));
    $isAdminLessonsActive = $forceActive === 'admin-materi' || (! $forceActive && request()->is('admin/materi*'));
    $isAdminQuestionsActive = $forceActive === 'admin-soal' || (! $forceActive && request()->is('admin/soal*'));
    $isAdminTryoutsActive = $forceActive === 'admin-tryout' || (! $forceActive && request()->is('admin/tryout*'));
    $isAdminMoreActive = $forceActive === 'lainnya' || (! $forceActive && (
        request()->is('admin/kategori*')
        || request()->is('admin/diskusi*')
        || request()->is('admin/pengguna*')
    ));
@endphp

<nav
    {{ $attributes->class(['rf-bottombar', 'rf-bottombar--static' => $static]) }}
    aria-label="{{ $static ? 'Contoh navigasi '.$resolvedContext : 'Navigasi utama' }}"
    data-bottombar="{{ $resolvedContext }}"
>
    <ul class="rf-bottombar__list">
        @if ($resolvedContext === 'admin')
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isAdminDashboardActive) is-active @endif"
                   href="{{ url('/admin') }}"
                   @if ($isAdminDashboardActive) aria-current="page" @endif>
                    <x-icon name="grid" />
                    <span class="rf-bottombar__label">Dasbor</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isAdminLessonsActive) is-active @endif"
                   href="{{ url('/admin/materi') }}"
                   @if ($isAdminLessonsActive) aria-current="page" @endif>
                    <x-icon name="book" />
                    <span class="rf-bottombar__label">Materi</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isAdminQuestionsActive) is-active @endif"
                   href="{{ url('/admin/soal') }}"
                   @if ($isAdminQuestionsActive) aria-current="page" @endif>
                    <x-icon name="list" />
                    <span class="rf-bottombar__label">Soal</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isAdminTryoutsActive) is-active @endif"
                   href="{{ url('/admin/tryout') }}"
                   @if ($isAdminTryoutsActive) aria-current="page" @endif>
                    <x-icon name="clipboard" />
                    <span class="rf-bottombar__label">Try out</span>
                </a>
            </li>
            <li class="rf-bottombar__cell" data-more-container>
                <button
                    type="button"
                    class="rf-bottombar__item @if ($isAdminMoreActive) is-active @endif"
                    data-more-toggle
                    aria-expanded="{{ $openMore ? 'true' : 'false' }}"
                    aria-haspopup="true"
                    @if ($isAdminMoreActive) aria-current="page" @endif
                >
                    <x-icon name="more" />
                    <span class="rf-bottombar__label">Lainnya</span>
                </button>

                <div class="rf-more-panel" data-more-panel @if (! $openMore) hidden @endif>
                    <ul class="rf-more-panel__list">
                        <li>
                            <a class="rf-more-panel__link @if (request()->is('admin/kategori*')) is-active @endif"
                               href="{{ url('/admin/kategori') }}"
                               @if (request()->is('admin/kategori*')) aria-current="page" @endif>
                                <x-icon name="tag" /> Kategori
                            </a>
                        </li>
                        <li>
                            <a class="rf-more-panel__link @if (request()->is('admin/diskusi*')) is-active @endif"
                               href="{{ url('/admin/diskusi') }}"
                               @if (request()->is('admin/diskusi*')) aria-current="page" @endif>
                                <x-icon name="message" /> Diskusi
                            </a>
                        </li>
                        <li>
                            <a class="rf-more-panel__link @if (request()->is('admin/pengguna*')) is-active @endif"
                               href="{{ url('/admin/pengguna') }}"
                               @if (request()->is('admin/pengguna*')) aria-current="page" @endif>
                                <x-icon name="user" /> Pengguna
                            </a>
                        </li>
                        <li class="rf-more-panel__divider" role="separator"></li>
                        <li>
                            <a class="rf-more-panel__link" href="{{ url('/') }}">
                                <x-icon name="home" /> Kembali ke situs
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        @elseif ($resolvedContext === 'user')
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isHomeActive) is-active @endif"
                   href="{{ route('home') }}"
                   @if ($isHomeActive) aria-current="page" @endif>
                    <x-icon name="home" />
                    <span class="rf-bottombar__label">Beranda</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isLessonsActive) is-active @endif"
                   href="{{ url('/materi') }}"
                   @if ($isLessonsActive) aria-current="page" @endif>
                    <x-icon name="book" />
                    <span class="rf-bottombar__label">Materi</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isTryoutsActive) is-active @endif"
                   href="{{ url('/tryout') }}"
                   @if ($isTryoutsActive) aria-current="page" @endif>
                    <x-icon name="clipboard" />
                    <span class="rf-bottombar__label">Try out</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isLeaderboardActive) is-active @endif"
                   href="{{ url('/leaderboard') }}"
                   @if ($isLeaderboardActive) aria-current="page" @endif>
                    <x-icon name="trophy" />
                    <span class="rf-bottombar__label">Leaderboard</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isProfileActive) is-active @endif"
                   href="{{ route('profile.show') }}"
                   @if ($isProfileActive) aria-current="page" @endif>
                    <x-icon name="user" />
                    <span class="rf-bottombar__label">Profil</span>
                </a>
            </li>
        @else
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isHomeActive) is-active @endif"
                   href="{{ url('/') }}"
                   @if ($isHomeActive) aria-current="page" @endif>
                    <x-icon name="home" />
                    <span class="rf-bottombar__label">Beranda</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isLessonsActive) is-active @endif"
                   href="{{ url('/materi') }}"
                   @if ($isLessonsActive) aria-current="page" @endif>
                    <x-icon name="book" />
                    <span class="rf-bottombar__label">Materi</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isTryoutsActive) is-active @endif"
                   href="{{ url('/tryout') }}"
                   @if ($isTryoutsActive) aria-current="page" @endif>
                    <x-icon name="clipboard" />
                    <span class="rf-bottombar__label">Try out</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isLeaderboardActive) is-active @endif"
                   href="{{ url('/leaderboard') }}"
                   @if ($isLeaderboardActive) aria-current="page" @endif>
                    <x-icon name="trophy" />
                    <span class="rf-bottombar__label">Leaderboard</span>
                </a>
            </li>
            <li class="rf-bottombar__cell">
                <a class="rf-bottombar__item @if ($isLoginActive) is-active @endif"
                   href="{{ route('login') }}"
                   @if ($isLoginActive) aria-current="page" @endif>
                    <x-icon name="login" />
                    <span class="rf-bottombar__label">Masuk</span>
                </a>
            </li>
        @endif
    </ul>
</nav>
