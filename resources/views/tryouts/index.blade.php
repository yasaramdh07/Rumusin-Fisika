@extends('layouts.app')

@section('title', 'Daftar Try Out')

@section('content')
<div class="container rf-lessons">
    <x-pagehead title="Try out aktif" />

    <header class="rf-lessons__head">
        <p class="rf-prose rf-muted">Pilih paket try out di bawah untuk menguji pemahaman rumusmu. Skor dan pembahasan bertahap langsung dihitung setelah kamu mengirim jawaban.</p>
    </header>

    @if ($tryouts->isEmpty())
        <div class="rf-slab">
            <div class="rf-empty">Belum ada paket try out yang aktif saat ini. Baca materi terlebih dulu sambil menunggu paket baru dibuka.</div>
        </div>
    @else
        <div class="rf-lessons__list">
            @foreach ($tryouts as $tryout)
                <article class="rf-slab rf-lessons__item">
                    <div class="rf-lessons__item-body">
                        <h2 class="h4">
                            <a class="rf-lessons__item-link" href="{{ route('tryouts.show', $tryout) }}">{{ $tryout->title }}</a>
                        </h2>
                        @if ($tryout->description)
                            <p class="rf-prose rf-muted mb-2">{{ $tryout->description }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2">
                            <span class="rf-tag rf-num">{{ $tryout->questions_count }} soal</span>
                            <span class="rf-tag rf-num">{{ $tryout->duration_minutes }} menit</span>
                            @if ($tryout->lesson)
                                <span class="rf-tag">{{ $tryout->lesson->title }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="rf-lessons__item-action">
                        <a class="rf-btn rf-btn--primary" href="{{ route('tryouts.show', $tryout) }}">Mulai try out</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
