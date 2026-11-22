<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Lesson;
use App\Services\LessonRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->query('category_id');

        $lessons = Lesson::query()
            ->with('category')
            ->withCount('videos')
            ->when(is_numeric($categoryId), fn ($q) => $q->where('category_id', (int) $categoryId))
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.lessons.index', [
            'lessons' => $lessons,
            'categories' => Category::orderBy('sort_order')->get(),
            'selectedCategoryId' => is_numeric($categoryId) ? (int) $categoryId : null,
        ]);
    }

    public function create(): View
    {
        $nextOrder = ((int) Lesson::max('sort_order')) + 1;

        return view('admin.lessons.form', [
            'lesson' => new Lesson(['sort_order' => $nextOrder, 'is_published' => false]),
            'categories' => Category::orderBy('sort_order')->get(),
            'videos' => collect(),
            'editingVideo' => null,
            'isEdit' => false,
            'previewBodyHtml' => '',
            'previewFormulasHtml' => '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateLesson($request);

        $lesson = Lesson::create([
            'category_id' => (int) $validated['category_id'],
            'title' => trim($validated['title']),
            'slug' => $this->uniqueSlug($validated['title']),
            'summary' => trim($validated['summary']),
            'body' => $validated['body'],
            'formulas' => isset($validated['formulas']) && trim((string) $validated['formulas']) !== ''
                ? $validated['formulas']
                : null,
            'is_published' => $request->boolean('is_published'),
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return redirect()
            ->route('admin.lessons.edit', $lesson)
            ->with('status', $lesson->is_published ? 'Materi diterbitkan.' : 'Materi disimpan sebagai draf.');
    }

    public function edit(Request $request, Lesson $lesson, LessonRenderer $renderer): View
    {
        $lesson->load('videos');

        $editingVideoId = $request->query('edit_video');
        $editingVideo = is_numeric($editingVideoId)
            ? $lesson->videos->firstWhere('id', (int) $editingVideoId)
            : null;

        return view('admin.lessons.form', [
            'lesson' => $lesson,
            'categories' => Category::orderBy('sort_order')->get(),
            'videos' => $lesson->videos,
            'editingVideo' => $editingVideo,
            'isEdit' => true,
            'previewBodyHtml' => $renderer->render(old('body', $lesson->body)),
            'previewFormulasHtml' => $renderer->render(old('formulas', $lesson->formulas)),
        ]);
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $validated = $this->validateLesson($request);

        $title = trim($validated['title']);
        $slug = $lesson->title === $title
            ? $lesson->slug
            : $this->uniqueSlug($title, $lesson->id);

        $lesson->update([
            'category_id' => (int) $validated['category_id'],
            'title' => $title,
            'slug' => $slug,
            'summary' => trim($validated['summary']),
            'body' => $validated['body'],
            'formulas' => isset($validated['formulas']) && trim((string) $validated['formulas']) !== ''
                ? $validated['formulas']
                : null,
            'is_published' => $request->boolean('is_published'),
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return redirect()
            ->route('admin.lessons.edit', $lesson)
            ->with('status', $lesson->is_published ? 'Materi diterbitkan.' : 'Perubahan materi disimpan.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return redirect()->route('admin.lessons.index')->with('status', 'Materi dihapus.');
    }

    public function preview(Request $request, LessonRenderer $renderer): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['nullable', 'string'],
            'formulas' => ['nullable', 'string'],
        ]);

        return response()->json([
            'body_html' => $renderer->render($validated['body'] ?? ''),
            'formulas_html' => $renderer->render($validated['formulas'] ?? ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLesson(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'formulas' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_published' => ['nullable', 'boolean'],
        ], [
            'category_id.required' => 'Pilih kategori materi.',
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'title.required' => 'Judul materi wajib diisi.',
            'title.max' => 'Judul materi maksimal 180 karakter.',
            'summary.required' => 'Ringkasan materi wajib diisi.',
            'summary.max' => 'Ringkasan materi maksimal 500 karakter.',
            'body.required' => 'Isi materi wajib diisi.',
            'sort_order.required' => 'Urutan materi wajib diisi.',
            'sort_order.integer' => 'Urutan materi harus berupa angka bulat.',
            'sort_order.min' => 'Urutan materi minimal 0.',
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'materi';
        }

        $slug = $base;
        $suffix = 2;

        while (
            Lesson::query()
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
