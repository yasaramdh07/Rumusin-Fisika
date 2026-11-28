<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\Tryout;
use Illuminate\Database\Seeder;

class TryoutSeeder extends Seeder
{
    public function run(): void
    {
        $lessonTryoutConfigs = [
            'gerak-lurus-glb-glbb' => [
                'slug' => 'tryout-gerak-lurus-glb-glbb',
                'title' => 'Try out Gerak Lurus (GLB dan GLBB)',
                'description' => 'Soal pilihan ganda tentang GLB, GLBB, dan gerak vertikal. Pembahasan muncul setelah kamu mengirim jawaban.',
                'duration_minutes' => 20,
            ],
            'hukum-ohm-dan-rangkaian-listrik' => [
                'slug' => 'tryout-hukum-ohm-dan-rangkaian-listrik',
                'title' => 'Try out Hukum Ohm dan Rangkaian Listrik',
                'description' => 'Uji pemahaman Hukum Ohm, daya listrik, rangkaian seri-paralel, dan perhitungan pemakaian energi listrik.',
                'duration_minutes' => 15,
            ],
            'hukum-newton-tentang-gerak' => [
                'slug' => 'tryout-hukum-newton-tentang-gerak',
                'title' => 'Try out Hukum Newton tentang Gerak',
                'description' => 'Lima soal pilihan ganda tentang Hukum Newton I, II, III, gaya berat, dan resultan gaya.',
                'duration_minutes' => 15,
            ],
            'usaha-dan-energi' => [
                'slug' => 'tryout-usaha-dan-energi',
                'title' => 'Try out Usaha dan Energi',
                'description' => 'Lima soal pilihan ganda tentang usaha, energi kinetik, energi potensial, dan kekekalan energi.',
                'duration_minutes' => 15,
            ],
            'hukum-archimedes' => [
                'slug' => 'tryout-hukum-archimedes',
                'title' => 'Try out Hukum Archimedes',
                'description' => 'Lima soal pilihan ganda tentang gaya apung, massa jenis, berat semu, serta kondisi terapung, melayang, dan tenggelam.',
                'duration_minutes' => 15,
            ],
            'komponen-elektronika-dasar' => [
                'slug' => 'tryout-komponen-elektronika-dasar',
                'title' => 'Try out Komponen Elektronika Dasar',
                'description' => 'Lima soal pilihan ganda tentang resistor, kapasitor, LED, Hukum Ohm, serta rangkaian seri dan paralel.',
                'duration_minutes' => 15,
            ],
        ];

        $mixedPool = collect();

        foreach ($lessonTryoutConfigs as $lessonSlug => $cfg) {
            $lesson = Lesson::where('slug', $lessonSlug)->first();
            if (! $lesson) {
                continue;
            }

            $questions = Question::where('lesson_id', $lesson->id)->orderBy('id')->get();
            $isActive = $questions->count() >= Tryout::MIN_ACTIVE_QUESTIONS;

            $tryout = Tryout::updateOrCreate(
                ['slug' => $cfg['slug']],
                [
                    'lesson_id' => $lesson->id,
                    'title' => $cfg['title'],
                    'description' => $cfg['description'],
                    'duration_minutes' => $cfg['duration_minutes'],
                    'is_active' => $isActive,
                    'starts_at' => null,
                    'ends_at' => null,
                ],
            );

            $sync = [];
            foreach ($questions->values() as $idx => $question) {
                $sync[$question->id] = ['position' => $idx + 1];
            }

            $tryout->questions()->sync($sync);

            // Ambil maksimal 3-4 soal pertama per materi untuk Try Out Gabungan (maks. 20 soal campuran).
            $takeCount = in_array($lessonSlug, ['gerak-lurus-glb-glbb', 'hukum-ohm-dan-rangkaian-listrik'], true) ? 4 : 3;
            $mixedPool = $mixedPool->concat($questions->take($takeCount));
        }

        // Paket Try Out Gabungan (maks. 20 soal campuran lintas materi).
        $combinedQuestions = $mixedPool->take(20)->values();
        $combinedTryout = Tryout::updateOrCreate(
            ['slug' => 'tryout-gabungan-fisika-sma'],
            [
                'lesson_id' => null,
                'title' => 'Try Out Gabungan Fisika SMA',
                'description' => 'Paket campuran 20 soal pilihan ganda dari seluruh materi: Gerak Lurus, Listrik, Hukum Newton, Usaha & Energi, Archimedes, dan Elektronika Dasar.',
                'duration_minutes' => 30,
                'is_active' => $combinedQuestions->count() >= Tryout::MIN_ACTIVE_QUESTIONS,
                'starts_at' => null,
                'ends_at' => null,
            ],
        );

        $combinedSync = [];
        foreach ($combinedQuestions as $idx => $question) {
            $combinedSync[$question->id] = ['position' => $idx + 1];
        }

        $combinedTryout->questions()->sync($combinedSync);
    }
}
