<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <a class="rf-skip" href="#konten">Lewati ke konten</a>

    @php
        $items = [
            ['/admin', 'Dasbor', 'grid', 'admin'],
            ['/admin/kategori', 'Kategori', 'tag', 'admin/kategori*'],
            ['/admin/materi', 'Materi', 'book', 'admin/materi*'],
            ['/admin/soal', 'Soal', 'list', 'admin/soal*'],
            ['/admin/tryout', 'Try out', 'clipboard', 'admin/tryout*'],
            ['/admin/diskusi', 'Diskusi', 'message', 'admin/diskusi*'],
            ['/admin/pengguna', 'Pengguna', 'user', 'admin/pengguna*'],
        ];
    @endphp

    <div class="container-xl rf-admin">
        <aside class="rf-admin__side">
            <a class="rf-admin__brand" href="{{ url('/admin') }}">
                <img src="{{ asset('images/logo-rumusin.png') }}" alt="" width="37" height="40">
                <span>Admin</span>
            </a>
            <nav aria-label="Menu admin">
                <ul class="rf-admin__nav">
                    @foreach ($items as [$href, $label, $icon, $pattern])
                        <li>
                            <a class="rf-admin__link" href="{{ url($href) }}" @if (request()->is($pattern)) aria-current="page" @endif>
                                <x-icon :name="$icon" /> {{ $label }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a class="rf-admin__link" href="{{ url('/') }}"><x-icon name="logout" /> Ke situs</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="rf-admin__main" id="konten" tabindex="-1">
            <div class="rf-admin__head">
                <h1>@yield('heading')</h1>
                @yield('actions')
            </div>
            @yield('content')
        </main>
    </div>

    @include('layouts.partials.toasts')
</body>
</html>