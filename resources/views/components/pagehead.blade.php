@props([
    'title' => null,
    'subtitle' => null,
    'homeUrl' => null,
])

@php
    $brandHref = $homeUrl ?? (auth()->check() ? route('home') : url('/'));
@endphp

<header {{ $attributes->class(['rf-pagehead']) }} data-pagehead>
    <div class="rf-pagehead__left">
        <a class="rf-pagehead__brand" href="{{ $brandHref }}" aria-label="Rumusin, ke beranda">
            <img src="{{ asset('images/logo-rumusin.png') }}" alt="Rumusin" width="38" height="40">
        </a>

        <div class="rf-pagehead__titles">
            @isset($crumbs)
                <nav class="rf-pagehead__crumb" aria-label="Jejak halaman">
                    {{ $crumbs }}
                </nav>
            @endisset

            @if ($title)
                <h1 class="rf-pagehead__heading">{{ $title }}</h1>
            @endif

            @if ($subtitle)
                <p class="rf-pagehead__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if (! $slot->isEmpty())
        <div class="rf-pagehead__actions">
            {{ $slot }}
        </div>
    @endif
</header>
