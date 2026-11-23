<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Tryout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TryoutController extends Controller
{
    public function index(): View
    {
        $tryouts = Tryout::query()
            ->with('lesson')
            ->withCount(['questions', 'attempts'])
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.tryouts.index', [
            'tryouts' => $tryouts,
            'minActiveQuestions' => Tryout::MIN_ACTIVE_QUESTIONS,
        ]);
    }

    public function create(): View
    {
        return view('admin.tryouts.form', [
            'tryout' => new Tryout(['duration_minutes' => 20, 'is_active' => false]),
            'lessons' => Lesson::orderBy('title')->get(),
            'allQuestions' => Question::with('lesson')->orderBy('lesson_id')->orderBy('difficulty')->orderBy('id')->get(),
            'selectedPositions' => [],
            'isEdit' => false,
            'minActiveQuestions' => Tryout::MIN_ACTIVE_QUESTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$validated, $syncMap] = $this->validateTryout($request);

        $tryout = DB::transaction(function () use ($validated, $syncMap, $request): Tryout {
            $tryout = Tryout::create([
                'lesson_id' => ! empty($validated['lesson_id']) ? (int) $validated['lesson_id'] : null,
                'title' => trim($validated['title']),
                'slug' => $this->uniqueSlug($validated['title']),
                'description' => isset($validated['description']) && trim((string) $validated['description']) !== ''
                    ? trim((string) $validated['description'])
                    : null,
                'duration_minutes' => (int) $validated['duration_minutes'],
                'is_active' => $request->boolean('is_active'),
                'starts_at' => ! empty($validated['starts_at']) ? $validated['starts_at'] : null,
                'ends_at' => ! empty($validated['ends_at']) ? $validated['ends_at'] : null,
            ]);

            if ($syncMap !== []) {
                $tryout->questions()->sync($syncMap);
            }

            return $tryout;
        });

        return redirect()
            ->route('admin.tryouts.edit', $tryout)
            ->with('status', $tryout->is_active ? 'Paket try out disimpan dan diaktifkan.' : 'Paket try out disimpan.');
    }

    public function edit(Tryout $tryout): View
    {
        $tryout->load('questions');

        $selectedPositions = [];
        foreach ($tryout->questions as $question) {
            $selectedPositions[$question->id] = (int) $question->pivot->position;
        }

        return view('admin.tryouts.form', [
            'tryout' => $tryout,
            'lessons' => Lesson::orderBy('title')->get(),
            'allQuestions' => Question::with('lesson')->orderBy('lesson_id')->orderBy('difficulty')->orderBy('id')->get(),
            'selectedPositions' => $selectedPositions,
            'isEdit' => true,
            'minActiveQuestions' => Tryout::MIN_ACTIVE_QUESTIONS,
        ]);
    }

    public function update(Request $request, Tryout $tryout): RedirectResponse
    {
        [$validated, $syncMap] = $this->validateTryout($request);

        $title = trim($validated['title']);
        $slug = $tryout->title === $title
            ? $tryout->slug
            : $this->uniqueSlug($title, $tryout->id);

        DB::transaction(function () use ($tryout, $validated, $syncMap, $title, $slug, $request): void {
            $tryout->update([
                'lesson_id' => ! empty($validated['lesson_id']) ? (int) $validated['lesson_id'] : null,
                'title' => $title,
                'slug' => $slug,
                'description' => isset($validated['description']) && trim((string) $validated['description']) !== ''
                    ? trim((string) $validated['description'])
                    : null,
                'duration_minutes' => (int) $validated['duration_minutes'],
                'is_active' => $request->boolean('is_active'),
                'starts_at' => ! empty($validated['starts_at']) ? $validated['starts_at'] : null,
                'ends_at' => ! empty($validated['ends_at']) ? $validated['ends_at'] : null,
            ]);

            $tryout->questions()->sync($syncMap);
        });

        return redirect()
            ->route('admin.tryouts.edit', $tryout)
            ->with('status', 'Perubahan paket try out disimpan.');
    }

    public function destroy(Tryout $tryout): RedirectResponse
    {
        $tryout->delete();

        return redirect()->route('admin.tryouts.index')->with('status', 'Paket try out dihapus.');
    }

    public function activate(Tryout $tryout): RedirectResponse
    {
        $questionCount = $tryout->questions()->count();

        if ($questionCount < Tryout::MIN_ACTIVE_QUESTIONS) {
            return redirect()
                ->route('admin.tryouts.index')
                ->with(
                    'error',
                    'Paket try out harus memiliki minimal '.Tryout::MIN_ACTIVE_QUESTIONS." soal sebelum diaktifkan (saat ini {$questionCount} soal)."
                );
        }

        $tryout->update(['is_active' => true]);

        return redirect()
            ->route('admin.tryouts.index')
            ->with('status', "Paket '{$tryout->title}' dijadikan aktif.");
    }

    public function moveQuestionUp(Tryout $tryout, Question $question): RedirectResponse
    {
        DB::transaction(function () use ($tryout, $question): void {
            $this->resequenceQuestions($tryout);

            $items = $tryout->questions()->get()->values();
            $idx = $items->search(fn (Question $q) => $q->id === $question->id);

            if ($idx !== false && $idx > 0) {
                $current = $items[$idx];
                $prev = $items[$idx - 1];

                $tryout->questions()->updateExistingPivot($current->id, ['position' => $idx]);
                $tryout->questions()->updateExistingPivot($prev->id, ['position' => $idx + 1]);
            }
        });

        return redirect()
            ->to(route('admin.tryouts.edit', $tryout).'#urutan-soal')
            ->with('status', 'Urutan soal diperbarui.');
    }

    public function moveQuestionDown(Tryout $tryout, Question $question): RedirectResponse
    {
        DB::transaction(function () use ($tryout, $question): void {
            $this->resequenceQuestions($tryout);

            $items = $tryout->questions()->get()->values();
            $idx = $items->search(fn (Question $q) => $q->id === $question->id);

            if ($idx !== false && $idx < $items->count() - 1) {
                $current = $items[$idx];
                $next = $items[$idx + 1];

                $tryout->questions()->updateExistingPivot($current->id, ['position' => $idx + 2]);
                $tryout->questions()->updateExistingPivot($next->id, ['position' => $idx + 1]);
            }
        });

        return redirect()
            ->to(route('admin.tryouts.edit', $tryout).'#urutan-soal')
            ->with('status', 'Urutan soal diperbarui.');
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<int, array{position: int}>}
     */
    private function validateTryout(Request $request): array
    {
        $validated = $request->validate([
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'question_ids' => ['nullable', 'array'],
            'question_ids.*' => ['integer', 'distinct', 'exists:questions,id'],
            'positions' => ['nullable', 'array'],
        ], [
            'lesson_id.exists' => 'Materi yang dipilih tidak ditemukan.',
            'title.required' => 'Judul paket try out wajib diisi.',
            'title.max' => 'Judul paket try out maksimal 180 karakter.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'duration_minutes.required' => 'Durasi try out wajib diisi.',
            'duration_minutes.integer' => 'Durasi harus berupa angka menit bulat.',
            'duration_minutes.min' => 'Durasi minimal 1 menit.',
            'duration_minutes.max' => 'Durasi maksimal 300 menit.',
            'starts_at.date' => 'Jadwal mulai tidak valid.',
            'ends_at.date' => 'Jadwal berakhir tidak valid.',
            'ends_at.after_or_equal' => 'Jadwal berakhir harus setelah atau sama dengan jadwal mulai.',
            'question_ids.*.exists' => 'Salah satu soal yang dipilih tidak ditemukan.',
        ]);

        $questionIds = array_values(array_unique(array_map('intval', $validated['question_ids'] ?? [])));

        if ($request->boolean('is_active') && count($questionIds) < Tryout::MIN_ACTIVE_QUESTIONS) {
            throw ValidationException::withMessages([
                'question_ids' => 'Paket try out yang diaktifkan wajib memiliki minimal '.Tryout::MIN_ACTIVE_QUESTIONS.' soal.',
            ]);
        }

        $rawPositions = is_array($validated['positions'] ?? null) ? $validated['positions'] : [];

        // Urutkan soal terpilih berdasarkan nilai posisi yang diisi admin (lalu urutan centang).
        $ordered = [];
        foreach ($questionIds as $idx => $qid) {
            $pos = isset($rawPositions[$qid]) && is_numeric($rawPositions[$qid])
                ? max(1, (int) $rawPositions[$qid])
                : ($idx + 1) * 10;
            $ordered[] = ['id' => $qid, 'pos' => $pos, 'idx' => $idx];
        }

        usort($ordered, fn (array $a, array $b) => ($a['pos'] <=> $b['pos']) ?: ($a['idx'] <=> $b['idx']));

        $syncMap = [];
        foreach ($ordered as $i => $item) {
            $syncMap[$item['id']] = ['position' => $i + 1];
        }

        return [$validated, $syncMap];
    }

    private function resequenceQuestions(Tryout $tryout): void
    {
        $questions = $tryout->questions()->get()->values();
        foreach ($questions as $index => $item) {
            $expected = $index + 1;
            if ((int) $item->pivot->position !== $expected) {
                $tryout->questions()->updateExistingPivot($item->id, ['position' => $expected]);
            }
        }
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'tryout';
        }

        $slug = $base;
        $suffix = 2;

        while (
            Tryout::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
