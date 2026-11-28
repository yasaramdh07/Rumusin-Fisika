<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QaAuditTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $admin = User::factory()->create([
            'username' => 'admin_qa',
            'email' => 'admin_qa@rumusin.test',
        ]);
        $admin->role = User::ROLE_ADMIN;
        $admin->is_active = true;
        $admin->save();

        return $admin;
    }

    private function makeStudent(string $suffix = '1'): User
    {
        $user = User::factory()->create([
            'username' => 'pelajar_qa_'.$suffix,
            'email' => 'pelajar_qa_'.$suffix.'@rumusin.test',
        ]);
        $user->role = User::ROLE_USER;
        $user->is_active = true;
        $user->save();

        return $user;
    }

    public function test_admin_can_view_users_page_and_regular_user_cannot(): void
    {
        $admin = $this->makeAdmin();
        $student = $this->makeStudent('1');

        $this->get('/admin/pengguna')->assertRedirect('/masuk');

        $this->actingAs($student)
            ->get('/admin/pengguna')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/pengguna')
            ->assertOk()
            ->assertSee('admin_qa')
            ->assertSee('pelajar_qa_1');
    }

    public function test_list_pages_execute_bounded_queries_without_n_plus_one(): void
    {
        $admin = $this->makeAdmin();

        // Buat 6 kategori dan 12 materi terbit
        $categories = collect();
        for ($c = 1; $c <= 6; $c++) {
            $cat = Category::create([
                'name' => "Kategori QA {$c}",
                'slug' => "kategori-qa-{$c}",
                'description' => "Deskripsi kategori {$c}",
                'sort_order' => $c,
            ]);
            $categories->push($cat);

            for ($l = 1; $l <= 2; $l++) {
                Lesson::create([
                    'category_id' => $cat->id,
                    'title' => "Materi QA {$c}-{$l}",
                    'slug' => "materi-qa-{$c}-{$l}",
                    'summary' => "Ringkasan materi {$c}-{$l}",
                    'body' => 'Isi materi $v = s/t$',
                    'formulas' => '$$s = v\,t$$',
                    'is_published' => true,
                    'sort_order' => $l,
                ]);
            }
        }

        $firstLesson = Lesson::firstOrFail();

        // Buat 6 paket try out aktif dengan 5 soal
        $questionIds = [];
        for ($q = 1; $q <= 5; $q++) {
            $question = Question::create([
                'lesson_id' => $firstLesson->id,
                'body' => "Pertanyaan QA {$q}",
                'option_a' => 'A',
                'option_b' => 'B',
                'option_c' => 'C',
                'option_d' => 'D',
                'correct_option' => 'a',
                'explanation' => 'Pembahasan QA',
                'difficulty' => 1,
            ]);
            $questionIds[$question->id] = ['position' => $q];
        }

        $latestTryout = null;
        for ($t = 1; $t <= 6; $t++) {
            $latestTryout = Tryout::create([
                'lesson_id' => $firstLesson->id,
                'title' => "Try Out QA {$t}",
                'slug' => "tryout-qa-{$t}",
                'description' => "Deskripsi try out {$t}",
                'duration_minutes' => 20,
                'is_active' => true,
            ]);
            $latestTryout->questions()->sync($questionIds);
        }

        // Buat 8 user dengan percobaan selesai
        $viewer = null;
        for ($u = 1; $u <= 8; $u++) {
            $student = $this->makeStudent((string) $u);
            $viewer ??= $student;

            TryoutAttempt::create([
                'tryout_id' => $latestTryout->id,
                'user_id' => $student->id,
                'started_at' => now()->subMinutes(10),
                'finished_at' => now()->subMinutes(2),
                'duration_seconds' => 400 + $u,
                'score' => 80,
                'correct_count' => 4,
                'total_questions' => 5,
                'question_order' => array_keys($questionIds),
                'is_late' => false,
            ]);
        }

        // 1. Cek query count pada /materi (6 kategori, 12 materi -> harus <= 5 query, bukan 1 + 6 + 12)
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/materi')->assertOk();
        $materiQueries = count(DB::getQueryLog());
        $this->assertLessThanOrEqual(6, $materiQueries, "Terdeteksi potensi N+1 di /materi ({$materiQueries} query).");

        // 2. Cek query count pada /tryout (6 try out -> harus <= 6 query)
        DB::flushQueryLog();
        $this->actingAs($viewer)->get('/tryout')->assertOk();
        $tryoutQueries = count(DB::getQueryLog());
        $this->assertLessThanOrEqual(6, $tryoutQueries, "Terdeteksi potensi N+1 di /tryout ({$tryoutQueries} query).");

        // 3. Cek query count pada /leaderboard (8 peserta -> harus <= 6 query)
        DB::flushQueryLog();
        $this->actingAs($viewer)->get('/leaderboard')->assertOk();
        $leaderboardQueries = count(DB::getQueryLog());
        $this->assertLessThanOrEqual(6, $leaderboardQueries, "Terdeteksi potensi N+1 di /leaderboard ({$leaderboardQueries} query).");

        // 4. Cek query count pada /admin/pengguna (9 user -> harus <= 6 query)
        DB::flushQueryLog();
        $this->actingAs($admin)->get('/admin/pengguna')->assertOk();
        $adminUsersQueries = count(DB::getQueryLog());
        $this->assertLessThanOrEqual(6, $adminUsersQueries, "Terdeteksi potensi N+1 di /admin/pengguna ({$adminUsersQueries} query).");

        DB::disableQueryLog();
    }

    public function test_seeded_v02_content_has_no_n_plus_one_and_does_not_leak_answers_or_api_key(): void
    {
        config([
            'rumusin.seed_password' => 'seed-qa-secret',
            'rumusin.ai.api_key' => 'AIzaSyFakeSecretKeyForQaAudit999',
        ]);

        $this->seed([
            \Database\Seeders\CategorySeeder::class,
            \Database\Seeders\LessonSeeder::class,
            \Database\Seeders\QuestionSeeder::class,
            \Database\Seeders\TryoutSeeder::class,
        ]);

        $student = $this->makeStudent('v02');

        // 1. Cek N+1 di /materi pada data hasil seeder lengkap v0.2 (6 kategori, 6 materi, 6 video)
        DB::flushQueryLog();
        DB::enableQueryLog();
        $materiRes = $this->get('/materi');
        $materiRes->assertOk()
            ->assertDontSee('AIzaSyFakeSecretKeyForQaAudit999');
        $this->assertLessThanOrEqual(6, count(DB::getQueryLog()));

        // 2. Cek N+1 di /tryout pada data hasil seeder lengkap v0.2 (7 paket try out aktif, 37 soal)
        DB::flushQueryLog();
        $tryoutRes = $this->actingAs($student)->get('/tryout');
        $tryoutRes->assertOk()
            ->assertSee('Try Out Gabungan Fisika SMA')
            ->assertDontSee('AIzaSyFakeSecretKeyForQaAudit999');
        $this->assertLessThanOrEqual(6, count(DB::getQueryLog()));

        DB::disableQueryLog();

        // 3. Pastikan halaman pengerjaan Try Out Gabungan tidak membocorkan kunci jawaban atau pembahasan atau API key
        $combinedTryout = Tryout::where('slug', 'tryout-gabungan-fisika-sma')->firstOrFail();
        $startRes = $this->actingAs($student)->post(route('tryouts.start', $combinedTryout));
        $attempt = TryoutAttempt::where('user_id', $student->id)
            ->where('tryout_id', $combinedTryout->id)
            ->whereNull('finished_at')
            ->latest('id')
            ->firstOrFail();

        $startRes->assertRedirect(route('tryouts.attempt', $attempt));

        $attemptRes = $this->actingAs($student)->get(route('tryouts.attempt', $attempt));
        $attemptRes->assertOk()
            ->assertSee('data-tryout-actionbar', false)
            ->assertDontSee('data-bottombar=', false)
            ->assertDontSee('correct_option')
            ->assertDontSee('AIzaSyFakeSecretKeyForQaAudit999');
    }
}
