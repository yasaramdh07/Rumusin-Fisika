@extends('layouts.app')

@section('title', $lesson->title)

@section('content')
<div class="container rf-lesson">
    <nav class="rf-lesson__crumb" aria-label="Jejak halaman">
        <a href="{{ route('lessons.index') }}">Materi</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('lessons.index', ['kategori' => $lesson->category->slug]) }}">{{ $lesson->category->name }}</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">{{ $lesson->title }}</span>
    </nav>

    <header class="rf-lesson__header">
        <h1>{{ $lesson->title }}</h1>
        <p class="rf-prose rf-muted mb-0">{{ $lesson->summary }}</p>
    </header>

    <div class="rf-lesson__grid">
        <div class="rf-lesson__main">
            <article class="rf-slab rf-slab--fold rf-lesson__article" aria-label="Isi materi">
                <div class="rf-prose rf-lesson__prose" data-math-root>
                    {!! $bodyHtml !!}
                </div>
            </article>

            @if ($lesson->videos->isNotEmpty())
                @php $firstVideo = $lesson->videos->first(); @endphp
                <section class="rf-slab rf-lesson__videos" id="tahapan-video" aria-labelledby="video-heading">
                    <div class="rf-lesson__videos-head">
                        <h2 class="h3" id="video-heading">Tahapan video</h2>
                        <p class="rf-muted rf-small mb-0">Pilih tahapan di bawah untuk mengganti video.</p>
                    </div>

                    <div class="rf-lesson__videos-layout">
                        <ol class="rf-track rf-lesson__track" aria-label="Daftar tahapan video">
                            @foreach ($lesson->videos as $video)
                                @php $isFirst = $loop->first; @endphp
                                <li class="rf-track__item">
                                    <button
                                        type="button"
                                        class="rf-track__step @if ($isFirst) is-active @endif"
                                        data-video-step
                                        data-youtube-id="{{ $video->youtube_id }}"
                                        data-video-title="{{ $video->title }}"
                                        data-video-position="{{ $video->position }}"
                                        @if ($isFirst) aria-current="step" @endif
                                    >
                                        <span class="rf-track__dot">{{ $video->position }}</span>
                                        <span class="rf-track__text">
                                            <span>{{ $video->title }}</span>
                                            @if ($video->duration_seconds)
                                                <span class="rf-track__meta rf-num">{{ max(1, (int) round($video->duration_seconds / 60)) }} menit</span>
                                            @endif
                                        </span>
                                    </button>
                                </li>
                            @endforeach
                        </ol>

                        <div class="rf-lesson__player-wrap">
                            <div class="rf-lesson__player">
                                <iframe
                                    data-video-player
                                    src="https://www.youtube-nocookie.com/embed/{{ $firstVideo->youtube_id }}?rel=0"
                                    title="Video tahapan {{ $firstVideo->position }}: {{ $firstVideo->title }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                            <p class="rf-small rf-muted mt-2 mb-0" data-video-current-title>
                                Tahapan {{ $firstVideo->position }}: {{ $firstVideo->title }}
                            </p>
                        </div>
                    </div>
                </section>
            @endif

            <section class="rf-slab rf-lesson__discussion" id="diskusi" aria-labelledby="diskusi-heading">
                <h2 class="h3" id="diskusi-heading">Diskusi</h2>
                <div class="rf-empty">
                    Belum ada diskusi di materi ini. Jadi yang pertama bertanya.
                </div>
            </section>
        </div>

        @if ($formulasHtml !== '')
            <aside class="rf-lesson__aside" aria-labelledby="lembar-rumus-heading">
                <div class="rf-sheet rf-sheet--sticky rf-lesson__sheet">
                    <h2 class="rf-sheet__title" id="lembar-rumus-heading">Lembar Rumus</h2>
                    <div class="rf-sheet__paper" data-math-root>
                        {!! $formulasHtml !!}
                    </div>
                    <p class="rf-sheet__note">Simpan lembar ini di samping layar sambil membaca penurunan rumus.</p>
                </div>
            </aside>
        @endif
    </div>
</div>

{{-- Dock Ruka: disiapkan sebagai tempat kosong di kanan-bawah, diisi pada prompt Ruka --}}
<div class="rf-ruka-dock" id="ruka-dock" aria-label="Ruka asisten fisika">
    <a class="rf-ruka-dock__btn" href="#diskusi" title="Ruka siap ditanya tentang {{ $lesson->title }}">
        <x-ruka-avatar size="md" />
        <span class="rf-ruka-dock__label">Tanya Ruka</span>
    </a>
</div>
@endsection
