@extends('layouts.app')

@section('title', 'Kerjakan '.$tryout->title)

@php
    $totalQuestions = count($questions);
@endphp

@section('content')
<div class="container rf-tryout-work" data-tryout-attempt>
    <x-pagehead :title="$tryout->title" :homeUrl="route('tryouts.show', $tryout)">
        <p class="rf-muted rf-small mb-0">
            <span class="rf-num" data-tryout-progress>0</span> dari <span class="rf-num">{{ $totalQuestions }}</span> soal terjawab
        </p>
    </x-pagehead>

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
        </div>
    </form>

    {{-- Mode fokus try out (DESIGN-UPDATE §5): bottom bar disembunyikan dan diganti bar aksi --}}
    <x-actionbar
        :questions="$questions"
        :savedAnswers="$savedAnswers"
        :remainingSeconds="$remainingSeconds"
        formId="tryout-form"
    />
</div>
@endsection

@section('hide_bottombar', '1')
