<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TryoutAttemptTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Tryout, 1: Collection<int, Question>}
     */
    private function seedTryoutWithSixQuestions(): array
    {
        $tryout = Tryout::create([
            'title' => 'Try Out Gerak Lurus',
            'slug' => 'try-out-gerak-lurus',
            'description' => 'Enam soal gerak lurus.',
            'duration_minutes' => 10,
            'is_active' => true,
        ]);

        $correctOptions = ['a', 'b', 'c', 'd', 'a', 'c'];
        $questions = collect();

        foreach ($correctOptions as $idx => $correct) {
            $num = $idx + 1;
            $q = Question::create([
                'body' => "Pertanyaan fisika ke-{$num}: berapakah nilai \$v_{$num}\$?",
                'option_a' => "OpsiA{$num}",
                'option_b' => "OpsiB{$num}",
                'option_c' => "OpsiC{$num}",
                'option_d' => "OpsiD{$num}",
                'correct_option' => $correct,
                'explanation' => "RAHASIA_PEMBAHASAN_SOAL_{$num}",
                'difficulty' => (($idx) % 3) + 1,
            ]);
            $questions->push($q);
            $tryout->questions()->attach($q->id, ['position' => $num]);
        }

        return [$tryout, $questions];
    }

    public function test_attempt_page_never_leaks_answer_key_or_explanation_and_keeps_stable_shuffled_order(): void
    {
        [$tryout] = $this->seedTryoutWithSixQuestions();
        $user = User::factory()->create();

        // Mulai percobaan
        $this->actingAs($user)
            ->post(route('tryouts.start', $tryout))
            ->assertRedirect();

        $attempt = TryoutAttempt::firstOrFail();

        // Jika klik mulai lagi saat belum selesai, lanjutkan percobaan yang sama (jangan buat baru)
        $this->actingAs($user)
            ->post(route('tryouts.start', $tryout))
            ->assertRedirect(route('tryouts.attempt', $attempt));

        $this->assertSame(1, TryoutAttempt::count());

        $firstResponse = $this->actingAs($user)
            ->get(route('tryouts.attempt', $attempt))
            ->assertOk();

        $html1 = $firstResponse->getContent();

        // Pastikan pembahasan dan kunci jawaban sama sekali tidak dikirim ke browser saat pengerjaan
        for ($i = 1; $i <= 6; $i++) {
            $this->assertStringNotContainsString("RAHASIA_PEMBAHASAN_SOAL_{$i}", $html1);
        }
        $this->assertStringNotContainsString('correct_option', $html1);
        $this->assertStringNotContainsString('Jawaban benar', $html1);

        // Urutan soal stabil saat halaman di-refresh pada percobaan yang sama
        $secondResponse = $this->actingAs($user)
            ->get(route('tryouts.attempt', $attempt))
            ->assertOk();

        preg_match_all('/Pertanyaan fisika ke-(\d+)/', $html1, $order1);
        preg_match_all('/Pertanyaan fisika ke-(\d+)/', $secondResponse->getContent(), $order2);

        $this->assertCount(6, $order1[1]);
        $this->assertSame($order1[1], $order2[1]);
    }

    public function test_server_calculates_score_ignores_client_manipulation_and_shows_result_and_profile_history(): void
    {
        [$tryout, $questions] = $this->seedTryoutWithSixQuestions();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tryouts.start', $tryout));
        $attempt = TryoutAttempt::firstOrFail();

        // Jawab 4 benar dari 6 soal -> round(4 / 6 * 100) = 67
        // Sekaligus kirim field palsu score=100 & is_correct=1 untuk memastikan server mengabaikannya
        $answers = [
            $questions[0]->id => 'a', // benar
            $questions[1]->id => 'b', // benar
            $questions[2]->id => 'c', // benar
            $questions[3]->id => 'd', // benar
            $questions[4]->id => 'd', // salah (harusnya a)
            // soal ke-6 dikosongkan (salah)
        ];

        $this->actingAs($user)
            ->post(route('tryouts.submit', $attempt), [
                'answers' => $answers,
                'score' => 100,
                'correct_count' => 6,
                'is_late' => false,
            ])
            ->assertRedirect(route('tryouts.result', $attempt));

        $attempt->refresh();
        $this->assertNotNull($attempt->finished_at);
        $this->assertSame(4, $attempt->correct_count);
        $this->assertSame(6, $attempt->total_questions);
        $this->assertSame(67, $attempt->score);
        $this->assertFalse($attempt->is_late);

        // Halaman hasil menampilkan skor, Benar/Salah, dan pembahasan yang tadinya disembunyikan
        $this->actingAs($user)
            ->get(route('tryouts.result', $attempt))
            ->assertOk()
            ->assertSee('67')
            ->assertSee('4 dari 6 soal')
            ->assertSee('Benar')
            ->assertSee('Salah')
            ->assertSee('RAHASIA_PEMBAHASAN_SOAL_1');

        // Halaman profil menampilkan riwayat percobaan & skor terbaik
        $this->actingAs($user)
            ->get('/profil')
            ->assertOk()
            ->assertSee('Try Out Gerak Lurus')
            ->assertSee('67 / 100')
            ->assertSee('4/6');
    }

    public function test_other_users_cannot_view_or_submit_someone_elses_attempt(): void
    {
        [$tryout, $questions] = $this->seedTryoutWithSixQuestions();
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($owner)->post(route('tryouts.start', $tryout));
        $attempt = TryoutAttempt::firstOrFail();

        // Intruder tidak bisa membuka halaman pengerjaan maupun mengirim jawaban
        $this->actingAs($intruder)
            ->get(route('tryouts.attempt', $attempt))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->post(route('tryouts.submit', $attempt), [
                'answers' => [$questions[0]->id => 'a'],
            ])
            ->assertForbidden();

        // Selesaikan oleh owner, intruder tetap tidak bisa melihat halaman hasil
        $this->actingAs($owner)
            ->post(route('tryouts.submit', $attempt), [
                'answers' => [$questions[0]->id => 'a'],
            ]);

        $this->actingAs($intruder)
            ->get(route('tryouts.result', $attempt))
            ->assertForbidden();
    }

    public function test_daily_attempt_limit_is_enforced_per_tryout_per_day(): void
    {
        config(['rumusin.tryout_daily_attempts' => 2]);

        [$tryout, $questions] = $this->seedTryoutWithSixQuestions();
        $user = User::factory()->create();

        // Percobaan 1 selesai
        $this->actingAs($user)->post(route('tryouts.start', $tryout));
        $first = TryoutAttempt::latest('id')->firstOrFail();
        $this->actingAs($user)->post(route('tryouts.submit', $first), [
            'answers' => [$questions[0]->id => 'a'],
        ]);

        // Percobaan 2 selesai
        $this->actingAs($user)->post(route('tryouts.start', $tryout));
        $second = TryoutAttempt::latest('id')->firstOrFail();
        $this->assertNotSame($first->id, $second->id);
        $this->actingAs($user)->post(route('tryouts.submit', $second), [
            'answers' => [$questions[0]->id => 'a'],
        ]);

        // Percobaan ke-3 di hari yang sama ditolak
        $this->actingAs($user)
            ->from(route('tryouts.show', $tryout))
            ->post(route('tryouts.start', $tryout))
            ->assertRedirect(route('tryouts.show', $tryout))
            ->assertSessionHasErrors('tryout');

        $this->assertSame(2, TryoutAttempt::count());
    }

    public function test_late_submission_respects_thirty_second_tolerance_and_still_grades_answers(): void
    {
        [$tryout, $questions] = $this->seedTryoutWithSixQuestions(); // durasi 10 menit = 600 detik
        $user = User::factory()->create();

        // 1. Dalam toleransi 30 detik (625 detik sejak mulai) -> is_late = false
        $onTimeAttempt = TryoutAttempt::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            'started_at' => now()->subSeconds(625),
            'total_questions' => 6,
        ]);

        $this->actingAs($user)->post(route('tryouts.submit', $onTimeAttempt), [
            'answers' => [$questions[0]->id => 'a'],
        ]);

        $this->assertFalse($onTimeAttempt->fresh()->is_late);

        // 2. Melewati batas + toleransi 30 detik (640 detik sejak mulai) -> is_late = true namun tetap dinilai
        $lateAttempt = TryoutAttempt::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            'started_at' => now()->subSeconds(640),
            'total_questions' => 6,
        ]);

        $this->actingAs($user)->post(route('tryouts.submit', $lateAttempt), [
            'answers' => [
                $questions[0]->id => 'a',
                $questions[1]->id => 'b',
                $questions[2]->id => 'c',
            ],
        ]);

        $lateAttempt->refresh();
        $this->assertTrue($lateAttempt->is_late);
        $this->assertSame(50, $lateAttempt->score);
        $this->assertSame(3, $lateAttempt->correct_count);
    }
}
