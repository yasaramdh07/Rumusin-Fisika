@extends('layouts.admin')

@section('title', 'Kelola Materi')
@section('heading', 'Materi')

@section('actions')
    <a class="rf-btn rf-btn--primary" href="{{ route('admin.lessons.create') }}">
        <x-icon name="plus" /> Tambah materi
    </a>
@endsection

@section('content')
<div class="rf-chips mb-4" aria-label="Filter kategori">
    <a class="rf-chip @if (! $selectedCategoryId) is-active @endif" href="{{ route('admin.lessons.index') }}" @if (! $selectedCategoryId) aria-current="true" @endif>
        <x-icon class="rf-chip__check" name="check" /> Semua
    </a>
    @foreach ($categories as $cat)
        @php $active = $selectedCategoryId === $cat->id; @endphp
        <a class="rf-chip @if ($active) is-active @endif" href="{{ route('admin.lessons.index', ['category_id' => $cat->id]) }}" @if ($active) aria-current="true" @endif>
            <x-icon class="rf-chip__check" name="check" /> {{ $cat->name }}
        </a>
    @endforeach
</div>

@if ($lessons->isEmpty())
    <div class="rf-empty">Belum ada materi. Tambahkan materi baru untuk mulai menulis isi dan menambahkan video.</div>
@else
    <div class="rf-table-wrap">
        <table class="rf-table">
            <caption class="visually-hidden">Daftar materi fisika</caption>
            <thead>
                <tr>
                    <th scope="col">Judul</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="rf-table__num">Video</th>
                    <th scope="col" class="rf-table__num">Urutan</th>
                    <th scope="col" class="rf-table__actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lessons as $lesson)
                    <tr>
                        <td>
                            <strong>{{ $lesson->title }}</strong>
                            <div class="rf-muted rf-small"><code>{{ $lesson->slug }}</code></div>
                        </td>
                        <td>{{ $lesson->category?->name ?? '-' }}</td>
                        <td>
                            @if ($lesson->is_published)
                                <span class="rf-tag rf-tag--ok"><x-icon name="check" /> Terbit</span>
                            @else
                                <span class="rf-tag">Draf</span>
                            @endif
                        </td>
                        <td class="rf-table__num">{{ $lesson->videos_count }}</td>
                        <td class="rf-table__num">{{ $lesson->sort_order }}</td>
                        <td class="rf-table__actions">
                            <div class="rf-admin-actions">
                                @if ($lesson->is_published)
                                    <a class="rf-btn rf-btn--tertiary" href="{{ route('lessons.show', $lesson) }}" target="_blank" rel="noopener">
                                        <x-icon name="eye" /> Lihat
                                    </a>
                                @endif
                                <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.lessons.edit', $lesson) }}">Ubah & video</a>
                                <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" data-confirm="Hapus materi {{ $lesson->title }} beserta seluruh tahapan videonya?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rf-btn rf-btn--tertiary"><x-icon name="trash" /> Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination', ['paginator' => $lessons])
@endif
@endsection
