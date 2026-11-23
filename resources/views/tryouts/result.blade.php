@extends('layouts.app')

@section('title', 'Hasil '.$tryout->title)

@section('content')
<div class="container rf-tryout-result">
    <nav class="rf-lesson__crumb" aria-label="Jejak halaman">
        <a href="{{ route('tryouts.index') }}">Try out</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('tryouts.show', $tryout) }}">{{ $tryout->title }}</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">Hasil</span>
    </nav>

    <section class="rf-slab rf-slab--fold rf-tryout-result__hero" aria-labelledby="hasil-heading">
        <div>
            <h1 id="hasil-heading">Hasil {{ $tryout->title }}</h1>
            <p class="rf-muted mb-3">
                Selesai pada {{ $attempt->finished_at->format('d M Y, H:i') }} WIB
                @if ($attempt->is_late)
                    · <span class="rf-tag">Terlambat</span>
                @endif
            </p>

            @if ($attempt->is_late)
                <div class="rf-alert rf-alert--info mb-4" role="status">
                    <x-icon name="info" />
                    <div class="rf-alert__body">
                        <span class="rf-alert__kind">Melewati batas waktu</span>
                        Jawabanmu tetap dinilai penuh, namun percobaan ini ditandai <strong>Terlambat</strong> karena dikirim melewati durasi {{ $tryout->duration_minutes }} menit.
                    </div>
                </div>
            @endif

            <dl class="rf-tryout-result__stats">
                <div>
                    <dt>Jawaban benar</dt>
                    <dd class="rf-num">{{ $attempt->correct_count }} dari {{ $attempt->total_questions }} soal</dd>
                </div>
                <div>
                    <dt>Durasi pengerjaan</dt>
                    <dd class="rf-num">
                        {{ intdiv((int) $attempt->duration_seconds, 60) }} menit {{ ((int) $attempt->duration_seconds) % 60 }} detik
                    </dd>
                </div>
            </dl>

            <div class="d-flex flex-wrap gap-3 mt-4">
                <a class="rf-btn rf-btn--primary" href="{{ route('tryouts.show', $tryout) }}">Kembali ke paket try out</a>
                <a class="rf-btn rf-btn--tertiary" href="{{ url('/leaderboard') }}">Lihat leaderboard</a>
                <a class="rf-btn rf-btn--tertiary" href="{{ route('profile.show') }}">Lihat riwayat di profil</a>
            </div>
        </div>

        <div class="rf-tryout-result__score-box" aria-label="Skor akhir">
            <span class="rf-tryout-result__score-label">Skor akhir</span>
            <strong class="rf-tryout-result__score-value rf-num">{{ $attempt->score }}</strong>
            <span class="rf-muted rf-small">dari 100</span>
        </div>
    </section>

    <section class="rf-tryout-result__review" aria-labelledby="tinjauan-heading">
        <h2 class="h3 mb-4" id="tinjauan-heading">Peninjauan per soal</h2>

        <div class="rf-tryout-work__questions">
            @foreach ($reviewItems as $item)
                <article class="rf-slab rf-tryout-q" aria-labelledby="review-soal-{{ $item['number'] }}">
                    <div class="rf-tryout-q__head">
                        <h3 class="h4 mb-0" id="review-soal-{{ $item['number'] }}">
                            Soal {{ $item['number'] }}
                        </h3>

                        @if ($item['is_correct'])
                            <span class="rf-verdict rf-verdict--ok">
                                <x-icon name="check-circle" /> Benar
                            </span>
                        @else
                            <span class="rf-verdict rf-verdict--wrong">
                                <x-icon name="alert-circle" /> Salah
                            </span>
                        @endif
                    </div>

                    <div class="rf-prose rf-lesson__prose rf-tryout-q__body" data-math-root>
                        {!! $item['body_html'] !!}
                    </div>

                    <div class="rf-tryout-options" role="list" aria-label="Opsi soal {{ $item['number'] }}">
                        @foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $optKey => $optLetter)
                            @php
                                $isUserChoice = $item['selected_option'] === $optKey;
                                $isCorrectChoice = $item['correct_option'] === $optKey;
                            @endphp
                            <div
                                role="listitem"
                                class="rf-option rf-option--static @if ($isCorrectChoice) is-correct @elseif ($isUserChoice) is-wrong @endif"
                            >
                                <span class="rf-option__letter" aria-hidden="true">{{ $optLetter }}</span>
                                <span class="rf-option__text" data-math-root>{!! $item['options'][$optKey] !!}</span>

                                <span class="rf-option__badges">
                                    @if ($isCorrectChoice)
                                        <span class="rf-verdict rf-verdict--ok">
                                            <x-icon name="check-circle" /> Jawaban benar
                                        </span>
                                    @endif
                                    @if ($isUserChoice && ! $isCorrectChoice)
                                        <span class="rf-verdict rf-verdict--wrong">
                                            <x-icon name="alert-circle" /> Jawabanmu (Salah)
                                        </span>
                                    @elseif ($isUserChoice && $isCorrectChoice)
                                        <span class="rf-tag">Jawabanmu</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>

                    @if ($item['selected_option'] === null)
                        <p class="rf-error mt-3 mb-0">
                            <x-icon name="alert-circle" /> Kamu belum memilih jawaban untuk soal ini. Jawaban benar: Pilihan {{ strtoupper($item['correct_option']) }}.
                        </p>
                    @endif

                    <div class="rf-tryout-explanation mt-4">
                        <h4 class="h5 mb-2">Pembahasan</h4>
                        <div class="rf-prose rf-lesson__prose" data-math-root>
                            {!! $item['explanation_html'] !!}
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
