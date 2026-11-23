<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Question;
use App\Services\LessonRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $lessonId = $request->query('lesson_id');
        $difficulty = $request->query('difficulty');

        $questions = Question::query()
            ->with('lesson')
            ->withCount('tryouts')
            ->withExists([
                'answers as used_in_finished_attempts' => fn ($q) => $q->whereHas(
                    'attempt',
                    fn ($a) => $a->whereNotNull('finished_at')
                ),
            ])
            ->when($lessonId === 'none', fn ($q) => $q->whereNull('lesson_id'))
            ->when(is_numeric($lessonId), fn ($q) => $q->where('lesson_id', (int) $lessonId))
            ->when(is_numeric($difficulty), fn ($q) => $q->where('difficulty', (int) $difficulty))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.questions.index', [
            'questions' => $questions,
            'lessons' => Lesson::orderBy('title')->get(),
            'selectedLessonId' => is_string($lessonId) ? $lessonId : null,
            'selectedDifficulty' => is_numeric($difficulty) ? (int) $difficulty : null,
        ]);
    }

    public function create(): View
    {
        return view('admin.questions.form', [
            'question' => new Question(['difficulty' => 1, 'correct_option' => 'a']),
            'lessons' => Lesson::orderBy('title')->get(),
            'isEdit' => false,
            'preview' => [
                'body_html' => '',
                'option_a_html' => '',
                'option_b_html' => '',
                'option_c_html' => '',
                'option_d_html' => '',
                'explanation_html' => '',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateQuestion($request);

        Question::create([
            'lesson_id' => ! empty($validated['lesson_id']) ? (int) $validated['lesson_id'] : null,
            'body' => trim($validated['body']),
            'option_a' => trim($validated['option_a']),
            'option_b' => trim($validated['option_b']),
            'option_c' => trim($validated['option_c']),
            'option_d' => trim($validated['option_d']),
            'correct_option' => strtolower($validated['correct_option']),
            'explanation' => trim($validated['explanation']),
            'difficulty' => (int) $validated['difficulty'],
        ]);

        return redirect()->route('admin.questions.index')->with('status', 'Soal ditambahkan.');
    }

    public function edit(Question $question, LessonRenderer $renderer): View
    {
        return view('admin.questions.form', [
            'question' => $question,
            'lessons' => Lesson::orderBy('title')->get(),
            'isEdit' => true,
            'preview' => [
                'body_html' => $renderer->render(old('body', $question->body)),
                'option_a_html' => $renderer->render(old('option_a', $question->option_a)),
                'option_b_html' => $renderer->render(old('option_b', $question->option_b)),
                'option_c_html' => $renderer->render(old('option_c', $question->option_c)),
                'option_d_html' => $renderer->render(old('option_d', $question->option_d)),
                'explanation_html' => $renderer->render(old('explanation', $question->explanation)),
            ],
        ]);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $validated = $this->validateQuestion($request);

        $question->update([
            'lesson_id' => ! empty($validated['lesson_id']) ? (int) $validated['lesson_id'] : null,
            'body' => trim($validated['body']),
            'option_a' => trim($validated['option_a']),
            'option_b' => trim($validated['option_b']),
            'option_c' => trim($validated['option_c']),
            'option_d' => trim($validated['option_d']),
            'correct_option' => strtolower($validated['correct_option']),
            'explanation' => trim($validated['explanation']),
            'difficulty' => (int) $validated['difficulty'],
        ]);

        return redirect()->route('admin.questions.index')->with('status', 'Perubahan soal disimpan.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        if ($question->hasCompletedAttempts()) {
            return redirect()
                ->route('admin.questions.index')
                ->with('error', 'Soal ini tidak bisa dihapus karena sudah dipakai pada percobaan try out yang selesai.');
        }

        $question->delete();

        return redirect()->route('admin.questions.index')->with('status', 'Soal dihapus.');
    }

    public function preview(Request $request, LessonRenderer $renderer): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string'],
            'option_a' => ['nullable', 'string'],
            'option_b' => ['nullable', 'string'],
            'option_c' => ['nullable', 'string'],
            'option_d' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
        ]);

        return response()->json([
            'body_html' => $renderer->render($validated['body'] ?? ''),
            'option_a_html' => $renderer->render($validated['option_a'] ?? ''),
            'option_b_html' => $renderer->render($validated['option_b'] ?? ''),
            'option_c_html' => $renderer->render($validated['option_c'] ?? ''),
            'option_d_html' => $renderer->render($validated['option_d'] ?? ''),
            'explanation_html' => $renderer->render($validated['explanation'] ?? ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'body' => ['required', 'string'],
            'option_a' => ['required', 'string', 'max:1000'],
            'option_b' => ['required', 'string', 'max:1000'],
            'option_c' => ['required', 'string', 'max:1000'],
            'option_d' => ['required', 'string', 'max:1000'],
            'correct_option' => ['required', 'string', Rule::in(['a', 'b', 'c', 'd', 'A', 'B', 'C', 'D'])],
            'explanation' => ['required', 'string'],
            'difficulty' => ['required', 'integer', 'min:1', 'max:3'],
        ], [
            'lesson_id.exists' => 'Materi yang dipilih tidak ditemukan.',
            'body.required' => 'Pertanyaan soal wajib diisi.',
            'option_a.required' => 'Pilihan A wajib diisi.',
            'option_b.required' => 'Pilihan B wajib diisi.',
            'option_c.required' => 'Pilihan C wajib diisi.',
            'option_d.required' => 'Pilihan D wajib diisi.',
            'correct_option.required' => 'Pilih jawaban benar (A, B, C, atau D).',
            'correct_option.in' => 'Jawaban benar harus salah satu dari A, B, C, atau D.',
            'explanation.required' => 'Pembahasan soal wajib diisi.',
            'difficulty.required' => 'Pilih tingkat kesulitan soal.',
            'difficulty.min' => 'Tingkat kesulitan minimal 1.',
            'difficulty.max' => 'Tingkat kesulitan maksimal 3.',
        ]);
    }
}
