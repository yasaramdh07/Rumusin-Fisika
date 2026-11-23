@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<div class="container rf-home">
    <div class="rf-home__top">
        <section class="rf-slab rf-slab--fold rf-home__continue" aria-labelledby="lanjut-heading">
            <h1 id="lanjut-heading">Halo, {{ $user->name }}</h1>
            @if ($featuredLesson)
                <p class="rf-prose rf-muted">
                    Materi yang siap kamu baca: <strong>{{ $featuredLesson->title }}</strong> ({{ $featuredLesson->category->name }}).
                    {{ $featuredLesson->summary }}
                </p>
                <div class="rf-home__cta">
                    <a class="rf-btn rf-btn--primary" href="{{ url('/materi/'.$featuredLesson->slug) }}">Lanjutkan belajar</a>
                    <a class="rf-btn rf-btn--tertiary" href="{{ url('/tryout') }}">Kerjakan try out</a>
                </div>
            @else
                <p class="rf-prose rf-muted">Pilih materi fisika di bawah untuk mulai membaca rumus dan menonton video bertahap.</p>
                <div class="rf-home__cta">
                    <a class="rf-btn rf-btn--primary" href="{{ url('/materi') }}">Buka daftar materi</a>
                </div>
            @endif
        </section>

        <aside class="rf-slab rf-home__summary" aria-labelledby="ringkasan-heading">
            <h2 class="h4" id="ringkasan-heading">Catatan minggu ini</h2>
            <dl class="rf-home__notes">
                <div class="rf-home__note">
                    <dt>Skor try out terbaik</dt>
                    <dd class="rf-num">
                        @if ($bestScore !== null)
                            {{ $bestScore }} dari 100
                        @else
                            Belum ada percobaan.
                        @endif
                    </dd>
                </div>
                <div class="rf-home__note">
                    <dt>Peringkat minggu ini</dt>
                    <dd class="rf-num">
                        @if ($weeklyRank !== null)
                            #{{ $weeklyRank }} di {{ $latestActiveTryout?->title ?? 'try out aktif' }}.
                            <a href="{{ route('leaderboard.index') }}">Lihat leaderboard</a>
                        @else
                            Belum masuk peringkat minggu ini. <a href="{{ route('leaderboard.index') }}">Lihat leaderboard</a>
                        @endif
                    </dd>
                </div>
            </dl>
        </aside>
    </div>

    <section class="rf-home__categories" aria-labelledby="kategori-heading">
        <h2 id="kategori-heading">Kategori materi</h2>
        <div class="rf-home__cat-grid">
            @foreach ($categories as $category)
                <a class="rf-tile rf-home__cat" href="{{ url('/materi?kategori='.$category->slug) }}">
                    <h3 class="h4">{{ $category->name }}</h3>
                    <p class="mb-0 rf-muted rf-small">{{ $category->description }}</p>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection
