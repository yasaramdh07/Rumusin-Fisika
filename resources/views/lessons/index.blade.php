@extends('layouts.app')

@section('title', 'Materi Fisika')

@section('content')
<div class="container rf-lessons">
    <x-pagehead title="Materi fisika" />

    <header class="rf-lessons__head">
        <p class="rf-prose rf-muted">Pilih kategori atau langsung buka materi yang sudah terbit. Semua materi dilengkapi Lembar Rumus dan tahapan video.</p>

        <nav class="rf-chips mt-4" aria-label="Filter kategori materi">
            <a class="rf-chip @if (! $selectedCategorySlug) is-active @endif"
               href="{{ route('lessons.index') }}"
               @if (! $selectedCategorySlug) aria-current="true" @endif>
                <x-icon class="rf-chip__check" name="check" /> Semua kategori
            </a>
            @foreach ($categories as $category)
                @php $isActive = $selectedCategorySlug === $category->slug; @endphp
                <a class="rf-chip @if ($isActive) is-active @endif"
                   href="{{ route('lessons.index', ['kategori' => $category->slug]) }}"
                   @if ($isActive) aria-current="true" @endif>
                    <x-icon class="rf-chip__check" name="check" /> {{ $category->name }}
                </a>
            @endforeach
        </nav>
    </header>

    <div class="rf-lessons__groups">
        @foreach ($categories as $category)
            @if (! $selectedCategorySlug || $selectedCategorySlug === $category->slug)
                <section class="rf-lessons__group" id="kategori-{{ $category->slug }}" aria-labelledby="heading-{{ $category->slug }}">
                    <div class="rf-lessons__group-head">
                        <h2 class="h3" id="heading-{{ $category->slug }}">{{ $category->name }}</h2>
                        @if ($category->description)
                            <p class="rf-muted mb-0">{{ $category->description }}</p>
                        @endif
                    </div>

                    @if ($category->lessons->isEmpty())
                        <div class="rf-tile rf-lessons__empty">
                            Belum ada materi terbit di kategori {{ $category->name }}. Coba buka kategori lain terlebih dulu.
                        </div>
                    @else
                        <div class="rf-lessons__list">
                            @foreach ($category->lessons as $lesson)
                                <article class="rf-slab rf-lessons__item">
                                    <div class="rf-lessons__item-body">
                                        <h3 class="h4">
                                            <a class="rf-lessons__item-link" href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a>
                                        </h3>
                                        <p class="rf-prose rf-muted mb-0">{{ $lesson->summary }}</p>
                                    </div>
                                    <div class="rf-lessons__item-action">
                                        @if ($lesson->videos_count > 0)
                                            <span class="rf-tag rf-num">{{ $lesson->videos_count }} tahapan video</span>
                                        @endif
                                        <a class="rf-btn rf-btn--secondary" href="{{ route('lessons.show', $lesson) }}">Baca materi</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif
        @endforeach
    </div>
</div>
@endsection
