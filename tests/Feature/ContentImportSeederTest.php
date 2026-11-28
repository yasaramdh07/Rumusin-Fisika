<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\LessonVideo;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentImportSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_seeder_matches_legacy_inventory_and_is_idempotent(): void
    {
        app()->detectEnvironment(fn () => 'local');
        config(['rumusin.seed_password' => 'seed-test-secret']);

        // Jalankan pertama kali
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(6, Category::count());
        $this->assertSame(6, Lesson::count());
        $this->assertSame(6, Lesson::where('is_published', true)->count());
        $this->assertSame(6, LessonVideo::count());
        $this->assertSame(37, Question::count()); // 30 soal legacy + 7 soal v0.1 tanpa padanan
        $this->assertSame(7, Tryout::count()); // 6 paket per materi + 1 Try Out Gabungan
        $this->assertSame(7, Tryout::where('is_active', true)->count());
        $this->assertSame(6, User::count());
        $this->assertSame(18, TryoutAttempt::count());

        // Pastikan Try Out Gabungan berisi tepat 20 soal campuran
        $combined = Tryout::where('slug', 'tryout-gabungan-fisika-sma')->firstOrFail();
        $this->assertNull($combined->lesson_id);
        $this->assertSame(20, $combined->questions()->count());

        // Jalankan kedua kali: jumlah baris harus tetap sama persis (idempotent, tanpa duplikat)
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(6, Category::count());
        $this->assertSame(6, Lesson::count());
        $this->assertSame(6, LessonVideo::count());
        $this->assertSame(37, Question::count());
        $this->assertSame(7, Tryout::count());
        $this->assertSame(6, User::count());
        $this->assertSame(18, TryoutAttempt::count());

        // Pastikan ke-6 halaman materi terbit dapat dibuka publik dengan rumus, alat, dan video YouTube masing-masing
        $expectedLessons = [
            'gerak-lurus-glb-glbb' => ['ZM8ECpBuQYE', 'gerak'],
            'hukum-ohm-dan-rangkaian-listrik' => ['WQjGeCGuC1o', 'listrik'],
            'hukum-newton-tentang-gerak' => ['ou9YMWlJgkE', 'gaya-newton'],
            'usaha-dan-energi' => ['qXSFmDzYWnE', 'energi'],
            'hukum-archimedes' => ['sLX_FHeI0FM', 'archimedes'],
            'komponen-elektronika-dasar' => ['dLalv-D_HM8', 'elektronika-dasar'],
        ];

        foreach ($expectedLessons as $slug => [$youtubeId, $toolKey]) {
            $this->get("/materi/{$slug}")
                ->assertOk()
                ->assertSee("youtube-nocookie.com/embed/{$youtubeId}", false)
                ->assertSee("data-physics-tool=\"{$toolKey}\"", false)
                ->assertSee('Simulasi &amp; Kalkulator Rumus', false)
                ->assertSee('Lembar Rumus');
        }
    }
}
