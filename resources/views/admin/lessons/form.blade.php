@extends('layouts.admin')

@section('title', $isEdit ? 'Ubah Materi' : 'Tambah Materi')
@section('heading', $isEdit ? 'Ubah Materi: '.$lesson->title : 'Tambah Materi')

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        @if ($isEdit && $lesson->is_published)
            <a class="rf-btn rf-btn--tertiary" href="{{ route('lessons.show', $lesson) }}" target="_blank" rel="noopener">
                <x-icon name="eye" /> Buka halaman publik
            </a>
        @endif
        <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.lessons.index') }}">Kembali ke daftar</a>
    </div>
@endsection

@section('content')
<div class="rf-slab">
    <form
        class="rf-admin-form"
        method="POST"
        action="{{ $isEdit ? route('admin.lessons.update', $lesson) : route('admin.lessons.store') }}"
        data-lesson-form
        data-preview-url="{{ route('admin.lessons.preview') }}"
        novalidate
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="rf-admin-form__row">
            <div class="rf-field">
                <label class="rf-label" for="category_id">Kategori</label>
                <select
                    class="rf-select @error('category_id') is-invalid @enderror"
                    id="category_id"
                    name="category_id"
                    required
                    @error('category_id') aria-invalid="true" aria-describedby="category-error" @enderror
                >
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((int) old('category_id', $lesson->category_id) === $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="rf-error" id="category-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                @enderror
            </div>

            <div class="rf-field">
                <label class="rf-label" for="sort_order">Urutan di dalam kategori</label>
                <input
                    class="rf-input @error('sort_order') is-invalid @enderror"
                    id="sort_order"
                    name="sort_order"
                    type="number"
                    min="0"
                    value="{{ old('sort_order', $lesson->sort_order) }}"
                    required
                    @error('sort_order') aria-invalid="true" aria-describedby="sort-order-error" @enderror
                >
                @error('sort_order')
                    <span class="rf-error" id="sort-order-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="rf-field">
            <label class="rf-label" for="title">Judul materi</label>
            <input
                class="rf-input @error('title') is-invalid @enderror"
                id="title"
                name="title"
                type="text"
                value="{{ old('title', $lesson->title) }}"
                required
                @error('title') aria-invalid="true" aria-describedby="title-error" @enderror
            >
            <span class="rf-hint">
                Slug dibuat otomatis dari judul.
                @if ($isEdit)
                    Slug saat ini: <code>{{ $lesson->slug }}</code>
                @endif
            </span>
            @error('title')
                <span class="rf-error" id="title-error"><x-icon name="alert-circle" /> {{ $message }}</span>
            @enderror
        </div>

        <div class="rf-field">
            <label class="rf-label" for="summary">Ringkasan</label>
            <textarea
                class="rf-textarea @error('summary') is-invalid @enderror"
                id="summary"
                name="summary"
                rows="2"
                required
                @error('summary') aria-invalid="true" aria-describedby="summary-error" @enderror
            >{{ old('summary', $lesson->summary) }}</textarea>
            <span class="rf-hint">Tampil di daftar materi dan bagian atas halaman materi (maksimal 500 karakter).</span>
            @error('summary')
                <span class="rf-error" id="summary-error"><x-icon name="alert-circle" /> {{ $message }}</span>
            @enderror
        </div>

        <div class="rf-admin-editor">
            <div class="rf-admin-editor__inputs">
                <div class="rf-field">
                    <label class="rf-label" for="body">Isi materi (Markdown + KaTeX)</label>
                    <textarea
                        class="rf-textarea rf-textarea--tall @error('body') is-invalid @enderror"
                        id="body"
                        name="body"
                        rows="14"
                        required
                        @error('body') aria-invalid="true" aria-describedby="body-error" @enderror
                    >{{ old('body', $lesson->body) }}</textarea>
                    <span class="rf-hint">Tulis rumus inline dengan <code>$v = s/t$</code> dan rumus blok dengan <code>$$v_t = v_0 + a\,t$$</code>.</span>
                    @error('body')
                        <span class="rf-error" id="body-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="formulas">Lembar Rumus (opsional, Markdown + KaTeX)</label>
                    <textarea
                        class="rf-textarea @error('formulas') is-invalid @enderror"
                        id="formulas"
                        name="formulas"
                        rows="7"
                        @error('formulas') aria-invalid="true" aria-describedby="formulas-error" @enderror
                    >{{ old('formulas', $lesson->formulas) }}</textarea>
                    <span class="rf-hint">Ditampilkan di atas kertas milimeter di samping isi materi.</span>
                    @error('formulas')
                        <span class="rf-error" id="formulas-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="rf-admin-editor__preview">
                <div class="rf-admin-editor__preview-bar">
                    <h2 class="h4 mb-0">Pratinjau materi & rumus</h2>
                    <button type="button" class="rf-btn rf-btn--secondary" data-preview-trigger>
                        <x-icon name="eye" /> Segarkan pratinjau
                    </button>
                </div>

                <div class="rf-tile rf-admin-preview-box">
                    <div class="rf-prose rf-lesson__prose" data-preview-body data-math-root>
                        @if ($previewBodyHtml !== '')
                            {!! $previewBodyHtml !!}
                        @else
                            <p class="rf-muted mb-0">Klik "Segarkan pratinjau" untuk memeriksa tampilan Markdown dan rumus KaTeX sebelum menyimpan.</p>
                        @endif
                    </div>
                </div>

                <div class="rf-sheet mt-4">
                    <h3 class="rf-sheet__title">Pratinjau Lembar Rumus</h3>
                    <div class="rf-sheet__paper" data-preview-formulas data-math-root>
                        @if ($previewFormulasHtml !== '')
                            {!! $previewFormulasHtml !!}
                        @else
                            <p class="rf-muted mb-0">Ringkasan rumus akan tampil di sini.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <label class="rf-check">
            <input
                type="checkbox"
                name="is_published"
                value="1"
                @checked(old('is_published', $lesson->is_published))
            >
            <span>Terbitkan materi ini agar langsung bisa dibaca pengunjung.</span>
        </label>

        <div class="rf-admin-form__actions">
            <button type="submit" class="rf-btn rf-btn--primary">
                {{ $isEdit ? 'Simpan perubahan' : 'Simpan & lanjut ke video' }}
            </button>
            <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.lessons.index') }}">Batal</a>
        </div>
    </form>
</div>

@if ($isEdit)
    <section class="rf-slab mt-6" id="video-materi" aria-labelledby="video-admin-heading">
        <h2 class="h3" id="video-admin-heading">Tahapan video materi ini</h2>

        <div class="rf-alert rf-alert--info my-4" role="note">
            <x-icon name="info" />
            <div class="rf-alert__body">
                <span class="rf-alert__kind">Catatan visibilitas YouTube</span>
                Gunakan video YouTube berstatus <strong>Unlisted (Tidak publik)</strong> atau Publik, <strong>bukan Private (Pribadi)</strong>. Video Private tidak bisa diputar oleh pelajar di luar akun pemilik channel.
            </div>
        </div>

        @if ($videos->isEmpty())
            <div class="rf-empty">Belum ada video untuk materi ini. Tambahkan tahapan video pertama di formulir bawah.</div>
        @else
            <div class="rf-table-wrap mb-5">
                <table class="rf-table">
                    <caption class="visually-hidden">Daftar tahapan video</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="rf-table__num">Tahap</th>
                            <th scope="col">Judul tahapan</th>
                            <th scope="col">ID YouTube</th>
                            <th scope="col" class="rf-table__num">Durasi</th>
                            <th scope="col" class="rf-table__actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($videos as $video)
                            <tr>
                                <td class="rf-table__num"><strong>{{ $video->position }}</strong></td>
                                <td>
                                    <strong>{{ $video->title }}</strong>
                                    <div class="rf-muted rf-small">{{ $video->youtube_url }}</div>
                                </td>
                                <td><code>{{ $video->youtube_id }}</code></td>
                                <td class="rf-table__num">
                                    @if ($video->duration_seconds)
                                        {{ max(1, (int) round($video->duration_seconds / 60)) }} menit
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="rf-table__actions">
                                    <div class="rf-admin-actions">
                                        <form method="POST" action="{{ route('admin.lessons.videos.up', [$lesson, $video]) }}">
                                            @csrf
                                            <button type="submit" class="rf-btn rf-btn--tertiary rf-btn--icon" aria-label="Naikkan tahapan {{ $video->title }}" title="Naikkan urutan" @disabled($loop->first)>
                                                <x-icon name="arrow-up" />
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.lessons.videos.down', [$lesson, $video]) }}">
                                            @csrf
                                            <button type="submit" class="rf-btn rf-btn--tertiary rf-btn--icon" aria-label="Turunkan tahapan {{ $video->title }}" title="Turunkan urutan" @disabled($loop->last)>
                                                <x-icon name="arrow-down" />
                                            </button>
                                        </form>
                                        <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.lessons.edit', ['lesson' => $lesson, 'edit_video' => $video->id]) }}#form-video">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.lessons.videos.destroy', [$lesson, $video]) }}" data-confirm="Hapus video tahapan '{{ $video->title }}'?">
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
        @endif

        <div class="rf-tile" id="form-video">
            <h3 class="h4 mb-3">
                {{ $editingVideo ? 'Ubah tahapan video #'.$editingVideo->position : 'Tambah tahapan video baru' }}
            </h3>

            <form
                class="rf-admin-form"
                method="POST"
                action="{{ $editingVideo ? route('admin.lessons.videos.update', [$lesson, $editingVideo]) : route('admin.lessons.videos.store', $lesson) }}"
                novalidate
            >
                @csrf
                @if ($editingVideo)
                    @method('PUT')
                @endif

                <div class="rf-admin-form__row">
                    <div class="rf-field">
                        <label class="rf-label" for="video_title">Judul tahapan</label>
                        <input
                            class="rf-input @error('title') is-invalid @enderror"
                            id="video_title"
                            name="title"
                            type="text"
                            value="{{ old('title', $editingVideo?->title) }}"
                            placeholder="Contoh: Gerak lurus beraturan (GLB)"
                            required
                        >
                    </div>

                    <div class="rf-field">
                        <label class="rf-label" for="duration_minutes">Durasi (menit, opsional)</label>
                        <input
                            class="rf-input @error('duration_minutes') is-invalid @enderror"
                            id="duration_minutes"
                            name="duration_minutes"
                            type="number"
                            min="1"
                            max="600"
                            value="{{ old('duration_minutes', $editingVideo && $editingVideo->duration_seconds ? (int) round($editingVideo->duration_seconds / 60) : '') }}"
                        >
                        @error('duration_minutes')
                            <span class="rf-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="youtube_url">Link YouTube (Unlisted / Publik)</label>
                    <input
                        class="rf-input @error('youtube_url') is-invalid @enderror"
                        id="youtube_url"
                        name="youtube_url"
                        type="url"
                        value="{{ old('youtube_url', $editingVideo?->youtube_url) }}"
                        placeholder="https://www.youtube.com/watch?v=... atau https://youtu.be/..."
                        required
                        @error('youtube_url') aria-invalid="true" aria-describedby="youtube-url-error" @enderror
                    >
                    <span class="rf-hint">Menerima link <code>youtube.com/watch?v=...</code> atau <code>youtu.be/...</code>. Jangan gunakan video berstatus Private.</span>
                    @error('youtube_url')
                        <span class="rf-error" id="youtube-url-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-admin-form__actions">
                    <button type="submit" class="rf-btn rf-btn--secondary">
                        {{ $editingVideo ? 'Simpan video' : 'Tambah video' }}
                    </button>
                    @if ($editingVideo)
                        <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.lessons.edit', $lesson) }}#video-materi">Batal ubah</a>
                    @endif
                </div>
            </form>
        </div>
    </section>
@endif
@endsection
