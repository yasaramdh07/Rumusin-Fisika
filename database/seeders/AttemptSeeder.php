<?php

namespace Database\Seeders;

use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttemptSeeder extends Seeder
{
    /**
     * Percobaan contoh agar leaderboard tidak kosong. Hanya jika akun dummy ada (environment local).
     * Idempotent: membersihkan percobaan lama milik akun dummy sebelum membuat ulang data contoh.
     */
    public function run(): void
    {
        $dummyUsernames = [
            'usersaturumusin',
            'userduarumusin',
            'usertigarumusin',
            'userempatrumusin',
            'userlimarumusin',
        ];

        $users = User::whereIn('username', $dummyUsernames)->orderBy('id')->get()->values();
        $gerakTryout = Tryout::where('slug', 'tryout-gerak-lurus-glb-glbb')->with('questions')->first();

        if (! $gerakTryout || $users->isEmpty()) {
            $this->command?->warn('AttemptSeeder dilewati: akun dummy atau try out belum ada.');

            return;
        }

        // Hapus percobaan lama milik akun dummy agar seeder idempotent saat dijalankan berulang kali.
        TryoutAttempt::whereIn('user_id', $users->pluck('id'))->delete();

        $now = now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekSpan = max(60, $weekStart->diffInSeconds($now));

        // 1. Percobaan pada Try out Gerak Lurus (12 percobaan: minggu ini, bulan ini, bulan lalu)
        // [indeks user, rasio benar (dari 10), durasi detik, minggu ini?, pecahan minggu / hari lalu]
        $gerakPlan = [
            [0, 9, 420, true, 0.20],
            [1, 10, 540, true, 0.35],
            [2, 8, 380, true, 0.50],
            [3, 10, 610, true, 0.60],
            [4, 6, 700, true, 0.80],
            [0, 7, 500, true, 0.90],
            [2, 9, 450, true, 0.95],
            [0, 10, 310, false, 10], // bulan ini (di luar minggu ini): user 1 naik ke peringkat 1 di tab Bulanan
            [1, 8, 650, false, 15],
            [2, 10, 290, false, 45], // bulan lalu: user 3 naik ke peringkat 1 di tab Semua waktu
            [3, 7, 560, false, 20],
            [4, 9, 480, false, 70],
        ];

        $this->seedAttemptsForTryout($gerakTryout, $users, $gerakPlan, $now, $weekStart, $weekSpan);

        // 2. Percobaan pada Try Out Gabungan Fisika SMA (6 percobaan: sebagian minggu ini, sebagian lebih lama)
        $combinedTryout = Tryout::where('slug', 'tryout-gabungan-fisika-sma')->with('questions')->first();
        if ($combinedTryout && $combinedTryout->questions->isNotEmpty()) {
            $combinedPlan = [
                [0, 9, 780, true, 0.25],
                [1, 8, 820, true, 0.45],
                [2, 10, 910, true, 0.65],
                [3, 7, 740, true, 0.75],
                [4, 9, 690, false, 12],
                [1, 10, 640, false, 40],
            ];

            $this->seedAttemptsForTryout($combinedTryout, $users, $combinedPlan, $now, $weekStart, $weekSpan);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     * @param  array<int, array{0: int, 1: int, 2: int, 3: bool, 4: float|int}>  $plan
     */
    private function seedAttemptsForTryout(
        Tryout $tryout,
        $users,
        array $plan,
        Carbon $now,
        Carbon $weekStart,
        int $weekSpan
    ): void {
        $questions = $tryout->questions->values();
        $total = $questions->count();
        if ($total === 0) {
            return;
        }

        foreach ($plan as $n => [$userIndex, $ratioOutOfTen, $duration, $thisWeek, $when]) {
            $user = $users[$userIndex % $users->count()];
            $correct = (int) round(($ratioOutOfTen / 10) * $total);
            $correct = max(0, min($total, $correct));

            $finishedAt = $thisWeek
                ? $weekStart->copy()->addSeconds((int) floor($weekSpan * $when))
                : $now->copy()->subDays((int) $when)->setTime(10 + ($n % 8), 15 * ($n % 4));
            $startedAt = $finishedAt->copy()->subSeconds($duration);

            $attempt = TryoutAttempt::create([
                'user_id' => $user->id,
                'tryout_id' => $tryout->id,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
                'score' => (int) round($correct / $total * 100),
                'correct_count' => $correct,
                'total_questions' => $total,
                'duration_seconds' => $duration,
                'is_late' => false,
            ]);

            foreach ($questions as $i => $question) {
                $isCorrect = (($i + $n) % $total) < $correct;
                $selected = $question->correct_option;

                if (! $isCorrect) {
                    $wrong = array_values(array_diff(['a', 'b', 'c', 'd'], [$question->correct_option]));
                    $selected = $wrong[($i + $n) % 3];
                }

                $attempt->answers()->create([
                    'question_id' => $question->id,
                    'selected_option' => $selected,
                    'is_correct' => $isCorrect,
                ]);
            }
        }
    }
}
