@extends('layouts.admin')

@section('title', $isEdit ? 'Ubah Soal' : 'Tambah Soal')
@section('heading', $isEdit ? 'Ubah Soal #'.$question->id : 'Tambah Soal')

@section('actions')
    <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.questions.index') }}">Kembali ke bank soal</a>
@endsection

@section('content')
<div class="rf-slab">
    <form
        class="rf-admin-form"
        method="POST"
        action="{{ $isEdit ? route('admin.questions.update', $question) : route('admin.questions.store') }}"
        data-question-form
        data-preview-url="{{ route('admin.questions.preview') }}"
        novalidate
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="rf-admin-form__row">
            <div class="rf-field">
                <label class="rf-label" for="lesson_id">Terkait materi (opsional)</label>
                <select
                    class="rf-select @error('lesson_id') is-invalid @enderror"
                    id="lesson_id"
                    name="lesson_id"
                    @error('lesson_id') aria-invalid="true" aria-describedby="lesson-error" @enderror
                >
                    <option value="">Gabungan (tanpa materi khusus)</option>
                    @foreach ($lessons as $lesson)
                        <option value="{{ $lesson->id }}" @selected((int) old('lesson_id', $question->lesson_id) === $lesson->id)>
                            {{ $lesson->title }}
                        </option>
                    @endforeach
                </select>
                @error('lesson_id')
                    <span class="rf-error" id="lesson-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                @enderror
            </div>

            <div class="rf-field">
                <label class="rf-label" for="difficulty">Tingkat kesulitan</label>
                <select
                    class="rf-select @error('difficulty') is-invalid @enderror"
                    id="difficulty"
                    name="difficulty"
                    required
                    @error('difficulty') aria-invalid="true" aria-describedby="difficulty-error" @enderror
                >
                    <option value="1" @selected((int) old('difficulty', $question->difficulty) === 1)>1 (Dasar)</option>
                    <option value="2" @selected((int) old('difficulty', $question->difficulty) === 2)>2 (Menengah)</option>
                    <option value="3" @selected((int) old('difficulty', $question->difficulty) === 3)>3 (Menantang)</option>
                </select>
                @error('difficulty')
                    <span class="rf-error" id="difficulty-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="rf-admin-editor">
            <div class="rf-admin-editor__inputs">
                <div class="rf-field">
                    <label class="rf-label" for="body">Pertanyaan soal (Markdown + KaTeX)</label>
                    <textarea
                        class="rf-textarea @error('body') is-invalid @enderror"
                        id="body"
                        name="body"
                        rows="5"
                        required
                        @error('body') aria-invalid="true" aria-describedby="body-error" @enderror
                    >{{ old('body', $question->body) }}</textarea>
                    <span class="rf-hint">Gunakan <code>$...$</code> untuk rumus inline dan <code>$$...$$</code> untuk rumus blok.</span>
                    @error('body')
                        <span class="rf-error" id="body-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                @foreach (['a' => 'Pilihan A', 'b' => 'Pilihan B', 'c' => 'Pilihan C', 'd' => 'Pilihan D'] as $optKey => $optLabel)
                    @php $fieldName = 'option_'.$optKey; @endphp
                    <div class="rf-field">
                        <label class="rf-label" for="{{ $fieldName }}">{{ $optLabel }}</label>
                        <input
                            class="rf-input @error($fieldName) is-invalid @enderror"
                            id="{{ $fieldName }}"
                            name="{{ $fieldName }}"
                            type="text"
                            value="{{ old($fieldName, $question->{$fieldName}) }}"
                            required
                            @error($fieldName) aria-invalid="true" aria-describedby="{{ $fieldName }}-error" @enderror
                        >
                        @error($fieldName)
                            <span class="rf-error" id="{{ $fieldName }}-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                        @enderror
                    </div>
                @endforeach

                <div class="rf-field rf-admin-form__short">
                    <label class="rf-label" for="correct_option">Jawaban benar</label>
                    <select
                        class="rf-select @error('correct_option') is-invalid @enderror"
                        id="correct_option"
                        name="correct_option"
                        required
                        @error('correct_option') aria-invalid="true" aria-describedby="correct-option-error" @enderror
                    >
                        @foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $val => $label)
                            <option value="{{ $val }}" @selected(strtolower((string) old('correct_option', $question->correct_option)) === $val)>
                                Pilihan {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('correct_option')
                        <span class="rf-error" id="correct-option-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="explanation">Pembahasan bertahap (Markdown + KaTeX)</label>
                    <textarea
                        class="rf-textarea @error('explanation') is-invalid @enderror"
                        id="explanation"
                        name="explanation"
                        rows="6"
                        required
                        @error('explanation') aria-invalid="true" aria-describedby="explanation-error" @enderror
                    >{{ old('explanation', $question->explanation) }}</textarea>
                    <span class="rf-hint">Tampil pada halaman hasil setelah pelajar selesai mengirim jawaban try out.</span>
                    @error('explanation')
                        <span class="rf-error" id="explanation-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="rf-admin-editor__preview">
                <div class="rf-admin-editor__preview-bar">
                    <h2 class="h4 mb-0">Pratinjau soal & KaTeX</h2>
                    <button type="button" class="rf-btn rf-btn--secondary" data-question-preview-trigger>
                        <x-icon name="eye" /> Segarkan pratinjau
                    </button>
                </div>

                <div class="rf-tile rf-admin-preview-box">
                    <div class="rf-prose rf-lesson__prose" data-q-preview-body data-math-root>
                        @if ($preview['body_html'] !== '')
                            {!! $preview['body_html'] !!}
                        @else
                            <p class="rf-muted mb-0">Klik "Segarkan pratinjau" untuk melihat tampilan soal dan rumus KaTeX.</p>
                        @endif
                    </div>

                    <ol class="rf-admin-q-options mt-3" type="A">
                        <li data-q-preview-option-a data-math-root>{!! $preview['option_a_html'] !!}</li>
                        <li data-q-preview-option-b data-math-root>{!! $preview['option_b_html'] !!}</li>
                        <li data-q-preview-option-c data-math-root>{!! $preview['option_c_html'] !!}</li>
                        <li data-q-preview-option-d data-math-root>{!! $preview['option_d_html'] !!}</li>
                    </ol>

                    <hr class="rf-profile__divider">

                    <h3 class="h5">Pembahasan</h3>
                    <div class="rf-prose rf-lesson__prose" data-q-preview-explanation data-math-root>
                        @if ($preview['explanation_html'] !== '')
                            {!! $preview['explanation_html'] !!}
                        @else
                            <p class="rf-muted mb-0">Pembahasan bertahap akan tampil di sini.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="rf-admin-form__actions">
            <button type="submit" class="rf-btn rf-btn--primary">
                {{ $isEdit ? 'Simpan perubahan' : 'Simpan soal' }}
            </button>
            <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.questions.index') }}">Batal</a>
        </div>
    </form>
</div>
@endsection
