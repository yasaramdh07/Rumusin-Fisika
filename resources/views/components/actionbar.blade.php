@props([
    'questions' => [],
    'savedAnswers' => [],
    'remainingSeconds' => 1200,
    'formId' => 'tryout-form',
    'static' => false,
])

<div
    {{ $attributes->class(['rf-actionbar', 'rf-actionbar--static' => $static]) }}
    role="region"
    aria-label="Bar aksi pengerjaan try out"
    data-tryout-actionbar
>
    <ol class="rf-actionbar__map" aria-label="Peta soal">
        @foreach ($questions as $q)
            @php
                $qId = $q['id'];
                $num = $q['number'];
                $answered = ! empty(old('answers.'.$qId, $savedAnswers[$qId] ?? null));
            @endphp
            <li>
                <a
                    class="rf-actionbar__dot rf-num @if ($answered) is-answered @endif"
                    href="#soal-{{ $num }}"
                    data-map-dot="{{ $num }}"
                    aria-label="Ke soal {{ $num }}"
                >
                    {{ $num }}
                </a>
            </li>
        @endforeach
    </ol>

    <div
        class="rf-actionbar__timer rf-num"
        role="timer"
        aria-live="off"
        data-tryout-timer
        data-remaining-seconds="{{ $remainingSeconds }}"
        title="Sisa waktu pengerjaan"
    >
        <span class="visually-hidden">Sisa waktu</span>
        <strong data-tryout-clock>
            {{ sprintf('%02d:%02d', intdiv((int) $remainingSeconds, 60), ((int) $remainingSeconds) % 60) }}
        </strong>
    </div>

    <button
        type="submit"
        @if ($formId) form="{{ $formId }}" @endif
        class="rf-btn rf-btn--primary rf-actionbar__submit"
    >
        Kirim jawaban
    </button>
</div>
