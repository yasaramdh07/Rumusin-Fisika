<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $bestScore = $user->attempts()
            ->whereNotNull('finished_at')
            ->max('score');

        $featuredLesson = Lesson::published()
            ->with('category')
            ->orderBy('sort_order')
            ->first();

        $categories = Category::query()
            ->orderBy('sort_order')
            ->get();

        return view('home', [
            'user' => $user,
            'bestScore' => $bestScore,
            'featuredLesson' => $featuredLesson,
            'categories' => $categories,
        ]);
    }
}
