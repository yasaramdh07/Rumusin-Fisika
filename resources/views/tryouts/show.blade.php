@extends('layouts.app')

@section('title', $tryout->title)

@section('content')
<div class="container rf-tryout-detail">
    <x-pagehead>
        <x-slot:crumbs>
            <a href="{{ route('tryouts.index') }}">Try out</a>
            <span aria-hidden="true">›</span>
            <span aria-current="page">{{ $tryout->title }}</span>
        </x-slot:crumbs>
    </x-pagehead>

    <div class="rf-tryout-detail__grid">
        <section class="rf-slab rf-slab--fold rf-tryout-detail__main" aria-labelledby="tryout-heading">
            <h1 id="tryout-heading">{{ $tryout->title }}</h1>

            @if ($tryout->description)
                <p class="rf-prose rf-muted">{{ $tryout->description }}</p>
            @endif

            <dl class="rf-tryout-detail__meta">
                <div>
                    <dt>Jumlah soal</dt>
                    <dd class="rf-num">{{ $tryout->questions_count }} soal pilihan ganda (A–D)</dd>
                </div>
                <div>
                    <dt>Durasi pengerjaan</dt>
                    <dd class="rf-num">{{ $tryout->duration_minutes }} menit</dd>
                </div>
                <div>
                    <dt>Materi terkait</dt>
                    <dd>
                        @if ($tryout->lesson)
                            <a href="{{ route('lessons.show', $tryout->lesson) }}">{{ $tryout->lesson->title }}</a>
                        @else
                            Gabungan
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Sisa kuota hari ini</dt>
                    <dd class="rf-num">{{ $remainingToday }} dari {{ $dailyLimit }} percobaan</dd>
                </div>
            </dl>

            @error('tryout')
                <div class="rf-alert rf-alert--error mb-4" role="alert">
                    <x-icon name="alert-circle" />
                    <div class="rf-alert__body">
                        <span class="rf-alert__kind">Tidak bisa memulai</span>
                        {{ $message }}
                    </div>
                </div>
            @enderror

            @if ($unfinishedAttempt)
                <div class="rf-alert rf-alert--info mb-4" role="status">
                    <x-icon name="info" />
                    <div class="rf-alert__body">
                        <span class="rf-alert__kind">Percobaan belum selesai</span>
                        Kamu masih punya percobaan yang sedang berjalan sejak {{ $unfinishedAttempt->started_at->format('H:i') }} WIB.
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('tryouts.start', $tryout) }}">
                @csrf
                @if ($unfinishedAttempt)
                    <button type="submit" class="rf-btn rf-btn--primary">Lanjutkan try out</button>
                @elseif ($remainingToday > 0)
                    <button type="submit" class="rf-btn rf-btn--primary">Mulai try out</button>
                @else
                    <button type="button" class="rf-btn rf-btn--primary" disabled>Kuota hari ini habis</button>
                @endif
            </form>
        </section>

        <aside class="rf-slab rf-tryout-detail__history" aria-labelledby="riwayat-tryout-heading">
            <div class="d-flex flex-wrap align-items-baseline justify-content-between gap-2 mb-3">
                <h2 class="h4 mb-0" id="riwayat-tryout-heading">Riwayatmu di paket ini</h2>
                @if ($bestScore !== null)
                    <span class="rf-tag rf-num">Terbaik: {{ $bestScore }}/100</span>
                @endif
            </div>

            @if ($attempts->isEmpty())
                <div class="rf-empty">Kamu belum pernah mengerjakan paket try out ini.</div>
            @else
                <ul class="rf-tryout-history-list">
                    @foreach ($attempts as $item)
                        <li class="rf-tryout-history-list__item">
                            <div>
                                @if ($item->finished_at)
                                    <strong class="rf-num">Skor {{ $item->score }}</strong>
                                    <span class="rf-muted rf-small rf-num">({{ $item->correct_count }}/{{ $item->total_questions }} benar)</span>
                                    @if ($item->is_late)
                                        <span class="rf-tag">Terlambat</span>
                                    @endif
                                    <div class="rf-muted rf-small rf-num">{{ $item->finished_at->format('d M Y, H:i') }}</div>
                                @else
                                    <strong>Sedang dikerjakan</strong>
                                    <div class="rf-muted rf-small rf-num">Mulai {{ $item->started_at->format('d M Y, H:i') }}</div>
                                @endif
                            </div>
                            <div>
                                @if ($item->finished_at)
                                    <a class="rf-btn rf-btn--tertiary" href="{{ route('tryouts.result', $item) }}">Lihat hasil</a>
                                @else
                                    <a class="rf-btn rf-btn--secondary" href="{{ route('tryouts.attempt', $item) }}">Lanjutkan</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </div>
</div>
@endsection
