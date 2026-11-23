@extends('layouts.admin')

@section('title', 'Kelola Soal')
@section('heading', 'Bank Soal')

@section('actions')
    <a class="rf-btn rf-btn--primary" href="{{ route('admin.questions.create') }}">
        <x-icon name="plus" /> Tambah soal
    </a>
@endsection

@section('content')
<div class="rf-chips mb-4" aria-label="Filter materi soal">
    <a class="rf-chip @if (! $selectedLessonId) is-active @endif" href="{{ route('admin.questions.index', array_filter(['difficulty' => $selectedDifficulty])) }}" @if (! $selectedLessonId) aria-current="true" @endif>
        <x-icon class="rf-chip__check" name="check" /> Semua materi
    </a>
    <a class="rf-chip @if ($selectedLessonId === 'none') is-active @endif" href="{{ route('admin.questions.index', array_filter(['lesson_id' => 'none', 'difficulty' => $selectedDifficulty])) }}" @if ($selectedLessonId === 'none') aria-current="true" @endif>
        <x-icon class="rf-chip__check" name="check" /> Gabungan (tanpa materi)
    </a>
    @foreach ($lessons as $lesson)
        @php $active = (string) $lesson->id === (string) $selectedLessonId; @endphp
        <a class="rf-chip @if ($active) is-active @endif" href="{{ route('admin.questions.index', array_filter(['lesson_id' => $lesson->id, 'difficulty' => $selectedDifficulty])) }}" @if ($active) aria-current="true" @endif>
            <x-icon class="rf-chip__check" name="check" /> {{ $lesson->title }}
        </a>
    @endforeach
</div>

@if ($questions->isEmpty())
    <div class="rf-empty">Belum ada soal di bank soal. Tambahkan soal baru untuk menyusun paket try out.</div>
@else
    <div class="rf-table-wrap">
        <table class="rf-table">
            <caption class="visually-hidden">Daftar soal di bank soal</caption>
            <thead>
                <tr>
                    <th scope="col">Soal</th>
                    <th scope="col">Materi</th>
                    <th scope="col">Tingkat</th>
                    <th scope="col">Kunci</th>
                    <th scope="col" class="rf-table__num">Paket</th>
                    <th scope="col" class="rf-table__actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($questions as $question)
                    <tr>
                        <td>
                            <div class="rf-admin-question-snippet" data-math-root>
                                {{ \Illuminate\Support\Str::limit($question->body, 140) }}
                            </div>
                        </td>
                        <td>{{ $question->lesson?->title ?? 'Gabungan' }}</td>
                        <td><span class="rf-tag">Tingkat {{ $question->difficulty }}</span></td>
                        <td><strong>{{ strtoupper($question->correct_option) }}</strong></td>
                        <td class="rf-table__num">{{ $question->tryouts_count }}</td>
                        <td class="rf-table__actions">
                            <div class="rf-admin-actions">
                                <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.questions.edit', $question) }}">Ubah</a>
                                @if ($question->used_in_finished_attempts)
                                    <button type="button" class="rf-btn rf-btn--tertiary" disabled title="Soal sudah dipakai pada percobaan yang selesai dan tidak bisa dihapus">
                                        Terkunci
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" data-confirm="Hapus soal ini dari bank soal?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rf-btn rf-btn--tertiary"><x-icon name="trash" /> Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination', ['paginator' => $questions])
@endif
@endsection
