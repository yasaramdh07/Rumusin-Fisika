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
     */
    public function run(): void
    {
        $tryout = Tryout::where('slug', 'tryout-gerak-lurus-glb-glbb')->with('questions')->first();
        $users = User::where('role', User::ROLE_USER)->orderBy('id')->get()->values();

        if (! $tryout || $users->isEmpty()) {
            $this->command?->warn('AttemptSeeder dilewati: akun dummy atau try out belum ada.');

            return;
        }

        $now = now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekSpan = max(60, $weekStart->diffInSeconds($now));

        // [indeks user, jumlah benar, durasi detik, minggu ini? , pecahan minggu / hari lalu]
        $plan = [
            [0, 9, 420, true, 0.20],
            [1, 10, 540, true, 0.35],
            [2, 8, 380, true, 0.50],
            [3, 10, 610, true, 0.60], // skor sama dengan user 2, lebih lambat
            [4, 6, 700, true, 0.80],
            [0, 7, 500, true, 0.90],
            [2, 9, 450, true, 0.95],
            [0, 5, 800, false, 12],
            [1, 8, 650, false, 15],
            [2, 10, 300, false, 40],
            [3, 7, 560, false, 25],
            [4, 9, 480, false, 70],
        ];

        foreach ($plan as $n => [$userIndex, $correct, $duration, $thisWeek, $when]) {
            $user = $users[$userIndex % $users->count()];

            $finishedAt = $thisWeek
                ? $weekStart->copy()->addSeconds((int) floor($weekSpan * $when))
                : $now->copy()->subDays((int) $when)->setTime(10 + ($n % 8), 15 * ($n % 4));
            $startedAt = $finishedAt->copy()->subSeconds($duration);

            $attempt = TryoutAttempt::create([
                'user_id' => $user->id,
                'tryout_id' => $tryout->id,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
                'score' => (int) round($correct / max(1, $tryout->questions->count()) * 100),
                'correct_count' => $correct,
                'total_questions' => $tryout->questions->count(),
                'duration_seconds' => $duration,
                'is_late' => false,
            ]);

            // Soal yang dijawab benar bergeser per percobaan agar polanya tidak seragam.
            $questions = $tryout->questions->values();
            $total = $questions->count();
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
