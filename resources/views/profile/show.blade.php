@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="container rf-profile">
    <header class="rf-profile__head">
        <x-ruka-avatar size="lg" />
        <div>
            <h1>{{ $user->name }}</h1>
            <p class="rf-muted mb-0">
                Username: <code>{{ $user->username }}</code>
                @if ($user->email)
                    · {{ $user->email }}
                @endif
            </p>
        </div>
    </header>

    <div class="rf-profile__grid">
        <section class="rf-slab" aria-labelledby="riwayat-heading">
            <div class="rf-profile__section-head">
                <h2 class="h3" id="riwayat-heading">Riwayat try out</h2>
                <p class="mb-0 rf-muted">
                    Skor terbaik:
                    <strong class="rf-num">{{ $bestScore !== null ? $bestScore.' / 100' : 'belum ada' }}</strong>
                </p>
            </div>

            @if ($attempts->isEmpty())
                <div class="rf-empty">
                    Belum ada percobaan try out yang selesai. <a href="{{ url('/tryout') }}">Mulai try out pertama</a>.
                </div>
            @else
                <div class="rf-table-wrap">
                    <table class="rf-table">
                        <caption class="visually-hidden">Daftar percobaan try out yang sudah selesai</caption>
                        <thead>
                            <tr>
                                <th scope="col">Paket try out</th>
                                <th scope="col">Selesai</th>
                                <th scope="col" class="rf-table__num">Benar</th>
                                <th scope="col" class="rf-table__num">Durasi</th>
                                <th scope="col" class="rf-table__num">Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attempts as $attempt)
                                <tr>
                                    <td>
                                        {{ $attempt->tryout?->title ?? 'Try out' }}
                                        @if ($attempt->is_late)
                                            <span class="rf-tag">Terlambat</span>
                                        @endif
                                    </td>
                                    <td class="rf-num">{{ $attempt->finished_at?->format('d M Y, H:i') }}</td>
                                    <td class="rf-table__num">{{ $attempt->correct_count }}/{{ $attempt->total_questions }}</td>
                                    <td class="rf-table__num">
                                        @if ($attempt->duration_seconds !== null)
                                            {{ intdiv($attempt->duration_seconds, 60) }}m {{ $attempt->duration_seconds % 60 }}d
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="rf-table__num"><strong>{{ $attempt->score }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="rf-slab" aria-labelledby="ubah-profil-heading">
            <h2 class="h3" id="ubah-profil-heading">Ubah profil</h2>
            <p class="rf-muted rf-small">Username dipakai untuk masuk dan tidak bisa diubah. Kosongkan kolom password bila hanya ingin mengganti nama.</p>

            <form class="rf-profile__form" method="POST" action="{{ route('profile.update') }}" novalidate>
                @csrf
                @method('PUT')

                <div class="rf-field">
                    <label class="rf-label" for="name">Nama</label>
                    <input
                        class="rf-input @error('name') is-invalid @enderror"
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        required
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                    >
                    @error('name')
                        <span class="rf-error" id="name-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="username_readonly">Username</label>
                    <input class="rf-input" id="username_readonly" type="text" value="{{ $user->username }}" disabled>
                </div>

                <hr class="rf-profile__divider">

                <div class="rf-field">
                    <label class="rf-label" for="current_password">Password saat ini</label>
                    <input
                        class="rf-input @error('current_password') is-invalid @enderror"
                        id="current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        @error('current_password') aria-invalid="true" aria-describedby="current-password-error" @enderror
                    >
                    @error('current_password')
                        <span class="rf-error" id="current-password-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="password">Password baru</label>
                    <input
                        class="rf-input @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        aria-describedby="new-password-hint @error('password') new-password-error @enderror"
                        @error('password') aria-invalid="true" @enderror
                    >
                    <span class="rf-hint" id="new-password-hint">Minimal 8 karakter.</span>
                    @error('password')
                        <span class="rf-error" id="new-password-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="password_confirmation">Ulangi password baru</label>
                    <input
                        class="rf-input"
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="rf-btn rf-btn--primary">Simpan perubahan</button>
            </form>
        </section>
    </div>
</div>
@endsection
