<?php

namespace Tests\Feature;

use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use App\Services\LeaderboardService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    private function createFinishedAttempt(
        User $user,
        Tryout $tryout,
        int $score,
        int $durationSeconds,
        Carbon $finishedAt
    ): TryoutAttempt {
        return TryoutAttempt::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            'started_at' => $finishedAt->copy()->subSeconds($durationSeconds),
            'finished_at' => $finishedAt,
            'score' => $score,
            'correct_count' => (int) round($score / 10),
            'total_questions' => 10,
            'duration_seconds' => $durationSeconds,
            'is_late' => false,
        ]);
    }

    public function test_period_boundaries_exclude_attempts_just_outside_weekly_and_monthly_ranges(): void
    {
        $tryout = Tryout::create([
            'title' => 'Try Out Batas Waktu',
            'slug' => 'try-out-batas-waktu',
            'duration_minutes' => 20,
            'is_active' => true,
        ]);

        // Referensi waktu: Rabu, 18 November 2026 12:00:00 WIB
        // Minggu ini: Senin 16 Nov 2026 00:00:00 s/d Minggu 22 Nov 2026 23:59:59 WIB
        // Bulan ini: 1 Nov 2026 00:00:00 s/d 30 Nov 2026 23:59:59 WIB
        $reference = Carbon::create(2026, 11, 18, 12, 0, 0, 'Asia/Jakarta');

        $uBeforeMonth = User::factory()->create(['username' => 'sebelum_bulan']);
        $uBeforeWeek = User::factory()->create(['username' => 'sebelum_minggu']);
        $uInsideWeek = User::factory()->create(['username' => 'dalam_minggu']);
        $uAfterWeek = User::factory()->create(['username' => 'setelah_minggu']);
        $uAfterMonth = User::factory()->create(['username' => 'setelah_bulan']);

        // 1 detik sebelum awal bulan (31 Okt 2026 23:59:59 WIB)
        $this->createFinishedAttempt($uBeforeMonth, $tryout, 100, 200, Carbon::create(2026, 10, 31, 23, 59, 59, 'Asia/Jakarta'));

        // 1 detik sebelum awal minggu (Minggu 15 Nov 2026 23:59:59 WIB -> masuk Bulanan, TIDAK masuk Mingguan)
        $this->createFinishedAttempt($uBeforeWeek, $tryout, 95, 220, Carbon::create(2026, 11, 15, 23, 59, 59, 'Asia/Jakarta'));

        // Tepat awal minggu (Senin 16 Nov 2026 00:00:00 WIB -> masuk Mingguan & Bulanan)
        $this->createFinishedAttempt($uInsideWeek, $tryout, 85, 250, Carbon::create(2026, 11, 16, 0, 0, 0, 'Asia/Jakarta'));

        // 1 detik setelah akhir minggu (Senin 23 Nov 2026 00:00:00 WIB -> masuk Bulanan, TIDAK masuk Mingguan)
        $this->createFinishedAttempt($uAfterWeek, $tryout, 90, 240, Carbon::create(2026, 11, 23, 0, 0, 0, 'Asia/Jakarta'));

        // 1 detik setelah akhir bulan (1 Des 2026 00:00:00 WIB -> hanya masuk Semua waktu)
        $this->createFinishedAttempt($uAfterMonth, $tryout, 98, 210, Carbon::create(2026, 12, 1, 0, 0, 0, 'Asia/Jakarta'));

        $service = app(LeaderboardService::class);

        $weeklyUsers = $service->rankings($tryout, LeaderboardService::PERIOD_WEEKLY, $reference)->pluck('username')->all();
        $this->assertSame(['dalam_minggu'], $weeklyUsers);

        $monthlyUsers = $service->rankings($tryout, LeaderboardService::PERIOD_MONTHLY, $reference)->pluck('username')->all();
        $this->assertSame(['sebelum_minggu', 'setelah_minggu', 'dalam_minggu'], $monthlyUsers);

        $allTimeUsers = $service->rankings($tryout, LeaderboardService::PERIOD_ALL_TIME, $reference)->pluck('username')->all();
        $this->assertSame(['sebelum_bulan', 'setelah_bulan', 'sebelum_minggu', 'setelah_minggu', 'dalam_minggu'], $allTimeUsers);
    }

    public function test_one_row_per_user_takes_best_attempt_and_breaks_ties_by_score_duration_and_finished_at(): void
    {
        $tryout = Tryout::create([
            'title' => 'Try Out Tie Break',
            'slug' => 'try-out-tie-break',
            'duration_minutes' => 20,
            'is_active' => true,
        ]);

        $now = Carbon::create(2026, 11, 18, 15, 0, 0, 'Asia/Jakarta');

        $userA = User::factory()->create(['username' => 'pelajar_a']);
        $userB = User::factory()->create(['username' => 'pelajar_b']);
        $userC = User::factory()->create(['username' => 'pelajar_c']);
        $userD = User::factory()->create(['username' => 'pelajar_d']);

        // User A punya 3 percobaan di minggu yang sama: 60 (300d), 90 (500d), dan 90 (360d) -> harus diambil yang 90 (360d)
        $this->createFinishedAttempt($userA, $tryout, 60, 300, $now->copy()->subHours(4));
        $this->createFinishedAttempt($userA, $tryout, 90, 500, $now->copy()->subHours(3));
        $bestAttemptA = $this->createFinishedAttempt($userA, $tryout, 90, 360, $now->copy()->subHours(2));

        // User B punya skor 90 dan durasi sama 360d, tapi selesai 1 jam LEBIH AWAL dari bestAttemptA -> B di atas A
        $this->createFinishedAttempt($userB, $tryout, 90, 360, $now->copy()->subHours(3));

        // User C punya skor 90 tapi durasi lebih cepat (320d) -> C di atas B dan A
        $this->createFinishedAttempt($userC, $tryout, 90, 320, $now->copy()->subHour());

        // User D punya skor tertinggi 100 meski durasi lambat -> D peringkat 1
        $this->createFinishedAttempt($userD, $tryout, 100, 800, $now->copy());

        $service = app(LeaderboardService::class);
        $rankings = $service->rankings($tryout, LeaderboardService::PERIOD_WEEKLY, $now);

        // Hanya 1 baris per user (total 4 baris)
        $this->assertCount(4, $rankings);
        $this->assertSame(['pelajar_d', 'pelajar_c', 'pelajar_b', 'pelajar_a'], $rankings->pluck('username')->all());
        $this->assertSame([1, 2, 3, 4], $rankings->pluck('rank')->all());

        // Pastikan baris User A memakai percobaan terbaiknya (durasi 360, bukan 500 atau skor 60)
        $entryA = $rankings->firstWhere('username', 'pelajar_a');
        $this->assertSame($bestAttemptA->id, $entryA['attempt_id']);
        $this->assertSame(90, $entryA['score']);
        $this->assertSame(360, $entryA['duration_seconds']);
    }

    public function test_leaderboard_page_shows_top_ten_and_appends_current_user_row_when_outside_top_ten(): void
    {
        $tryout = Tryout::create([
            'title' => 'Try Out Akbar',
            'slug' => 'try-out-akbar',
            'duration_minutes' => 20,
            'is_active' => true,
        ]);

        $now = now();

        // Buat 12 user dengan skor menurun 100..45
        $users = collect();
        for ($i = 1; $i <= 12; $i++) {
            $u = User::factory()->create([
                'name' => sprintf('Peserta Ke-%02d', $i),
                'username' => sprintf('peserta_%02d', $i),
            ]);
            $this->createFinishedAttempt($u, $tryout, 105 - ($i * 5), 300 + ($i * 10), $now->copy()->subMinutes(20 - $i));
            $users->push($u);
        }

        $twelfthUser = $users->last();

        $this->actingAs($twelfthUser)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSee('Peserta Ke-01')
            ->assertSee('Peserta Ke-10')
            ->assertDontSee('Peserta Ke-11') // peringkat 11 tidak masuk top 10
            ->assertSee('Posisimu di luar 10 besar pada periode ini:')
            ->assertSee('#12')
            ->assertSee('Peserta Ke-12')
            ->assertSee('Kamu');
    }

    public function test_seeded_data_produces_distinct_sensible_results_across_weekly_monthly_and_all_time_tabs(): void
    {
        app()->detectEnvironment(fn () => 'local');
        config(['rumusin.seed_password' => 'seed-test-secret']);

        $this->seed(DatabaseSeeder::class);

        $tryout = Tryout::where('slug', 'tryout-gerak-lurus-glb-glbb')->firstOrFail();
        $service = app(LeaderboardService::class);

        $weekly = $service->rankings($tryout, LeaderboardService::PERIOD_WEEKLY);
        $monthly = $service->rankings($tryout, LeaderboardService::PERIOD_MONTHLY);
        $allTime = $service->rankings($tryout, LeaderboardService::PERIOD_ALL_TIME);

        $this->assertCount(5, $weekly);
        $this->assertCount(5, $monthly);
        $this->assertCount(5, $allTime);

        $weeklySignature = $weekly->map(fn ($r) => $r['username'].':'.$r['score'].':'.$r['duration_seconds'])->implode('|');
        $monthlySignature = $monthly->map(fn ($r) => $r['username'].':'.$r['score'].':'.$r['duration_seconds'])->implode('|');
        $allTimeSignature = $allTime->map(fn ($r) => $r['username'].':'.$r['score'].':'.$r['duration_seconds'])->implode('|');

        // Ketiga tab menghasilkan urutan/skor terbaik yang berbeda dari data seeder
        $this->assertNotSame($weeklySignature, $monthlySignature);
        $this->assertNotSame($monthlySignature, $allTimeSignature);
        $this->assertNotSame($weeklySignature, $allTimeSignature);
    }
}
