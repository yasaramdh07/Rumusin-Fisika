<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Lesson;
use App\Services\LessonRenderer;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(LessonRenderer $renderer): View
    {
        $categories = Category::query()
            ->withCount(['lessons as published_lessons_count' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->get();

        $featuredLesson = Lesson::published()
            ->with('category')
            ->orderBy('sort_order')
            ->first();

        $heroFormulas = $featuredLesson?->formulas ?? <<<'MD'
**GLB** (kecepatan konstan)

$$s = v\,t$$

**GLBB** (percepatan konstan)

$$v_t = v_0 + a\,t$$

$$s = v_0\,t + \tfrac{1}{2}\,a\,t^2$$

$$v_t^2 = v_0^2 + 2\,a\,s$$
MD;

        return view('welcome', [
            'categories' => $categories,
            'featuredLesson' => $featuredLesson,
            'heroFormulasHtml' => $renderer->render($heroFormulas),
        ]);
    }
}
