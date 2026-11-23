@extends('layouts.admin')

@section('title', $isEdit ? 'Ubah Paket Try Out' : 'Tambah Paket Try Out')
@section('heading', $isEdit ? 'Ubah Paket: '.$tryout->title : 'Tambah Paket Try Out')

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        @if ($isEdit && ! $tryout->is_active)
            <form method="POST" action="{{ route('admin.tryouts.activate', $tryout) }}">
                @csrf
                <button type="submit" class="rf-btn rf-btn--secondary">Jadikan aktif</button>
            </form>
        @endif
        <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.tryouts.index') }}">Kembali ke daftar</a>
    </div>
@endsection

@php
    $oldQuestionIds = old('question_ids');
    $checkedIds = is_array($oldQuestionIds)
        ? array_map('intval', $oldQuestionIds)
        : array_keys($selectedPositions);
    $oldPositions = is_array(old('positions')) ? old('positions') : [];
@endphp

@section('content')
<div class="rf-slab">
    <form
        class="rf-admin-form"
        method="POST"
        action="{{ $isEdit ? route('admin.tryouts.update', $tryout) : route('admin.tryouts.store') }}"
        novalidate
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="rf-admin-form__row">
            <div class="rf-field">
                <label class="rf-label" for="title">Judul paket try out</label>
                <input
                    class="rf-input @error('title') is-invalid @enderror"
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title', $tryout->title) }}"
                    required
                    @error('title') aria-invalid="true" aria-describedby="title-error" @enderror
                >
                <span class="rf-hint">
                    Slug dibuat otomatis dari judul.
                    @if ($isEdit)
                        Slug saat ini: <code>{{ $tryout->slug }}</code>
                    @endif
                </span>
                @error('title')
                    <span class="rf-error" id="title-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                @enderror
            </div>

            <div class="rf-field">
                <label class="rf-label" for="duration_minutes">Durasi pengerjaan (menit)</label>
                <input
                    class="rf-input @error('duration_minutes') is-invalid @enderror"
                    id="duration_minutes"
                    name="duration_minutes"
                    type="number"
                    min="1"
                    max="300"
                    value="{{ old('duration_minutes', $tryout->duration_minutes) }}"
                    required
                    @error('duration_minutes') aria-invalid="true" aria-describedby="duration-error" @enderror
                >
                @error('duration_minutes')
                    <span class="rf-error" id="duration-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                @enderror
            </div>
        </div>

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
                    <option value="{{ $lesson->id }}" @selected((int) old('lesson_id', $tryout->lesson_id) === $lesson->id)>
                        {{ $lesson->title }}
                    </option>
                @endforeach
            </select>
            @error('lesson_id')
                <span class="rf-error" id="lesson-error"><x-icon name="alert-circle" /> {{ $message }}</span>
            @enderror
        </div>

        <div class="rf-field">
            <label class="rf-label" for="description">Deskripsi (opsional)</label>
            <textarea
                class="rf-textarea @error('description') is-invalid @enderror"
                id="description"
                name="description"
                rows="3"
                @error('description') aria-invalid="true" aria-describedby="description-error" @enderror
            >{{ old('description', $tryout->description) }}</textarea>
            @error('description')
                <span class="rf-error" id="description-error"><x-icon name="alert-circle" /> {{ $message }}</span>
            @enderror
        </div>

        <div class="rf-admin-form__row">
            <div class="rf-field">
                <label class="rf-label" for="starts_at">Jadwal mulai (opsional)</label>
                <input
                    class="rf-input @error('starts_at') is-invalid @enderror"
                    id="starts_at"
                    name="starts_at"
                    type="datetime-local"
                    value="{{ old('starts_at', $tryout->starts_at?->format('Y-m-d\TH:i')) }}"
                    @error('starts_at') aria-invalid="true" aria-describedby="starts-at-error" @enderror
                >
                @error('starts_at')
                    <span class="rf-error" id="starts-at-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                @enderror
            </div>

            <div class="rf-field">
                <label class="rf-label" for="ends_at">Jadwal berakhir (opsional)</label>
                <input
                    class="rf-input @error('ends_at') is-invalid @enderror"
                    id="ends_at"
                    name="ends_at"
                    type="datetime-local"
                    value="{{ old('ends_at', $tryout->ends_at?->format('Y-m-d\TH:i')) }}"
                    @error('ends_at') aria-invalid="true" aria-describedby="ends-at-error" @enderror
                >
                @error('ends_at')
                    <span class="rf-error" id="ends-at-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="rf-field">
            <div class="d-flex flex-wrap align-items-baseline justify-content-between gap-2">
                <span class="rf-label">Pilih dan urutkan soal untuk paket ini</span>
                <span class="rf-hint">Minimal {{ $minActiveQuestions }} soal bila paket diaktifkan.</span>
            </div>

            @error('question_ids')
                <span class="rf-error"><x-icon name="alert-circle" /> {{ $message }}</span>
            @enderror

            @if ($allQuestions->isEmpty())
                <div class="rf-empty">
                    Bank soal masih kosong. <a href="{{ route('admin.questions.create') }}">Buat soal terlebih dulu</a>.
                </div>
            @else
                <div class="rf-table-wrap">
                    <table class="rf-table">
                        <caption class="visually-hidden">Pilih soal dan atur urutan dalam paket try out</caption>
                        <thead>
                            <tr>
                                <th scope="col">Pilih</th>
                                <th scope="col" class="rf-table__num">Urutan</th>
                                <th scope="col">Pertanyaan</th>
                                <th scope="col">Materi</th>
                                <th scope="col">Tingkat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allQuestions as $question)
                                @php
                                    $isChecked = in_array($question->id, $checkedIds, true);
                                    $posValue = $oldPositions[$question->id] ?? ($selectedPositions[$question->id] ?? ($loop->iteration));
                                @endphp
                                <tr>
                                    <td>
                                        <input
                                            type="checkbox"
                                            class="rf-admin-checkbox"
                                            id="q_{{ $question->id }}"
                                            name="question_ids[]"
                                            value="{{ $question->id }}"
                                            @checked($isChecked)
                                            aria-label="Pilih soal #{{ $question->id }}"
                                        >
                                    </td>
                                    <td class="rf-table__num" style="width: 110px;">
                                        <input
                                            class="rf-input rf-num"
                                            type="number"
                                            min="1"
                                            max="999"
                                            name="positions[{{ $question->id }}]"
                                            value="{{ $posValue }}"
                                            aria-label="Urutan untuk soal #{{ $question->id }}"
                                        >
                                    </td>
                                    <td>
                                        <label for="q_{{ $question->id }}" class="d-block" data-math-root>
                                            {{ \Illuminate\Support\Str::limit($question->body, 150) }}
                                        </label>
                                    </td>
                                    <td>{{ $question->lesson?->title ?? 'Gabungan' }}</td>
                                    <td><span class="rf-tag">Tingkat {{ $question->difficulty }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <label class="rf-check">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $tryout->is_active))
            >
            <span>Jadikan paket try out ini aktif (minimal {{ $minActiveQuestions }} soal terpilih).</span>
        </label>

        <div class="rf-admin-form__actions">
            <button type="submit" class="rf-btn rf-btn--primary">
                {{ $isEdit ? 'Simpan perubahan' : 'Simpan paket try out' }}
            </button>
            <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.tryouts.index') }}">Batal</a>
        </div>
    </form>
</div>

@if ($isEdit && $tryout->questions->isNotEmpty())
    <section class="rf-slab mt-6" id="urutan-soal" aria-labelledby="urutan-soal-heading">
        <h2 class="h3" id="urutan-soal-heading">Urutan soal terpilih ({{ $tryout->questions->count() }} soal)</h2>
        <p class="rf-muted rf-small">Gunakan tombol panah untuk menaikkan atau menurunkan urutan soal di paket ini.</p>

        <div class="rf-table-wrap">
            <table class="rf-table">
                <caption class="visually-hidden">Urutan soal dalam paket {{ $tryout->title }}</caption>
                <thead>
                    <tr>
                        <th scope="col" class="rf-table__num">No</th>
                        <th scope="col">Soal</th>
                        <th scope="col">Tingkat</th>
                        <th scope="col" class="rf-table__actions">Atur urutan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tryout->questions as $question)
                        <tr>
                            <td class="rf-table__num"><strong>{{ $question->pivot->position }}</strong></td>
                            <td data-math-root>{{ \Illuminate\Support\Str::limit($question->body, 160) }}</td>
                            <td><span class="rf-tag">Tingkat {{ $question->difficulty }}</span></td>
                            <td class="rf-table__actions">
                                <div class="rf-admin-actions">
                                    <form method="POST" action="{{ route('admin.tryouts.questions.up', [$tryout, $question]) }}">
                                        @csrf
                                        <button type="submit" class="rf-btn rf-btn--tertiary rf-btn--icon" aria-label="Naikkan urutan soal #{{ $question->id }}" title="Naikkan urutan" @disabled($loop->first)>
                                            <x-icon name="arrow-up" />
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.tryouts.questions.down', [$tryout, $question]) }}">
                                        @csrf
                                        <button type="submit" class="rf-btn rf-btn--tertiary rf-btn--icon" aria-label="Turunkan urutan soal #{{ $question->id }}" title="Turunkan urutan" @disabled($loop->last)>
                                            <x-icon name="arrow-down" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection
