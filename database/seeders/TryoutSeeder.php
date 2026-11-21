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
        $lesson = Lesson::where('slug', 'gerak-lurus-glb-glbb')->firstOrFail();

        $tryout = Tryout::updateOrCreate(
            ['slug' => 'tryout-gerak-lurus-glb-glbb'],
            [
                'lesson_id' => $lesson->id,
                'title' => 'Try out Gerak Lurus (GLB dan GLBB)',
                'description' => 'Sepuluh soal pilihan ganda tentang GLB, GLBB, dan gerak vertikal. Pembahasan muncul setelah kamu mengirim jawaban.',
                'duration_minutes' => 20,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
            ],
        );

        $sync = [];
        Question::where('lesson_id', $lesson->id)->orderBy('id')->get()
            ->each(function (Question $question, int $i) use (&$sync) {
                $sync[$question->id] = ['position' => $i + 1];
            });

        $tryout->questions()->sync($sync);
    }
}
