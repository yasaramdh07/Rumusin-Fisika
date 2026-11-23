<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Lesson;
use App\Services\LessonRenderer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategorySlug = $request->query('kategori');

        $categories = Category::query()
            ->with(['lessons' => fn ($query) => $query->published()->withCount('videos')->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return view('lessons.index', [
            'categories' => $categories,
            'selectedCategorySlug' => is_string($selectedCategorySlug) ? $selectedCategorySlug : null,
        ]);
    }

    public function show(Lesson $lesson, LessonRenderer $renderer): View
    {
        if (! $lesson->is_published) {
            abort(404);
        }

        $lesson->load(['category', 'videos']);

        $threads = $lesson->threads()
            ->with(['user:id,name,username,role', 'replies.user:id,name,username,role'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->fragment('diskusi');

        return view('lessons.show', [
            'lesson' => $lesson,
            'bodyHtml' => $renderer->render($lesson->body),
            'formulasHtml' => $renderer->render($lesson->formulas),
            'threads' => $threads,
        ]);
    }
}
