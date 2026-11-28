@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="container rf-auth">
    <x-pagehead :homeUrl="url('/')">
        <a class="rf-btn rf-btn--secondary" href="{{ route('register') }}">Buat akun</a>
    </x-pagehead>

    <div class="rf-auth__grid">
        <div class="rf-slab rf-slab--fold rf-auth__panel">
            <h1>Masuk ke Rumusin</h1>
            <p class="rf-muted">Pakai username atau email akunmu.</p>

            <form class="rf-auth__form" method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf

                <div class="rf-field">
                    <label class="rf-label" for="identity">Username atau email</label>
                    <input
                        class="rf-input @error('identity') is-invalid @enderror"
                        id="identity"
                        name="identity"
                        type="text"
                        value="{{ old('identity') }}"
                        autocomplete="username"
                        required
                        autofocus
                        @error('identity') aria-invalid="true" aria-describedby="identity-error" @enderror
                    >
                    @error('identity')
                        <span class="rf-error" id="identity-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-field">
                    <label class="rf-label" for="password">Password</label>
                    <input
                        class="rf-input @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                    >
                    @error('password')
                        <span class="rf-error" id="password-error"><x-icon name="alert-circle" /> {{ $message }}</span>
                    @enderror
                </div>

                <div class="rf-auth__actions">
                    <button type="submit" class="rf-btn rf-btn--primary rf-btn--block">Masuk</button>
                </div>
            </form>

            <p class="rf-auth__switch">Belum punya akun? <a href="{{ route('register') }}">Buat akun</a></p>
        </div>

        <aside class="rf-auth__aside" aria-label="Contoh rumus">
            <div class="rf-sheet">
                <h2 class="rf-sheet__title">Lembar Rumus: Gerak Lurus</h2>
                <div class="rf-sheet__paper">
                    <div data-tex="v_t = v_0 + a\,t" data-display="block">v_t = v_0 + a t</div>
                    <div data-tex="s = v_0\,t + \tfrac{1}{2}\,a\,t^2" data-display="block">s = v_0 t + 1/2 a t^2</div>
                    <div data-tex="v_t^2 = v_0^2 + 2\,a\,s" data-display="block">v_t^2 = v_0^2 + 2 a s</div>
                </div>
                <p class="rf-sheet__note">Materi bisa tetap dibaca tanpa masuk. Masuk dipakai untuk mengerjakan try out, berdiskusi, dan bertanya ke Ruka.</p>
            </div>
        </aside>
    </div>
</div>
@endsection
