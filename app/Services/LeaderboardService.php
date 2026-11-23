<?php

namespace App\Services;

use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeaderboardService
{
    public const PERIOD_WEEKLY = 'mingguan';

    public const PERIOD_MONTHLY = 'bulanan';

    public const PERIOD_ALL_TIME = 'semua';

    public const TIMEZONE = 'Asia/Jakarta';

    /**
     * Normalisasi kode periode ke salah satu nilai yang didukung.
     */
    public function normalizePeriod(?string $period): string
    {
        return in_array($period, [self::PERIOD_WEEKLY, self::PERIOD_MONTHLY, self::PERIOD_ALL_TIME], true)
            ? (string) $period
            : self::PERIOD_WEEKLY;
    }

    /**
     * Rentang waktu periode dalam zona waktu Asia/Jakarta (WIB).
     * Mingguan: Senin 00:00:00 – Minggu 23:59:59 WIB.
     * Bulanan: tanggal 1 00:00:00 – akhir bulan 23:59:59 WIB.
     * Semua waktu: [null, null].
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    public function periodBounds(string $period, ?Carbon $reference = null): array
    {
        $now = ($reference ? $reference->copy() : now())->timezone(self::TIMEZONE);
        $normalized = $this->normalizePeriod($period);

        if ($normalized === self::PERIOD_WEEKLY) {
            return [
                $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay(),
            ];
        }

        if ($normalized === self::PERIOD_MONTHLY) {
            return [
                $now->copy()->startOfMonth()->startOfDay(),
                $now->copy()->endOfMonth()->endOfDay(),
            ];
        }

        return [null, null];
    }

    /**
     * Hitung seluruh peringkat suatu paket try out pada periode tertentu.
     * Portabel SQLite & PostgreSQL: memfilter rentang waktu dengan parameter Carbon standar
     * lalu memilih percobaan terbaik per user dan mengurutkan tie-break di PHP.
     *
     * Aturan urutan:
     * 1. score tertinggi (DESC)
     * 2. duration_seconds tercepat (ASC)
     * 3. finished_at paling awal (ASC)
     * 4. id terkecil (ASC, penentu deterministik)
     *
     * @return Collection<int, array{
     *     rank: int,
     *     user_id: int,
     *     name: string,
     *     username: string,
     *     score: int,
     *     correct_count: int,
     *     total_questions: int,
     *     duration_seconds: int,
     *     finished_at: Carbon,
     *     attempt_id: int
     * }>
     */
    public function rankings(Tryout $tryout, string $period = self::PERIOD_WEEKLY, ?Carbon $reference = null): Collection
    {
        [$start, $end] = $this->periodBounds($period, $reference);

        $query = TryoutAttempt::query()
            ->with('user:id,name,username')
            ->where('tryout_id', $tryout->id)
            ->whereNotNull('finished_at')
            ->whereNotNull('score');

        if ($start !== null && $end !== null) {
            $query->where('finished_at', '>=', $start)
                ->where('finished_at', '<=', $end);
        }

        $attempts = $query->get();

        // Pilih satu percobaan terbaik per user dalam periode ini.
        $bestByUser = [];

        foreach ($attempts as $attempt) {
            if (! $attempt->user) {
                continue;
            }

            $uid = (int) $attempt->user_id;

            if (! isset($bestByUser[$uid]) || $this->compareAttempts($attempt, $bestByUser[$uid]) < 0) {
                $bestByUser[$uid] = $attempt;
            }
        }

        $bestList = array_values($bestByUser);
        usort($bestList, fn (TryoutAttempt $a, TryoutAttempt $b): int => $this->compareAttempts($a, $b));

        return collect($bestList)->values()->map(function (TryoutAttempt $attempt, int $index): array {
            return [
                'rank' => $index + 1,
                'user_id' => (int) $attempt->user_id,
                'name' => (string) $attempt->user->name,
                'username' => (string) $attempt->user->username,
                'score' => (int) $attempt->score,
                'correct_count' => (int) $attempt->correct_count,
                'total_questions' => (int) $attempt->total_questions,
                'duration_seconds' => (int) ($attempt->duration_seconds ?? 0),
                'finished_at' => $attempt->finished_at,
                'attempt_id' => (int) $attempt->id,
            ];
        });
    }

    /**
     * Ambil 10 besar dan baris milik user yang sedang masuk (jika di luar 10 besar).
     *
     * @return array{
     *     topTen: Collection<int, array<string, mixed>>,
     *     currentUserEntry: ?array<string, mixed>,
     *     isOutsideTopTen: bool,
     *     totalParticipants: int
     * }
     */
    public function boardForUser(
        Tryout $tryout,
        ?User $user,
        string $period = self::PERIOD_WEEKLY,
        ?Carbon $reference = null
    ): array {
        $all = $this->rankings($tryout, $period, $reference);
        $topTen = $all->take(10)->values();

        $currentUserEntry = $user
            ? $all->firstWhere('user_id', (int) $user->id)
            : null;

        $isOutsideTopTen = $currentUserEntry !== null && $currentUserEntry['rank'] > 10;

        return [
            'topTen' => $topTen,
            'currentUserEntry' => $currentUserEntry,
            'isOutsideTopTen' => $isOutsideTopTen,
            'totalParticipants' => $all->count(),
        ];
    }

    /**
     * Bandingkan dua percobaan: negatif jika $a lebih unggul dari $b.
     */
    private function compareAttempts(TryoutAttempt $a, TryoutAttempt $b): int
    {
        $scoreDiff = ((int) $b->score) <=> ((int) $a->score);
        if ($scoreDiff !== 0) {
            return $scoreDiff;
        }

        $durA = $a->duration_seconds !== null ? (int) $a->duration_seconds : PHP_INT_MAX;
        $durB = $b->duration_seconds !== null ? (int) $b->duration_seconds : PHP_INT_MAX;
        $durationDiff = $durA <=> $durB;
        if ($durationDiff !== 0) {
            return $durationDiff;
        }

        $timeA = $a->finished_at ? $a->finished_at->getTimestamp() : PHP_INT_MAX;
        $timeB = $b->finished_at ? $b->finished_at->getTimestamp() : PHP_INT_MAX;
        $timeDiff = $timeA <=> $timeB;
        if ($timeDiff !== 0) {
            return $timeDiff;
        }

        return ((int) $a->id) <=> ((int) $b->id);
    }
}
