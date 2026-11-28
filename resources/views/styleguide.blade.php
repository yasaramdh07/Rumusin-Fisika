@extends('layouts.app')

@section('title', 'Styleguide')

@php
    $swatches = [
        ['--rf-base', '#ECEEF2', 'Dasar semua permukaan'],
        ['--rf-base-hi', '#FFFFFF', 'Sisi terang bayangan'],
        ['--rf-base-lo', '#C8CDD8', 'Sisi gelap bayangan'],
        ['--rf-ink', '#23222B', 'Teks utama'],
        ['--rf-ink-soft', '#5B5A68', 'Teks sekunder'],
        ['--rf-red', '#AF1919', 'Merah Rumus (dari logo)'],
        ['--rf-red-deep', '#8A1010', 'Teks merah, hover'],
        ['--rf-red-soft', '#F4CFCF', 'Latar tipis'],
        ['--rf-yellow', '#E8A627', 'Kuning Rumus (dari logo)'],
        ['--rf-yellow-deep', '#B07408', 'Garis dan ikon'],
        ['--rf-yellow-soft', '#FBE8BD', 'Sorotan'],
        ['--rf-ok', '#176B48', 'Benar, berhasil'],
    ];
@endphp

@section('content')
<div class="container sg">
    <x-pagehead title="Styleguide Rumusin" />
    <header class="sg-intro">
        <p class="rf-prose rf-muted">Semua komponen beserta state-nya. Hover, fokus, dan aktif di sini dipaksa lewat kelas <code>.is-hover</code>, <code>.is-focus</code>, dan <code>.is-pressed</code> supaya bisa dibandingkan berdampingan. Coba juga tekan Tab untuk melihat fokus sungguhan.</p>
    </header>

    <div class="sg-layout">
        <nav class="sg-index" aria-label="Daftar komponen">
            <ul>
                <li><a href="#navigasi">Navigasi bottom bar</a></li>
                <li><a href="#warna">Warna</a></li>
                <li><a href="#tipografi">Tipografi</a></li>
                <li><a href="#elevasi">Elevasi dan slab</a></li>
                <li><a href="#tombol">Tombol</a></li>
                <li><a href="#form">Input dan form</a></li>
                <li><a href="#chip">Chip dan tag</a></li>
                <li><a href="#lembar">Lembar Rumus</a></li>
                <li><a href="#track">Track tahapan</a></li>
                <li><a href="#alert">Alert dan toast</a></li>
                <li><a href="#ruka">Avatar Ruka</a></li>
                <li><a href="#tabel">Tabel admin</a></li>
                <li><a href="#ikon">Ikon</a></li>
                <li><a href="{{ route('styleguide.admin') }}">Layout admin</a></li>
            </ul>
        </nav>

        <div class="sg-body">

            <section id="navigasi" class="sg-section">
                <h2>Navigasi bottom bar, kepala halaman, &amp; mode fokus</h2>
                <p class="rf-prose rf-muted">Seluruh navigasi aplikasi berada di bottom bar tunggal (tanpa navbar atas dan tanpa sidebar). Di bawah ini adalah pratinjau untuk tiga konteks (tamu, user, admin beserta panel "Lainnya"), action bar mode fokus try out, dan kepala halaman (<code>x-pagehead</code>).</p>

                <div class="d-flex flex-column gap-4 mt-4">
                    <div>
                        <h3 class="h5 mb-2">1. Bottom bar konteks Tamu (<code>guest</code>)</h3>
                        <x-bottombar context="guest" :static="true" forceActive="beranda" />
                    </div>
                    <div>
                        <h3 class="h5 mb-2">2. Bottom bar konteks User masuk (<code>user</code>)</h3>
                        <x-bottombar context="user" :static="true" forceActive="materi" />
                    </div>
                    <div style="padding-top: 230px;">
                        <h3 class="h5 mb-2">3. Bottom bar konteks Admin + panel "Lainnya" terbuka (<code>admin</code>)</h3>
                        <x-bottombar context="admin" :static="true" forceActive="lainnya" :openMore="true" />
                    </div>
                    <div>
                        <h3 class="h5 mb-2">4. Action bar mode fokus Try out (<code>x-actionbar</code>)</h3>
                        <x-actionbar
                            :static="true"
                            :questions="[['id' => 1, 'number' => 1], ['id' => 2, 'number' => 2], ['id' => 3, 'number' => 3], ['id' => 4, 'number' => 4], ['id' => 5, 'number' => 5]]"
                            :savedAnswers="[1 => 'a', 2 => 'c']"
                            :remainingSeconds="760"
                            :formId="null"
                        />
                    </div>
                    <div>
                        <h3 class="h5 mb-2">5. Kepala halaman (<code>x-pagehead</code>)</h3>
                        <div class="rf-slab p-4">
                            <x-pagehead title="Contoh Kepala Halaman" subtitle="Menggantikan navbar atas di seluruh halaman" :headingTag="'h4'" />
                        </div>
                    </div>
                </div>
            </section>

            <section id="warna" class="sg-section">
                <h2>Warna</h2>
                <ul class="sg-swatches">
                    @foreach ($swatches as [$token, $hex, $role])
                        <li class="sg-swatch">
                            <span class="sg-swatch__chip" style="background: var({{ $token }})"></span>
                            <span><code>{{ $token }}</code> <span class="rf-num">{{ $hex }}</span><br><span class="rf-muted rf-small">{{ $role }}</span></span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section id="tipografi" class="sg-section">
                <h2>Tipografi</h2>
                <div class="sg-type">
                    <h1>Judul besar 52</h1>
                    <h2>Judul bagian 38</h2>
                    <h3>Judul kecil 28</h3>
                    <h4>Sub-judul 22</h4>
                    <p class="rf-prose">Isi memakai Atkinson Hyperlegible 17px dengan tinggi baris 1,65. Panjang baris dibatasi sekitar 68 karakter supaya enak dibaca. Angka tabel memakai <span class="rf-num">tabular-nums: 1.234,50</span>.</p>
                    <p class="rf-small rf-muted">Teks kecil 14px untuk keterangan dan petunjuk.</p>
                </div>
            </section>

            <section id="elevasi" class="sg-section">
                <h2>Elevasi dan slab</h2>
                <div class="sg-elev">
                    <div class="rf-slab sg-elev__big">
                        <h3 class="h4">Slab besar</h3>
                        <p class="mb-0 rf-muted">Radius 28px, bayangan <code>flat</code>. Wadah untuk konten utama.</p>
                    </div>
                    <div class="rf-slab rf-slab--fold sg-elev__fold">
                        <h3 class="h4">Slab berlipatan</h3>
                        <p class="mb-0 rf-muted">Sudut kanan atas terlipat. Pakai hemat.</p>
                    </div>
                    <div class="rf-tile">Tile radius 18px, <code>low</code></div>
                    <div class="rf-slab rf-slab--inset sg-elev__inset">Inset: input, track, panel</div>
                </div>
            </section>

            <section id="tombol" class="sg-section">
                <h2>Tombol</h2>
                <p class="rf-muted rf-prose">Satu tombol primer per layar. Teks memakai kata kerja, tanpa panah.</p>
                <div class="sg-states" role="table" aria-label="State tombol">
                    @foreach ([
                        ['Primer', 'rf-btn rf-btn--primary', 'Mulai try out'],
                        ['Sekunder', 'rf-btn rf-btn--secondary', 'Tanya Ruka'],
                        ['Tersier', 'rf-btn rf-btn--tertiary', 'Lihat pembahasan'],
                    ] as [$name, $class, $text])
                        <div class="sg-state-row" role="row">
                            <span class="sg-state-name" role="rowheader">{{ $name }}</span>
                            <div class="sg-state-cell"><button type="button" class="{{ $class }}">{{ $text }}</button><span class="sg-cap">Default</span></div>
                            <div class="sg-state-cell"><button type="button" class="{{ $class }} is-hover">{{ $text }}</button><span class="sg-cap">Hover</span></div>
                            <div class="sg-state-cell"><button type="button" class="{{ $class }} is-focus">{{ $text }}</button><span class="sg-cap">Fokus</span></div>
                            <div class="sg-state-cell"><button type="button" class="{{ $class }} is-pressed">{{ $text }}</button><span class="sg-cap">Aktif</span></div>
                            <div class="sg-state-cell"><button type="button" class="{{ $class }}" disabled>{{ $text }}</button><span class="sg-cap">Nonaktif</span></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 d-flex flex-wrap gap-3">
                    <button type="button" class="rf-btn rf-btn--primary"><x-icon name="plus" /> Tambah materi</button>
                    <button type="button" class="rf-btn rf-btn--tertiary"><x-icon name="trash" /> Hapus</button>
                    <button type="button" class="rf-btn rf-btn--tertiary rf-btn--icon" aria-label="Ubah"><x-icon name="edit" /></button>
                </div>
            </section>

            <section id="form" class="sg-section">
                <h2>Input dan form</h2>
                <form class="sg-form" onsubmit="return false">
                    <div class="rf-field">
                        <label class="rf-label" for="sg-name">Nama</label>
                        <input class="rf-input" id="sg-name" type="text" value="Dewi Anggraini">
                        <span class="rf-hint">Default. Label selalu terlihat di atas.</span>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="sg-hover">Username</label>
                        <input class="rf-input is-hover" id="sg-hover" type="text" placeholder="huruf kecil, angka, garis bawah">
                        <span class="rf-hint">Hover</span>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="sg-focus">Email</label>
                        <input class="rf-input is-focus" id="sg-focus" type="email" value="user1@rumusin.test">
                        <span class="rf-hint">Fokus: garis merah dan ring</span>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="sg-error">Password</label>
                        <input class="rf-input is-invalid" id="sg-error" type="password" value="abc" aria-invalid="true" aria-describedby="sg-error-msg">
                        <span class="rf-error" id="sg-error-msg"><x-icon name="alert-circle" /> Password minimal 8 karakter. Tambahkan lima karakter lagi.</span>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="sg-off">Peringkat</label>
                        <input class="rf-input" id="sg-off" type="text" value="Terkunci" disabled>
                        <span class="rf-hint">Nonaktif</span>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="sg-select">Tingkat soal</label>
                        <select class="rf-select" id="sg-select">
                            <option>1 (dasar)</option>
                            <option>2 (menengah)</option>
                            <option>3 (menantang)</option>
                        </select>
                    </div>
                    <div class="rf-field sg-form__wide">
                        <label class="rf-label" for="sg-text">Pertanyaan</label>
                        <textarea class="rf-textarea" id="sg-text" rows="3">Kenapa percepatan di puncak lemparan tetap g?</textarea>
                        <span class="rf-hint">0 dari 2000 karakter</span>
                    </div>
                </form>
            </section>

            <section id="chip" class="sg-section">
                <h2>Chip dan tag</h2>
                <div class="rf-chips">
                    <button type="button" class="rf-chip">Semua</button>
                    <button type="button" class="rf-chip is-hover">Hover</button>
                    <button type="button" class="rf-chip is-focus">Fokus</button>
                    <button type="button" class="rf-chip" aria-pressed="true"><x-icon class="rf-chip__check" name="check" /> Mingguan</button>
                    <button type="button" class="rf-chip" disabled>Nonaktif</button>
                </div>
                <p class="mt-4 mb-0"><span class="rf-tag">Tingkat 2</span> <span class="rf-tag">Gerak</span></p>
            </section>

            <section id="lembar" class="sg-section">
                <h2>Lembar Rumus</h2>
                <div class="sg-sheet">
                    <div class="rf-sheet rf-sheet--tilt">
                        <h3 class="rf-sheet__title">Lembar Rumus: GLBB</h3>
                        <div class="rf-sheet__paper">
                            <div data-tex="v_t = v_0 + a\,t" data-display="block">v_t = v_0 + a t</div>
                            <div data-tex="s = v_0\,t + \tfrac{1}{2}\,a\,t^2" data-display="block">s = v_0 t + 1/2 a t^2</div>
                            <div data-tex="v_t^2 = v_0^2 + 2\,a\,s" data-display="block">v_t^2 = v_0^2 + 2 a s</div>
                        </div>
                        <p class="rf-sheet__note">Kisi 16px, garis tipis. Rumus di atas kertas milimeter.</p>
                    </div>
                    <p class="rf-prose rf-muted">Lembar Rumus menempel di samping isi materi dan sticky di desktop (<code>rf-sheet--sticky</code>). Kemiringan 2° hanya untuk landing.</p>
                </div>
            </section>

            <section id="track" class="sg-section">
                <h2>Track tahapan video</h2>
                <ol class="rf-track sg-track">
                    <li class="rf-track__item">
                        <button type="button" class="rf-track__step"><span class="rf-track__dot">1</span><span class="rf-track__text">Dasar gerak<span class="rf-track__meta">10 menit</span></span></button>
                    </li>
                    <li class="rf-track__item">
                        <button type="button" class="rf-track__step" aria-current="step"><span class="rf-track__dot">2</span><span class="rf-track__text">Gerak lurus beraturan<span class="rf-track__meta">13 menit, sedang diputar</span></span></button>
                    </li>
                    <li class="rf-track__item">
                        <button type="button" class="rf-track__step is-hover"><span class="rf-track__dot">3</span><span class="rf-track__text">Gerak lurus berubah beraturan<span class="rf-track__meta">15 menit</span></span></button>
                    </li>
                    <li class="rf-track__item">
                        <button type="button" class="rf-track__step is-focus"><span class="rf-track__dot">4</span><span class="rf-track__text">Gerak vertikal<span class="rf-track__meta">fokus keyboard</span></span></button>
                    </li>
                </ol>
            </section>

            <section id="alert" class="sg-section">
                <h2>Alert dan toast</h2>
                <div class="sg-alerts">
                    <div class="rf-alert rf-alert--ok" role="status"><x-icon name="check-circle" /><div class="rf-alert__body"><span class="rf-alert__kind">Berhasil</span> Materi diterbitkan.</div></div>
                    <div class="rf-alert rf-alert--error" role="alert"><x-icon name="alert-circle" /><div class="rf-alert__body"><span class="rf-alert__kind">Perlu diperbaiki</span> Link bukan dari YouTube. Tempel link youtube.com atau youtu.be.</div></div>
                    <div class="rf-alert rf-alert--info" role="status"><x-icon name="info" /><div class="rf-alert__body"><span class="rf-alert__kind">Info</span> Batas try out hari ini tersisa 1 kali.</div></div>
                </div>
                <div class="mt-4 d-flex flex-wrap gap-3">
                    <button type="button" class="rf-btn rf-btn--tertiary" onclick="rfToast('Materi diterbitkan', 'ok')">Munculkan toast berhasil</button>
                    <button type="button" class="rf-btn rf-btn--tertiary" onclick="rfToast('Jawaban belum dikirim. Periksa koneksi, lalu coba lagi.', 'error')">Munculkan toast error</button>
                </div>
            </section>

            <section id="ruka" class="sg-section">
                <h2>Avatar Ruka</h2>
                <div class="d-flex align-items-center gap-5">
                    <x-ruka-avatar size="sm" /> <x-ruka-avatar size="md" /> <x-ruka-avatar size="lg" />
                </div>
            </section>

            <section id="tabel" class="sg-section">
                <h2>Tabel admin</h2>
                <div class="rf-table-wrap">
                    <table class="rf-table">
                        <caption class="visually-hidden">Contoh tabel pengguna</caption>
                        <thead><tr><th scope="col">Nama</th><th scope="col">Username</th><th scope="col">Peran</th><th scope="col" class="rf-table__num">Skor terbaik</th></tr></thead>
                        <tbody>
                            <tr><td>Dewi Anggraini</td><td>usersaturumusin</td><td>User</td><td class="rf-table__num">90</td></tr>
                            <tr><td>Raka Pratama</td><td>userduarumusin</td><td>User</td><td class="rf-table__num">100</td></tr>
                            <tr><td>Admin Rumusin</td><td>admin_rumusin</td><td>Admin</td><td class="rf-table__num">0</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="rf-empty">Belum ada diskusi. Jadi yang pertama bertanya.</div>
            </section>

            <section id="ikon" class="sg-section">
                <h2>Ikon</h2>
                <p class="rf-muted rf-prose">Satu set: garis 2px, ujung bulat, 20px.</p>
                <ul class="sg-icons">
                    @foreach (['home','trophy','login','more','check','x','check-circle','alert-circle','info','menu','user','logout','grid','book','list','clipboard','message','tag','plus','edit','trash'] as $icon)
                        <li><x-icon :name="$icon" /> <span class="rf-small">{{ $icon }}</span></li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
</div>
@endsection
