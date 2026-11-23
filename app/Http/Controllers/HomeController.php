<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Tryout;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, LeaderboardService $leaderboard): View
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

        $latestActiveTryout = Tryout::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        $weeklyRank = null;
        if ($latestActiveTryout) {
            $board = $leaderboard->boardForUser($latestActiveTryout, $user, LeaderboardService::PERIOD_WEEKLY);
            $weeklyRank = $board['currentUserEntry']['rank'] ?? null;
        }

        return view('home', [
            'user' => $user,
            'bestScore' => $bestScore,
            'weeklyRank' => $weeklyRank,
            'latestActiveTryout' => $latestActiveTryout,
            'featuredLesson' => $featuredLesson,
            'categories' => $categories,
        ]);
    }
}
