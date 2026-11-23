<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTryoutQuestionTest extends TestCase
{
    use RefreshDatabase;

    private function makeQuestion(int $number, ?int $lessonId = null): Question
    {
        return Question::create([
            'lesson_id' => $lessonId,
            'body' => "Soal fisika nomor {$number} dengan rumus \$v_{$number} = s / t\$?",
            'option_a' => '10 m/s',
            'option_b' => '20 m/s',
            'option_c' => '30 m/s',
            'option_d' => '40 m/s',
            'correct_option' => 'b',
            'explanation' => "Pembahasan langkah demi langkah untuk soal {$number}.",
            'difficulty' => (($number - 1) % 3) + 1,
        ]);
    }

    public function test_admin_can_crud_questions_and_preview_katex(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create(['name' => 'Gerak', 'slug' => 'gerak', 'sort_order' => 1]);
        $lesson = Lesson::create([
            'category_id' => $category->id,
            'title' => 'Gerak Lurus',
            'slug' => 'gerak-lurus',
            'summary' => 'Ringkasan',
            'body' => 'Isi',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        // Pratinjau soal merender KaTeX dengan LessonRenderer dan membuang HTML mentah
        $this->actingAs($admin)
            ->postJson(route('admin.questions.preview'), [
                'body' => 'Berapa nilai $v_t = v_0 + a\,t$? <script>alert(1)</script>',
                'option_a' => '$10\ \text{m/s}$',
                'option_b' => '$20\ \text{m/s}$',
                'option_c' => '$30\ \text{m/s}$',
                'option_d' => '$40\ \text{m/s}$',
                'explanation' => 'Gunakan $$v_t = v_0 + a\,t$$',
            ])
            ->assertOk()
            ->assertJsonFragment(['option_a_html' => '<p>$10\ \text{m/s}$</p>'])
            ->assertDontSee('<script>', false);

        // Buat soal baru
        $this->actingAs($admin)
            ->post(route('admin.questions.store'), [
                'lesson_id' => $lesson->id,
                'body' => 'Benda bergerak dengan $v = 15\ \text{m/s}$ selama 4 sekon. Jaraknya?',
                'option_a' => '30 m',
                'option_b' => '45 m',
                'option_c' => '60 m',
                'option_d' => '75 m',
                'correct_option' => 'c',
                'explanation' => 'Pakai $s = v \cdot t = 15 \times 4 = 60\ \text{m}$.',
                'difficulty' => 2,
            ])
            ->assertRedirect(route('admin.questions.index'));

        $question = Question::firstOrFail();
        $this->assertSame('c', $question->correct_option);
        $this->assertSame(2, $question->difficulty);

        // Ubah soal
        $this->actingAs($admin)
            ->put(route('admin.questions.update', $question), [
                'lesson_id' => null,
                'body' => 'Pertanyaan diperbarui',
                'option_a' => '1 m',
                'option_b' => '2 m',
                'option_c' => '3 m',
                'option_d' => '4 m',
                'correct_option' => 'a',
                'explanation' => 'Pembahasan diperbarui',
                'difficulty' => 3,
            ])
            ->assertRedirect(route('admin.questions.index'));

        $question->refresh();
        $this->assertNull($question->lesson_id);
        $this->assertSame('a', $question->correct_option);
        $this->assertSame(3, $question->difficulty);
    }

    public function test_question_used_in_completed_attempt_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();

        $usedQuestion = $this->makeQuestion(1);
        $unusedQuestion = $this->makeQuestion(2);

        $tryout = Tryout::create([
            'title' => 'Paket Uji',
            'slug' => 'paket-uji',
            'duration_minutes' => 15,
            'is_active' => true,
        ]);

        // Percobaan yang sudah selesai (finished_at terisi)
        $finishedAttempt = TryoutAttempt::create([
            'user_id' => $student->id,
            'tryout_id' => $tryout->id,
            'started_at' => now()->subMinutes(10),
            'finished_at' => now()->subMinutes(2),
            'score' => 100,
            'correct_count' => 1,
            'total_questions' => 1,
            'duration_seconds' => 480,
            'is_late' => false,
        ]);

        $finishedAttempt->answers()->create([
            'question_id' => $usedQuestion->id,
            'selected_option' => 'b',
            'is_correct' => true,
        ]);

        // Coba hapus soal yang sudah dipakai di percobaan selesai -> ditolak
        $this->actingAs($admin)
            ->delete(route('admin.questions.destroy', $usedQuestion))
            ->assertRedirect(route('admin.questions.index'))
            ->assertSessionHas('error');

        $this->assertStringContainsString(
            'tidak bisa dihapus karena sudah dipakai pada percobaan try out yang selesai',
            (string) session('error')
        );
        $this->assertDatabaseHas('questions', ['id' => $usedQuestion->id]);

        // Soal yang belum pernah dipakai di percobaan selesai bisa dihapus
        $this->actingAs($admin)
            ->delete(route('admin.questions.destroy', $unusedQuestion))
            ->assertRedirect(route('admin.questions.index'))
            ->assertSessionHas('status', 'Soal dihapus.');

        $this->assertDatabaseMissing('questions', ['id' => $unusedQuestion->id]);
    }

    public function test_tryout_activation_requires_at_least_five_questions(): void
    {
        $admin = User::factory()->admin()->create();
        $q1 = $this->makeQuestion(1);
        $q2 = $this->makeQuestion(2);
        $q3 = $this->makeQuestion(3);
        $q4 = $this->makeQuestion(4);

        // 1. Gagal menyimpan paket dengan is_active=1 jika soal < 5
        $this->actingAs($admin)
            ->from(route('admin.tryouts.create'))
            ->post(route('admin.tryouts.store'), [
                'title' => 'Paket Kurang Soal',
                'duration_minutes' => 15,
                'is_active' => '1',
                'question_ids' => [$q1->id, $q2->id, $q3->id, $q4->id],
            ])
            ->assertRedirect(route('admin.tryouts.create'))
            ->assertSessionHasErrors('question_ids');

        $this->assertStringContainsString(
            'minimal 5 soal',
            (string) session('errors')?->first('question_ids')
        );

        // 2. Simpan sebagai nonaktif dulu (4 soal) -> berhasil
        $this->actingAs($admin)
            ->post(route('admin.tryouts.store'), [
                'title' => 'Paket Draf Empat Soal',
                'duration_minutes' => 15,
                'question_ids' => [$q1->id, $q2->id, $q3->id, $q4->id],
            ])
            ->assertRedirect();

        $tryout = Tryout::where('slug', 'paket-draf-empat-soal')->firstOrFail();
        $this->assertFalse($tryout->is_active);

        // 3. Tombol cepat "Jadikan aktif" ditolak karena baru 4 soal
        $this->actingAs($admin)
            ->post(route('admin.tryouts.activate', $tryout))
            ->assertRedirect(route('admin.tryouts.index'))
            ->assertSessionHas('error');

        $this->assertFalse($tryout->fresh()->is_active);
    }

    public function test_admin_can_create_tryout_from_scratch_order_questions_activate_and_see_in_active_list(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();

        $questions = collect(range(1, 5))->map(fn (int $i) => $this->makeQuestion($i));

        // Buat paket dari nol sebagai draf dengan urutan khusus (soal ke-5 ditaruh di posisi 1)
        $positions = [
            $questions[0]->id => 2,
            $questions[1]->id => 3,
            $questions[2]->id => 4,
            $questions[3]->id => 5,
            $questions[4]->id => 1,
        ];

        $this->actingAs($admin)
            ->post(route('admin.tryouts.store'), [
                'title' => 'Try Out Kinematika Pekan Ini',
                'description' => 'Latihan 5 soal kinematika pilihan.',
                'duration_minutes' => 25,
                'question_ids' => $questions->pluck('id')->all(),
                'positions' => $positions,
            ])
            ->assertRedirect();

        $tryout = Tryout::where('slug', 'try-out-kinematika-pekan-ini')->firstOrFail();
        $this->assertFalse($tryout->is_active);
        $this->assertSame(
            [$questions[4]->id, $questions[0]->id, $questions[1]->id, $questions[2]->id, $questions[3]->id],
            $tryout->questions()->pluck('questions.id')->all()
        );

        // Geser urutan soal menggunakan tombol naik
        $this->actingAs($admin)
            ->post(route('admin.tryouts.questions.up', [$tryout, $questions[0]]))
            ->assertRedirect();

        $this->assertSame(
            [$questions[0]->id, $questions[4]->id, $questions[1]->id, $questions[2]->id, $questions[3]->id],
            $tryout->questions()->pluck('questions.id')->all()
        );

        // Gunakan tombol cepat "Jadikan aktif"
        $this->actingAs($admin)
            ->post(route('admin.tryouts.activate', $tryout))
            ->assertRedirect(route('admin.tryouts.index'))
            ->assertSessionHas('status');

        $this->assertTrue($tryout->fresh()->is_active);

        // Muncul di halaman daftar try out aktif sisi user (/tryout)
        $this->actingAs($student)
            ->get('/tryout')
            ->assertOk()
            ->assertSee('Try Out Kinematika Pekan Ini')
            ->assertSee('5 soal')
            ->assertSee('25 menit');
    }
}
