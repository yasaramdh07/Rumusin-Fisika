# Rumusin Fisika

**"Rumusin soalnya, paham fisikanya."**  
Platform belajar fisika SMA (versi *prototype*) yang menggabungkan materi tertulis dengan **Lembar Rumus** bergaya kertas milimeter, tahapan video terstruktur, kalkulator dan simulasi interaktif, *try out* bernilai di server, *leaderboard* periodik, forum diskusi per materi, serta **Ruka** — asisten belajar AI kontekstual.

Dikembangkan oleh **Ilyasa Ramadhany** ([ilyasaramadhany.my.id](https://ilyasaramadhany.my.id)).

---

## Status Proyek: Prototype

Repositori ini merupakan **prototype fungsional** dari Rumusin Fisika yang mencakup:

- **Materi & Lembar Rumus**: Enam kategori fisika SMA (*Gerak*, *Listrik*, *Gaya / Hukum Newton*, *Energi*, *Hukum Archimedes*, *Elektronika Dasar*) dengan materi Markdown + rumus LaTeX (KaTeX) yang disanitasi di server.
- **Tahapan Video**: Daftar video YouTube bertahap (*step track*) yang dimuat secara *lazy* lewat `youtube-nocookie.com`.
- **Simulasi & Kalkulator Rumus**: Enam modul kalkulator & simulasi fisika interaktif (*Gerak Lurus GLB/GLBB*, *Listrik & Tagihan Rumahan*, *Gaya Newton*, *Usaha & Energi*, *Archimedes*, *Elektronika Dasar*) serta kalkulator mini langsung di Lembar Rumus halaman depan.
- **Try Out & Penilaian Server**: Paket *try out* berdurasi dengan urutan soal diacak secara deterministik per percobaan, batas percobaan harian (`TRYOUT_DAILY_ATTEMPTS`), penilaian 100% di server (kunci jawaban dan pembahasan tidak pernah dikirim ke browser saat pengerjaan), dan halaman peninjauan hasil.
- **Leaderboard**: Peringkat mingguan (Senin–Minggu WIB), bulanan, dan semua waktu berdasarkan satu percobaan terbaik tiap pelajar (skor tertinggi → durasi tercepat → selesai paling awal).
- **Diskusi per Materi**: Thread tanya jawab teks polos dengan balasan satu tingkat dan moderasi admin.
- **Ruka (Asisten AI per Materi)**: Panel tanya jawab kontekstual yang memahami isi dan Lembar Rumus materi yang sedang dibuka, menggunakan provider `fake` (untuk pengembangan/pengujian lokal) atau **Google AI Studio (Gemini)** di server.
- **Panel Admin (`/admin`)**: Kelola kategori, materi, video bertahap, bank soal dengan pratinjau KaTeX, paket *try out*, moderasi diskusi, dan daftar pengguna terdaftar.

---

## Tumpukan Teknologi

- **Backend**: PHP 8.3+, Laravel 13
- **Database**: SQLite (default pengembangan lokal; skema dan *query* portabel ke PostgreSQL)
- **Frontend**: Blade (Server-Side Rendered), Bootstrap 5.3 (SCSS modular dengan *design system* Neumorphism + Claymorphism kustom), Vanilla JS, Vite
- **Tipografi & Rumus**: *Bricolage Grotesque*, *Atkinson Hyperlegible*, KaTeX, `league/commonmark`

---

## Cara Install dari Nol (Lokal)

### 1. Prasyarat
Pastikan perangkat sudah terpasang:
- **PHP >= 8.2** (beserta ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `curl`)
- **Composer 2.x**
- **Node.js >= 20** dan **npm**

### 2. Kloning & Pasang Dependensi

```bash
git clone https://github.com/yasaramdh07/Rumusin-Fisika.git
cd Rumusin-Fisika
composer install
npm install
```

### 3. Salin File `.env` & Siapkan Database SQLite

```bash
cp .env.example .env
php artisan key:generate
```

Buat file database SQLite kosong bila belum ada:

- **macOS / Linux / Git Bash**:
  ```bash
  touch database/database.sqlite
  ```
- **Windows PowerShell**:
  ```powershell
  New-Item -ItemType File -Path database/database.sqlite -Force
  ```

### 4. Atur `SEED_PASSWORD` & Jalankan Migrasi + Seeder

Seeder membuat **1 akun admin** dan **5 akun pelajar dummy**, 6 kategori fisika, materi lengkap *Gerak Lurus (GLB dan GLBB)* beserta tahapan video, 10 soal fisika, 1 paket *try out* aktif, dan contoh riwayat percobaan untuk *leaderboard*.

Buka file `.env`, lalu isi variabel `SEED_PASSWORD` dengan password lokal pilihan Anda (minimal 8 karakter; nilai ini **wajib** diisi sebelum menjalankan `--seed` dan tidak pernah disimpan di kode atau repo):

```dotenv
SEED_PASSWORD=isi_password_lokal_anda_di_sini
```

Jalankan migrasi dan seeder:

```bash
php artisan migrate:fresh --seed
```

Setelah seeder selesai, Anda bisa masuk (`/masuk`) memakai password dari `SEED_PASSWORD` di `.env` Anda dengan salah satu akun berikut:

| Peran | Username | Email |
| :--- | :--- | :--- |
| **Admin** | `admin_rumusin` | `admin@rumusin.test` |
| **Pelajar 1** | `usersaturumusin` | `usersatu@rumusin.test` |
| **Pelajar 2** | `userduarumusin` | `userdua@rumusin.test` |
| **Pelajar 3** | `usertigarumusin` | `usertiga@rumusin.test` |
| **Pelajar 4** | `userempatrumusin` | `userempat@rumusin.test` |
| **Pelajar 5** | `userlimarumusin` | `userlima@rumusin.test` |

### 5. (Opsional) Konfigurasi Asisten AI Ruka

Secara bawaan `.env.example` memakai `AI_PROVIDER=fake` sehingga fitur chat Ruka langsung dapat dicoba secara lokal tanpa koneksi API eksternal.

Untuk mengaktifkan **Google AI Studio (Gemini)**, ubah variabel berikut di `.env`:

```dotenv
AI_PROVIDER=google
AI_API_KEY=isi_api_key_google_ai_studio_anda
AI_MODEL=gemini-2.5-flash
RUKA_DAILY_LIMIT=20
```

> **Catatan keamanan**: `AI_API_KEY` hanya dibaca di server melalui `config('rumusin.ai.api_key')` dan tidak pernah dikirim ke browser ataupun dicatat ke log.

### 6. Bangun Aset & Jalankan Server

Untuk pengembangan harian (dua terminal):

```bash
php artisan serve
npm run dev
```

Atau bangun aset produksi terlebih dahulu:

```bash
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser.  
Pada mode `APP_ENV=local`, halaman pemeriksaan komponen desain juga tersedia di `http://127.0.0.1:8000/styleguide`.

---

## Menjalankan Pengujian Otomatis

Seluruh pengujian (*unit* & *feature test*) menggunakan SQLite *in-memory* dan `FakeRukaProvider` sehingga tidak menyentuh database lokal maupun kuota API eksternal:

```bash
php artisan test
```

---

## Struktur Proyek

```text
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controller publik, pelajar, dan Admin/
│   │   └── Middleware/         # EnsureUserIsAdmin (proteksi rute /admin/*)
│   ├── Models/                 # User, Category, Lesson, LessonVideo, Question, Tryout,
│   │                           # TryoutAttempt, TryoutAnswer, Thread, Reply, RukaMessage
│   ├── Services/
│   │   ├── Ruka/               # RukaProvider (interface), FakeRukaProvider, GoogleAiStudioRukaProvider
│   │   ├── LeaderboardService.php
│   │   ├── LessonRenderer.php  # Markdown + proteksi delimiter KaTeX + sanitasi HTML
│   │   ├── RukaService.php     # System prompt, konteks materi, batas harian
│   │   └── TryoutAttemptService.php
│   └── Support/
│       └── YoutubeUrlParser.php
├── config/
│   └── rumusin.php             # Konfigurasi kuota try out, Ruka, dan AI provider
├── database/
│   ├── factories/              # Factory model untuk pengujian
│   ├── migrations/             # Skema portabel SQLite & PostgreSQL
│   └── seeders/                # AdminUserSeeder, DummyUsersSeeder, CategorySeeder,
│                               # LessonSeeder, QuestionSeeder, TryoutSeeder
├── resources/
│   ├── js/
│   │   ├── tools/              # Modul kalkulator & simulasi per topik fisika
│   │   └── app.js              # KaTeX auto-render, video switcher, timer try out, Ruka chat
│   ├── scss/
│   │   ├── abstracts/          # Token warna (--rf-*), tipografi, mixin elevasi clay/neumorphic
│   │   ├── base/               # Reset, tipografi, focus ring (:focus-visible)
│   │   ├── components/         # Tombol, slab, formula-sheet, form, chip, track, chat, tools, dll.
│   │   └── pages/              # Gaya spesifik halaman (landing, lesson, tryout, admin, dll.)
│   └── views/                  # Template Blade (layouts, welcome, home, lessons, tryouts,
│                               # leaderboard, profile, auth, admin)
├── routes/
│   └── web.php                 # Definisi rute publik, auth, dan admin
└── tests/
    ├── Feature/                # Pengujian fitur end-to-end (Auth, Lesson, Admin, Tryout,
    │                           # Leaderboard, Discussion, Ruka, QaAudit)
    └── Unit/                   # Pengujian unit (YoutubeUrlParser, PhysicsCalculator)
```
