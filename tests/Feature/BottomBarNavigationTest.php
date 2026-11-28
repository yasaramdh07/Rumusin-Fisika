<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BottomBarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_render_guest_bottom_bar_without_navbar_or_sidebar(): void
    {
        foreach (['/', '/materi', '/masuk', '/daftar'] as $url) {
            $response = $this->get($url);

            $response->assertOk()
                ->assertSee('data-bottombar="guest"', false)
                ->assertSee('Beranda')
                ->assertSee('Materi')
                ->assertSee('Try out')
                ->assertSee('Leaderboard')
                ->assertSee('Masuk')
                ->assertDontSee('class="rf-navbar"', false)
                ->assertDontSee('class="rf-admin__side"', false);
        }
    }

    public function test_guest_pressing_tryout_or_leaderboard_redirects_to_login_and_returns_after_login(): void
    {
        $user = User::factory()->create([
            'username' => 'siswa_nav',
            'email' => 'siswa_nav@rumusin.test',
            'password' => Hash::make('Rahasia123!'),
            'role' => 'user',
            'is_active' => true,
        ]);

        // 1. Guest clicks Try out -> redirected to /masuk with intended URL saved
        $this->get('/tryout')->assertRedirect('/masuk');

        // Login returns to /tryout
        $loginTryout = $this->post('/masuk', [
            'identity' => 'siswa_nav',
            'password' => 'Rahasia123!',
        ]);
        $loginTryout->assertRedirect(route('tryouts.index'));

        $this->post('/keluar');

        // 2. Guest clicks Leaderboard -> redirected to /masuk with intended URL saved
        $this->get('/leaderboard')->assertRedirect('/masuk');

        // Login returns to /leaderboard
        $loginLeaderboard = $this->post('/masuk', [
            'identity' => 'siswa_nav',
            'password' => 'Rahasia123!',
        ]);
        $loginLeaderboard->assertRedirect(route('leaderboard.index'));
    }

    public function test_authenticated_user_pages_render_user_bottom_bar_and_profile_has_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        foreach (['/beranda', '/materi', '/tryout', '/leaderboard', '/profil'] as $url) {
            $response = $this->actingAs($user)->get($url);

            $response->assertOk()
                ->assertSee('data-bottombar="user"', false)
                ->assertSee('Beranda')
                ->assertSee('Materi')
                ->assertSee('Try out')
                ->assertSee('Leaderboard')
                ->assertSee('Profil')
                ->assertDontSee('class="rf-navbar"', false);
        }

        $profileResponse = $this->actingAs($user)->get('/profil');
        $profileResponse->assertOk()
            ->assertSee('action="'.url('/keluar').'"', false)
            ->assertSee('Keluar')
            ->assertDontSee('Dasbor Admin');
    }

    public function test_admin_pages_render_admin_bottom_bar_with_more_panel_and_active_states(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        foreach (['/admin', '/admin/materi', '/admin/soal', '/admin/tryout', '/admin/kategori', '/admin/diskusi', '/admin/pengguna'] as $url) {
            $response = $this->actingAs($admin)->get($url);

            $response->assertOk()
                ->assertSee('data-bottombar="admin"', false)
                ->assertSee('Dasbor')
                ->assertSee('Materi')
                ->assertSee('Soal')
                ->assertSee('Try out')
                ->assertSee('Lainnya')
                ->assertSee('Kategori')
                ->assertSee('Diskusi')
                ->assertSee('Pengguna')
                ->assertSee('Kembali ke situs')
                ->assertDontSee('class="rf-admin__side"', false)
                ->assertDontSee('class="rf-navbar"', false);
        }

        // On /admin/kategori, "Lainnya" toggle has is-active and aria-current="page"
        $catResponse = $this->actingAs($admin)->get('/admin/kategori');
        $catResponse->assertOk()
            ->assertSee('data-more-toggle', false)
            ->assertSee('aria-current="page"', false);

        // When admin visits public/user pages (/materi or /profil), user bottom bar is used and /profil links to /admin
        $publicAsAdmin = $this->actingAs($admin)->get('/profil');
        $publicAsAdmin->assertOk()
            ->assertSee('data-bottombar="user"', false)
            ->assertSee('Dasbor Admin');
    }

    public function test_tryout_attempt_uses_focus_actionbar_instead_of_bottom_bar_and_result_restores_bottom_bar(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Mekanika',
            'slug' => 'mekanika',
            'sort_order' => 1,
        ]);

        $lesson = Lesson::create([
            'category_id' => $category->id,
            'title' => 'Gerak Lurus',
            'slug' => 'gerak-lurus-glb-glbb',
            'summary' => 'Ringkasan gerak lurus.',
            'body' => 'Isi materi gerak lurus.',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $question = Question::create([
            'lesson_id' => $lesson->id,
            'body' => 'Berapa jarak tempuh mobil dengan kecepatan $10\text{ m/s}$ selama $5\text{ s}$?',
            'option_a' => '15 m',
            'option_b' => '50 m',
            'option_c' => '25 m',
            'option_d' => '100 m',
            'correct_option' => 'b',
            'explanation' => '$s = v \cdot t = 50\text{ m}$',
            'difficulty' => 1,
        ]);

        $tryout = Tryout::create([
            'title' => 'Try Out Gerak Lurus',
            'slug' => 'tryout-gerak-lurus',
            'description' => 'Uji gerak lurus.',
            'duration_minutes' => 15,
            'max_attempts_per_day' => 3,
            'is_active' => true,
        ]);
        $tryout->questions()->attach($question->id, ['position' => 1]);

        $attempt = TryoutAttempt::create([
            'tryout_id' => $tryout->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(15),
        ]);

        // Focus mode on attempt page: no bottom bar, shows actionbar
        $attemptResponse = $this->actingAs($user)->get(route('tryouts.attempt', $attempt));
        $attemptResponse->assertOk()
            ->assertDontSee('data-bottombar=', false)
            ->assertSee('data-tryout-actionbar', false)
            ->assertSee('Kirim jawaban');

        // Submit attempt -> result page restores user bottom bar
        $this->actingAs($user)->post(route('tryouts.submit', $attempt), [
            'answers' => [$question->id => 'b'],
        ])->assertRedirect(route('tryouts.result', $attempt));

        $resultResponse = $this->actingAs($user)->get(route('tryouts.result', $attempt));
        $resultResponse->assertOk()
            ->assertSee('data-bottombar="user"', false)
            ->assertDontSee('data-tryout-actionbar', false);
    }
}
