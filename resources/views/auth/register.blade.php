@extends('layouts.app')

@section('title', 'Buat akun')

@section('content')
<div class="container rf-auth">
    <div class="rf-auth__grid">
        <div class="rf-slab rf-slab--fold rf-auth__panel">
            <h1>Buat akun baru</h1>
            <p class="rf-muted">Isi data singkat di bawah. Setelah selesai, kamu langsung masuk.</p>

            <form class="rf-auth__form" method="POST" action="{{ route('register.store') }}" novalidate>
                @csrf

                <div class="rf-field">
                    <label class="rf-label" for="name">Nama</label>
                    <input
                        class="rf-input @error('name') is-invalid @enderror"
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        required
                        autofocus
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                    >
                    @error('name')
                        <span class="rf-error" id="name-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="username">Username</label>
                    <input
                        class="rf-input @error('username') is-invalid @enderror"
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        required
                        aria-describedby="username-hint @error('username') username-error @enderror"
                        @error('username') aria-invalid="true" @enderror
                    >
                    <span class="rf-hint" id="username-hint">3 sampai 20 karakter: huruf kecil, angka, atau garis bawah (_).</span>
                    @error('username')
                        <span class="rf-error" id="username-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="email">Email</label>
                    <input
                        class="rf-input @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    >
                    @error('email')
                        <span class="rf-error" id="email-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="password">Password</label>
                    <input
                        class="rf-input @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        aria-describedby="password-hint @error('password') password-error @enderror"
                        @error('password') aria-invalid="true" @enderror
                    >
                    <span class="rf-hint" id="password-hint">Minimal 8 karakter.</span>
                    @error('password')
                        <span class="rf-error" id="password-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="password_confirmation">Ulangi password</label>
                    <input
                        class="rf-input"
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="rf-auth__actions">
                    <button type="submit" class="rf-btn rf-btn--primary rf-btn--block">Buat akun</button>
                </div>
            </form>

            <p class="rf-auth__switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
        </div>

        <aside class="rf-auth__aside" aria-label="Catatan akun">
            <div class="rf-sheet">
                <h2 class="rf-sheet__title">Kenapa perlu akun?</h2>
                <div class="rf-sheet__paper">
                    <p class="mb-0">Skor try out, posisi di leaderboard, riwayat tanya jawab dengan Ruka, dan diskusimu di tiap materi tersimpan di akun ini.</p>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
