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
                <div class="rf-discussion__head">
                    <h2 class="h3 mb-0" id="diskusi-heading">Diskusi</h2>
                    <span class="rf-muted rf-small rf-num">{{ $threads->total() }} thread</span>
                </div>

                @auth
                    <form class="rf-discussion__form" method="POST" action="{{ route('discussions.threads.store', $lesson) }}" novalidate>
                        @csrf

                        @error('thread_title')
                            <div class="rf-alert rf-alert--error" role="alert">
                                <x-icon name="alert-circle" />
                                <div class="rf-alert__body">
                                    <span class="rf-alert__kind">Tunggu sebentar</span>
                                    {{ $message }}
                                </div>
                            </div>
                        @enderror

                        <div class="rf-field">
                            <label class="rf-label" for="thread-title">Judul pertanyaan</label>
                            <input
                                class="rf-input @error('title') is-invalid @enderror"
                                id="thread-title"
                                name="title"
                                type="text"
                                minlength="5"
                                maxlength="120"
                                value="{{ old('title') }}"
                                placeholder="Contoh: Kapan kita memakai rumus GLBB tanpa waktu t?"
                                required
                                @error('title') aria-invalid="true" aria-describedby="thread-title-error" @enderror
                            >
                            <span class="rf-hint">5 sampai 120 karakter.</span>
                            @error('title')
                                <span class="rf-error" id="thread-title-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="rf-field">
                            <label class="rf-label" for="thread-body">Isi pertanyaan atau tanggapan</label>
                            <textarea
                                class="rf-textarea @error('body') is-invalid @enderror"
                                id="thread-body"
                                name="body"
                                rows="4"
                                maxlength="2000"
                                required
                                @error('body') aria-invalid="true" aria-describedby="thread-body-error" @enderror
                            >{{ old('body') }}</textarea>
                            <span class="rf-hint">Maksimal 2000 karakter. Teks polos, baris baru dipertahankan.</span>
                            @error('body')
                                <span class="rf-error" id="thread-body-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="rf-btn rf-btn--secondary">Kirim pertanyaan</button>
                        </div>
                    </form>
                @else
                    <div class="rf-tile rf-discussion__guest">
                        <p class="mb-2">Punya pertanyaan tentang <strong>{{ $lesson->title }}</strong>? Masuk dulu untuk menulis pertanyaan atau membalas diskusi temanmu.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="rf-btn rf-btn--secondary" href="{{ route('login') }}">Masuk untuk berdiskusi</a>
                            <a class="rf-btn rf-btn--tertiary" href="{{ route('register') }}">Buat akun</a>
                        </div>
                    </div>
                @endauth

                @error('reply_body')
                    <div class="rf-alert rf-alert--error mt-4" role="alert">
                        <x-icon name="alert-circle" />
                        <div class="rf-alert__body">
                            <span class="rf-alert__kind">Tunggu sebentar</span>
                            {{ $message }}
                        </div>
                    </div>
                @enderror

                @if ($threads->isEmpty())
                    <div class="rf-empty">
                        Belum ada diskusi. Jadi yang pertama bertanya.
                    </div>
                @else
                    <ul class="rf-thread-list">
                        @foreach ($threads as $thread)
                            <li class="rf-thread" id="thread-{{ $thread->id }}">
                                <div class="rf-thread__head">
                                    <div class="rf-thread__meta">
                                        <strong>{{ $thread->user?->name ?? 'Pelajar' }}</strong>
                                        <span class="rf-muted rf-small"><code>{{ $thread->user?->username }}</code></span>
                                        @if ($thread->user?->isAdmin())
                                            <span class="rf-tag">Admin</span>
                                        @endif
                                        <span class="rf-muted rf-small rf-num">· {{ $thread->created_at?->format('d M Y, H:i') }}</span>
                                    </div>

                                    @if ($thread->canBeDeletedBy(auth()->user()))
                                        <form method="POST" action="{{ route('discussions.threads.destroy', $thread) }}" data-confirm="Hapus diskusi ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rf-btn rf-btn--tertiary rf-thread__del">
                                                <x-icon name="trash" /> Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <h3 class="h4 rf-thread__title">{{ $thread->title }}</h3>
                                <div class="rf-thread__body">{!! nl2br(e($thread->body)) !!}</div>

                                @if ($thread->replies->isNotEmpty())
                                    <ul class="rf-replies" aria-label="Balasan untuk {{ $thread->title }}">
                                        @foreach ($thread->replies as $reply)
                                            <li class="rf-reply" id="reply-{{ $reply->id }}">
                                                <div class="rf-thread__head">
                                                    <div class="rf-thread__meta">
                                                        <strong>{{ $reply->user?->name ?? 'Pelajar' }}</strong>
                                                        <span class="rf-muted rf-small"><code>{{ $reply->user?->username }}</code></span>
                                                        @if ($reply->user?->isAdmin())
                                                            <span class="rf-tag">Admin</span>
                                                        @endif
                                                        <span class="rf-muted rf-small rf-num">· {{ $reply->created_at?->format('d M Y, H:i') }}</span>
                                                    </div>

                                                    @if ($reply->canBeDeletedBy(auth()->user()))
                                                        <form method="POST" action="{{ route('discussions.replies.destroy', $reply) }}" data-confirm="Hapus balasan ini?">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="rf-btn rf-btn--tertiary rf-thread__del">
                                                                <x-icon name="trash" /> Hapus
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>

                                                <div class="rf-thread__body">{!! nl2br(e($reply->body)) !!}</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @auth
                                    <form class="rf-reply-form" method="POST" action="{{ route('discussions.replies.store', $thread) }}" novalidate>
                                        @csrf
                                        <div class="rf-field">
                                            <label class="rf-label rf-small" for="reply-body-{{ $thread->id }}">Tulis balasan</label>
                                            <textarea
                                                class="rf-textarea rf-reply-form__input"
                                                id="reply-body-{{ $thread->id }}"
                                                name="body"
                                                rows="2"
                                                maxlength="1000"
                                                placeholder="Tulis balasan singkat (maksimal 1000 karakter)"
                                                required
                                            ></textarea>
                                        </div>
                                        <div>
                                            <button type="submit" class="rf-btn rf-btn--tertiary">Kirim balasan</button>
                                        </div>
                                    </form>
                                @endauth
                            </li>
                        @endforeach
                    </ul>

                    @include('admin.partials.pagination', ['paginator' => $threads])
                @endif
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
