# Rumusin Fisika

Rumusin! adalah platform belajar fisika untuk pelajar SMA: materi dan video bertahap, try out bernilai, leaderboard, forum diskusi per materi, dan Ruka, asisten AI yang menjawab sesuai materi yang sedang dibuka.

> **Status: prototype.** Belum untuk produksi dan belum di-deploy. Fitur dikembangkan bertahap.

## Stack

- Laravel 13, PHP 8.3
- SQLite untuk prototype (query dijaga portabel ke PostgreSQL)
- Blade, Bootstrap 5, Sass, JavaScript vanilla, dibangun dengan Vite
- KaTeX untuk rumus

## Instalasi lokal

Prasyarat: PHP 8.3+, Composer, Node.js 22+ dan npm.

```bash
git clone https://github.com/yasaramdh07/Rumusin-Fisika.git
cd Rumusin-Fisika
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # Windows PowerShell: New-Item database/database.sqlite
php artisan migrate
```

Isi variabel di .env sesuai kebutuhan (lihat .env.example). Nilai rahasia seperti SEED_PASSWORD dan AI_API_KEY hanya diisi di .env lokal dan tidak boleh di-commit.

## Menjalankan

```bash
npm run build        # compile aset sekali
php artisan serve    # http://localhost:8000
```

Saat mengembangkan tampilan, jalankan 
pm run dev di terminal lain.

## Tes

```bash
php artisan test
```
