@extends('layouts.app')

@section('title', 'Rumusin Soalnya, Paham Fisikanya')

@section('content')
<div class="container rf-landing">
    <section class="rf-landing__hero" aria-labelledby="hero-heading">
        <div class="rf-landing__copy">
            <h1 id="hero-heading">Rumusin soalnya,<br>paham fisikanya.</h1>
            <p class="rf-prose rf-muted">
                Materi bertahap, try out bernilai, dan Ruka yang siap ditanya tepat di bagian rumus yang belum nyambung.
            </p>
            <div class="rf-landing__cta">
                <a class="rf-btn rf-btn--primary" href="{{ $featuredLesson ? route('lessons.show', $featuredLesson) : route('lessons.index') }}">Mulai belajar</a>
                <a class="rf-btn rf-btn--tertiary" href="{{ route('lessons.index') }}">Lihat semua materi</a>
            </div>
        </div>

        <aside class="rf-landing__sheet-wrap" aria-label="Contoh Lembar Rumus">
            <div class="rf-sheet rf-sheet--tilt rf-landing__sheet">
                <h2 class="rf-sheet__title">Lembar Rumus: {{ $featuredLesson?->title ?? 'Gerak Lurus' }}</h2>
                <div class="rf-sheet__paper" data-math-root>
                    {!! $heroFormulasHtml !!}

                    <div class="rf-hero-calc" data-hero-mini-calc>
                        <p class="rf-small fw-bold mb-2">Coba hitung cepat GLBB ($v_t = v_0 + a\,t$ dan $s = v_0\,t + \tfrac{1}{2}a\,t^2$):</p>
                        <div class="rf-hero-calc__inputs">
                            <div class="rf-field">
                                <label class="rf-label rf-small" for="hero-v0">v₀ (m/s)</label>
                                <input class="rf-input rf-num" id="hero-v0" type="number" step="any" value="0" data-hero-v0>
                            </div>
                            <div class="rf-field">
                                <label class="rf-label rf-small" for="hero-a">a (m/s²)</label>
                                <input class="rf-input rf-num" id="hero-a" type="number" step="any" value="4" data-hero-a>
                            </div>
                            <div class="rf-field">
                                <label class="rf-label rf-small" for="hero-t">t (s)</label>
                                <input class="rf-input rf-num" id="hero-t" type="number" step="any" value="5" data-hero-t>
                            </div>
                        </div>
                        <div class="rf-hero-calc__results rf-num">
                            <span>Kecepatan akhir $v_t$: <strong data-hero-vt>20.0 m/s</strong></span>
                            <span>Jarak $s$: <strong data-hero-s>50.0 m</strong></span>
                        </div>
                    </div>
                </div>
                <p class="rf-sheet__note">Ubah angka di atas untuk menguji rumus langsung di Lembar Rumus.</p>
            </div>
        </aside>
    </section>

    <section class="rf-landing__categories" aria-labelledby="kategori-landing-heading">
        <div class="rf-landing__section-head">
            <h2 id="kategori-landing-heading">Pilih topik fisika</h2>
            <p class="rf-muted mb-0">Enam topik SMA dari kinematika sampai elektronika dasar.</p>
        </div>

        <div class="rf-landing__bento">
            @foreach ($categories as $category)
                <a class="rf-tile rf-landing__tile" href="{{ route('lessons.index', ['kategori' => $category->slug]) }}">
                    <div>
                        <h3 class="h4">{{ $category->name }}</h3>
                        <p class="rf-muted mb-0">{{ $category->description }}</p>
                    </div>
                    <span class="rf-landing__tile-meta rf-num">
                        @if ($category->published_lessons_count > 0)
                            {{ $category->published_lessons_count }} materi terbit
                        @else
                            Segera terbit
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection
