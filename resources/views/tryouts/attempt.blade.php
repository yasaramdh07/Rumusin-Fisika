@extends('layouts.app')

@section('title', 'Kerjakan '.$tryout->title)

@php
    $totalQuestions = count($questions);
@endphp

@section('content')
<div class="container rf-tryout-work" data-tryout-attempt>
    <header class="rf-slab rf-tryout-work__bar">
        <div>
            <h1 class="h3 mb-1">{{ $tryout->title }}</h1>
            <p class="rf-muted rf-small mb-0">
                <span class="rf-num" data-tryout-progress>0</span> dari <span class="rf-num">{{ $totalQuestions }}</span> soal terjawab
            </p>
        </div>

        <div class="rf-tryout-timer" role="timer" aria-live="off" data-tryout-timer data-remaining-seconds="{{ $remainingSeconds }}">
            <span class="rf-tryout-timer__label">Sisa waktu</span>
            <strong class="rf-tryout-timer__clock rf-num" data-tryout-clock>
                {{ sprintf('%02d:%02d', intdiv($remainingSeconds, 60), $remainingSeconds % 60) }}
            </strong>
        </div>
    </header>

    <form method="POST" action="{{ route('tryouts.submit', $attempt) }}" id="tryout-form">
        @csrf

        <div class="rf-tryout-work__grid">
            <div class="rf-tryout-work__questions">
                @foreach ($questions as $q)
                    @php
                        $qId = $q['id'];
                        $selected = old('answers.'.$qId, $savedAnswers[$qId] ?? null);
                    @endphp
                    <section
                        class="rf-slab rf-tryout-q"
                        id="soal-{{ $q['number'] }}"
                        data-question-block
                        data-question-number="{{ $q['number'] }}"
                        aria-labelledby="judul-soal-{{ $q['number'] }}"
                    >
                        <div class="rf-tryout-q__head">
                            <h2 class="h4 mb-0" id="judul-soal-{{ $q['number'] }}">
                                Soal {{ $q['number'] }} dari {{ $totalQuestions }}
                            </h2>
                            <span class="rf-tag">Tingkat {{ $q['difficulty'] }}</span>
                        </div>

                        <div class="rf-prose rf-lesson__prose rf-tryout-q__body" data-math-root>
                            {!! $q['body_html'] !!}
                        </div>

                        <fieldset class="rf-tryout-options">
                            <legend class="visually-hidden">Pilihan jawaban soal {{ $q['number'] }}</legend>

                            @foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $optKey => $optLetter)
                                @php
                                    $inputId = 'q_'.$qId.'_'.$optKey;
                                    $isChosen = strtolower((string) $selected) === $optKey;
                                @endphp
                                <label
                                    class="rf-option @if ($isChosen) is-selected @endif"
                                    for="{{ $inputId }}"
                                    data-option-tile
                                >
                                    <input
                                        class="rf-option__input visually-hidden"
                                        type="radio"
                                        id="{{ $inputId }}"
                                        name="answers[{{ $qId }}]"
                                        value="{{ $optKey }}"
                                        @checked($isChosen)
                                        data-option-input
                                    >
                                    <span class="rf-option__letter" aria-hidden="true">{{ $optLetter }}</span>
                                    <span class="rf-option__text" data-math-root>{!! $q['options'][$optKey] !!}</span>
                                    <span class="rf-option__check" aria-hidden="true">
                                        <x-icon name="check" /> Dipilih
                                    </span>
                                </label>
                            @endforeach
                        </fieldset>
                    </section>
                @endforeach
            </div>

            <aside class="rf-tryout-work__side" aria-label="Peta soal dan pengiriman">
                <div class="rf-slab rf-tryout-map">
                    <h2 class="h5 mb-3">Peta soal</h2>
                    <ol class="rf-tryout-map__grid">
                        @foreach ($questions as $q)
                            @php
                                $qId = $q['id'];
                                $answered = ! empty(old('answers.'.$qId, $savedAnswers[$qId] ?? null));
                            @endphp
                            <li>
                                <a
                                    class="rf-tryout-map__dot rf-num @if ($answered) is-answered @endif"
                                    href="#soal-{{ $q['number'] }}"
                                    data-map-dot="{{ $q['number'] }}"
                                    aria-label="Ke soal {{ $q['number'] }}"
                                >
                                    {{ $q['number'] }}
                                </a>
                            </li>
                        @endforeach
                    </ol>

                    <p class="rf-muted rf-small mt-3 mb-4">
                        Periksa kembali jawabanmu sebelum mengirim. Skor dihitung di server setelah tombol di bawah ditekan.
                    </p>

                    <button type="submit" class="rf-btn rf-btn--primary rf-btn--block">
                        Kirim jawaban
                    </button>
                </div>
            </aside>
        </div>
    </form>
</div>
@endsection
