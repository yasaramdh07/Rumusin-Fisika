<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;
use RuntimeException;

class QuestionSeeder extends Seeder
{
    /**
     * Impor seluruh soal quiz dari database/seeders/content/questions.json secara idempotent.
     * Mencakup 30 soal asli dari legacy/ + 7 soal v0.1 yang tidak memiliki padanan di legacy/.
     */
    public function run(): void
    {
        $path = database_path('seeders/content/questions.json');
        if (! is_file($path)) {
            throw new RuntimeException("Berkas konten soal tidak ditemukan: {$path}");
        }

        /** @var array<int, array<string, mixed>> $items */
        $items = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $lessonsBySlug = Lesson::query()->pluck('id', 'slug');

        // 3 soal buatan agent dari v0.1 yang digantikan oleh padanan asli dari legacy/:
        $obsoleteAgentBodies = [
            'Sebuah mobil melaju dengan kecepatan tetap 72 km/jam. Jarak yang ditempuh mobil selama 20 menit adalah ...',
            'Sebuah sepeda motor mulai bergerak dari keadaan diam dengan percepatan tetap $2\ \text{m/s}^2$. Kecepatan motor setelah 6 sekon adalah ...',
            'Sebuah mobil bergerak dengan kecepatan awal $10\ \text{m/s}$ dan percepatan tetap $2\ \text{m/s}^2$. Jarak yang ditempuh mobil selama 5 sekon adalah ...',
        ];

        Question::query()->whereIn('body', $obsoleteAgentBodies)->delete();

        foreach ($items as $item) {
            $lessonSlug = (string) ($item['lesson_slug'] ?? '');
            $lessonId = $lessonSlug !== '' ? ($lessonsBySlug[$lessonSlug] ?? null) : null;

            Question::updateOrCreate(
                ['body' => (string) $item['body']],
                [
                    'lesson_id' => $lessonId,
                    'option_a' => (string) $item['option_a'],
                    'option_b' => (string) $item['option_b'],
                    'option_c' => (string) $item['option_c'],
                    'option_d' => (string) $item['option_d'],
                    'correct_option' => strtolower((string) $item['correct_option']),
                    'explanation' => (string) $item['explanation'],
                    'difficulty' => (int) ($item['difficulty'] ?? 1),
                ],
            );
        }
    }
}
