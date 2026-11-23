<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TryoutAttemptService
{
    public const LATE_TOLERANCE_SECONDS = 30;

    /**
     * Hitung sisa kuota percobaan hari ini untuk user dan paket try out tertentu.
     */
    public function remainingDailyAttempts(User $user, Tryout $tryout): int
    {
        $limit = max(1, (int) config('rumusin.tryout_daily_attempts', 3));

        $usedToday = TryoutAttempt::query()
            ->where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->where('started_at', '>=', now()->startOfDay())
            ->where('started_at', '<=', now()->endOfDay())
            ->count();

        return max(0, $limit - $usedToday);
    }

    /**
     * Mulai percobaan baru atau kembalikan percobaan yang belum selesai (finished_at IS NULL).
     */
    public function startOrResume(User $user, Tryout $tryout): TryoutAttempt
    {
        $unfinished = TryoutAttempt::query()
            ->where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->whereNull('finished_at')
            ->latest('started_at')
            ->first();

        if ($unfinished) {
            return $unfinished;
        }

        if ($this->remainingDailyAttempts($user, $tryout) <= 0) {
            $limit = max(1, (int) config('rumusin.tryout_daily_attempts', 3));

            throw ValidationException::withMessages([
                'tryout' => "Batas percobaan hari ini untuk try out ini sudah habis ({$limit} kali per hari). Coba lagi besok.",
            ]);
        }

        $totalQuestions = $tryout->questions()->count();
        if ($totalQuestions === 0) {
            throw ValidationException::withMessages([
                'tryout' => 'Paket try out ini belum memiliki soal.',
            ]);
        }

        return TryoutAttempt::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            'started_at' => now(),
            'finished_at' => null,
            'score' => null,
            'correct_count' => 0,
            'total_questions' => $totalQuestions,
            'duration_seconds' => null,
            'is_late' => false,
        ]);
    }

    /**
     * Urutkan soal secara acak namun deterministik (stabil) untuk satu percobaan.
     * Hanya mengambil kolom yang aman untuk pengerjaan (TANPA correct_option dan explanation).
     *
     * @return Collection<int, Question>
     */
    public function orderedQuestionsForAttempt(TryoutAttempt $attempt): Collection
    {
        $questions = $attempt->tryout()
            ->firstOrFail()
            ->questions()
            ->select([
                'questions.id',
                'questions.lesson_id',
                'questions.body',
                'questions.option_a',
                'questions.option_b',
                'questions.option_c',
                'questions.option_d',
                'questions.difficulty',
            ])
            ->get();

        return $questions
            ->sortBy(fn (Question $q) => hash('sha256', $attempt->id.':'.$attempt->user_id.':'.$q->id))
            ->values();
    }

    /**
     * Urutkan soal dengan urutan stabil yang sama termasuk kunci jawaban & pembahasan (HANYA untuk halaman hasil).
     *
     * @return Collection<int, Question>
     */
    public function orderedQuestionsForReview(TryoutAttempt $attempt): Collection
    {
        $questions = $attempt->tryout()
            ->firstOrFail()
            ->questions()
            ->get()
            ->each(fn (Question $q) => $q->makeVisible(['correct_option', 'explanation']));

        return $questions
            ->sortBy(fn (Question $q) => hash('sha256', $attempt->id.':'.$attempt->user_id.':'.$q->id))
            ->values();
    }

    /**
     * Nilai dan selesaikan percobaan di server.
     *
     * @param  array<int|string, mixed>  $submittedAnswers
     */
    public function submit(TryoutAttempt $attempt, array $submittedAnswers): TryoutAttempt
    {
        if ($attempt->finished_at !== null) {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt, $submittedAnswers): TryoutAttempt {
            $tryout = $attempt->tryout()->firstOrFail();
            $questions = $tryout->questions()->get();
            $total = $questions->count();
            $correctCount = 0;

            foreach ($questions as $question) {
                $rawChoice = $submittedAnswers[$question->id] ?? $submittedAnswers[(string) $question->id] ?? null;
                $selected = is_string($rawChoice) ? strtolower(trim($rawChoice)) : null;

                if (! in_array($selected, ['a', 'b', 'c', 'd'], true)) {
                    $selected = null;
                }

                $isCorrect = $selected !== null && $selected === strtolower((string) $question->getRawOriginal('correct_option'));
                if ($isCorrect) {
                    $correctCount++;
                }

                $attempt->answers()->updateOrCreate(
                    ['question_id' => $question->id],
                    [
                        'selected_option' => $selected,
                        'is_correct' => $isCorrect,
                    ]
                );
            }

            $finishedAt = now();
            $durationSeconds = max(0, (int) $attempt->started_at->diffInSeconds($finishedAt, false));
            $allowedSeconds = ((int) $tryout->duration_minutes * 60) + self::LATE_TOLERANCE_SECONDS;
            $isLate = $durationSeconds > $allowedSeconds;
            $score = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;

            $attempt->update([
                'finished_at' => $finishedAt,
                'score' => $score,
                'correct_count' => $correctCount,
                'total_questions' => $total,
                'duration_seconds' => $durationSeconds,
                'is_late' => $isLate,
            ]);

            return $attempt->fresh();
        });
    }
}
