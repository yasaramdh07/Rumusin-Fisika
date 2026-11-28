@extends('layouts.app')

@section('title', 'Leaderboard')

@php
    $currentUserId = (int) auth()->id();
    $periods = [
        \App\Services\LeaderboardService::PERIOD_WEEKLY => 'Mingguan',
        \App\Services\LeaderboardService::PERIOD_MONTHLY => 'Bulanan',
        \App\Services\LeaderboardService::PERIOD_ALL_TIME => 'Semua waktu',
    ];
    $podiumEntries = $topTen->take(3);
    $restEntries = $topTen->slice(3);
    $formatDuration = fn (int $seconds): string => sprintf('%dm %02dd', intdiv($seconds, 60), $seconds % 60);
@endphp

@section('content')
<div class="container rf-lb">
    <x-pagehead title="Leaderboard" />

    <header class="rf-lb__head">
        <div>
            <p class="rf-prose rf-muted mb-0">
                Peringkat dihitung dari satu percobaan terbaik tiap pelajar dalam periode terpilih (skor tertinggi, lalu durasi tercepat, lalu selesai paling awal).
            </p>
        </div>
    </header>

    <div class="rf-lb__controls">
        <nav class="rf-chips" aria-label="Pilih periode peringkat">
            @foreach ($periods as $key => $label)
                @php
                    $isActive = $period === $key;
                    $query = array_filter([
                        'periode' => $key,
                        'tryout' => $selectedTryout?->slug,
                    ]);
                @endphp
                <a
                    class="rf-chip @if ($isActive) is-active @endif"
                    href="{{ route('leaderboard.index', $query) }}"
                    @if ($isActive) aria-current="true" @endif
                >
                    <x-icon class="rf-chip__check" name="check" /> {{ $label }}
                </a>
            @endforeach
        </nav>

        @if ($tryouts->isNotEmpty())
            <form class="rf-lb__picker" method="GET" action="{{ route('leaderboard.index') }}">
                <input type="hidden" name="periode" value="{{ $period }}">
                <label class="rf-label" for="tryout-select">Paket try out</label>
                <div class="rf-lb__picker-row">
                    <select class="rf-select" id="tryout-select" name="tryout" onchange="this.form.submit()">
                        @foreach ($tryouts as $item)
                            <option value="{{ $item->slug }}" @selected($selectedTryout && $selectedTryout->id === $item->id)>
                                {{ $item->title }} @if (! $item->is_active) (Arsip) @endif
                            </option>
                        @endforeach
                    </select>
                    <noscript>
                        <button type="submit" class="rf-btn rf-btn--tertiary">Tampilkan</button>
                    </noscript>
                </div>
            </form>
        @endif
    </div>

    @if (! $selectedTryout)
        <div class="rf-slab">
            <div class="rf-empty">Belum ada paket try out yang tersedia.</div>
        </div>
    @else
        <div class="rf-lb__meta rf-small rf-muted">
            <span>Paket: <strong>{{ $selectedTryout->title }}</strong></span>
            <span>·</span>
            <span class="rf-num">
                @if ($periodStart && $periodEnd)
                    Periode {{ $periodStart->format('d M Y') }} – {{ $periodEnd->format('d M Y') }} WIB
                @else
                    Semua waktu
                @endif
            </span>
            <span>·</span>
            <span class="rf-num">{{ $totalParticipants }} peserta</span>
        </div>

        @if ($topTen->isEmpty())
            <div class="rf-slab">
                <div class="rf-empty">
                    Belum ada percobaan selesai pada periode ini.
                    @if ($selectedTryout->isAvailable())
                        <a href="{{ route('tryouts.show', $selectedTryout) }}">Mulai try out sekarang</a> untuk menjadi peringkat pertama.
                    @endif
                </div>
            </div>
        @else
            {{-- Podium asimetris 1-3 (DESIGN §5.4: juara 1 paling tinggi, tidak simetris kiri-kanan) --}}
            <section class="rf-podium" aria-label="Tiga peringkat teratas">
                @foreach ($podiumEntries as $entry)
                    @php
                        $rank = $entry['rank'];
                        $isMe = $entry['user_id'] === $currentUserId;
                    @endphp
                    <article class="rf-slab rf-podium__card rf-podium__card--rank-{{ $rank }} @if ($isMe) is-me @endif">
                        <div class="rf-podium__top">
                            <span class="rf-rank-badge rf-rank-badge--{{ $rank }}" aria-label="Peringkat {{ $rank }}">
                                #{{ $rank }}
                            </span>
                            @if ($isMe)
                                <span class="rf-tag rf-lb-you">Kamu</span>
                            @endif
                        </div>

                        <div class="rf-podium__identity">
                            <h2 class="h4 mb-0">{{ $entry['name'] }}</h2>
                            <span class="rf-muted rf-small"><code>{{ $entry['username'] }}</code></span>
                        </div>

                        <div class="rf-podium__metrics">
                            <div class="rf-podium__score">
                                <span class="rf-muted rf-small">Skor</span>
                                <strong class="rf-podium__score-num rf-num">{{ $entry['score'] }}</strong>
                            </div>
                            <div class="rf-podium__duration">
                                <span class="rf-muted rf-small">Durasi</span>
                                <strong class="rf-num">{{ $formatDuration($entry['duration_seconds']) }}</strong>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            {{-- Daftar peringkat 4-10 (atau semua baris dalam format daftar inset tipis) --}}
            @if ($restEntries->isNotEmpty())
                <section class="rf-slab rf-lb__board" aria-label="Peringkat 4 sampai 10">
                    <ol class="rf-lb__list" start="4">
                        @foreach ($restEntries as $entry)
                            @php $isMe = $entry['user_id'] === $currentUserId; @endphp
                            <li class="rf-lb-row @if ($isMe) rf-lb-row--me @endif">
                                <span class="rf-lb-row__rank rf-num">{{ $entry['rank'] }}</span>

                                <div class="rf-lb-row__user">
                                    <strong>{{ $entry['name'] }}</strong>
                                    <span class="rf-muted rf-small"><code>{{ $entry['username'] }}</code></span>
                                    @if ($isMe)
                                        <span class="rf-tag rf-lb-you">Kamu</span>
                                    @endif
                                </div>

                                <div class="rf-lb-row__stats rf-num">
                                    <span class="rf-lb-row__duration" title="Durasi pengerjaan">
                                        {{ $formatDuration($entry['duration_seconds']) }}
                                    </span>
                                    <strong class="rf-lb-row__score">{{ $entry['score'] }} <span class="rf-muted rf-small">poin</span></strong>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- Baris tambahan milik user jika berada di luar 10 besar (DESIGN §4 & §5.4) --}}
            @if ($isOutsideTopTen && $currentUserEntry)
                <section class="rf-slab rf-lb__yours" aria-label="Peringkatmu saat ini">
                    <p class="rf-small rf-muted mb-2">Posisimu di luar 10 besar pada periode ini:</p>
                    <div class="rf-lb-row rf-lb-row--me">
                        <span class="rf-lb-row__rank rf-num">#{{ $currentUserEntry['rank'] }}</span>

                        <div class="rf-lb-row__user">
                            <strong>{{ $currentUserEntry['name'] }}</strong>
                            <span class="rf-muted rf-small"><code>{{ $currentUserEntry['username'] }}</code></span>
                            <span class="rf-tag rf-lb-you">Kamu</span>
                        </div>

                        <div class="rf-lb-row__stats rf-num">
                            <span class="rf-lb-row__duration">
                                {{ $formatDuration($currentUserEntry['duration_seconds']) }}
                            </span>
                            <strong class="rf-lb-row__score">{{ $currentUserEntry['score'] }} <span class="rf-muted rf-small">poin</span></strong>
                        </div>
                    </div>
                </section>
            @endif
        @endif
    @endif
</div>
@endsection
