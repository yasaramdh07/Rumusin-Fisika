<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(Request $request, LeaderboardService $service): View
    {
        $period = $service->normalizePeriod($request->query('periode'));
        $tryoutSlug = $request->query('tryout');

        // Daftar try out: paket aktif terbaru lebih dulu, diikuti paket lain yang sudah punya percobaan.
        $tryouts = Tryout::query()
            ->where(function ($q): void {
                $q->where('is_active', true)
                    ->orWhereHas('attempts', fn ($a) => $a->whereNotNull('finished_at'));
            })
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        $selectedTryout = null;
        if (is_string($tryoutSlug) && $tryoutSlug !== '') {
            $selectedTryout = $tryouts->firstWhere('slug', $tryoutSlug);
        }

        // Default: try out aktif terbaru (atau try out pertama jika belum ada yang aktif).
        $selectedTryout ??= $tryouts->firstWhere('is_active', true) ?? $tryouts->first();

        $board = $selectedTryout
            ? $service->boardForUser($selectedTryout, $request->user(), $period)
            : [
                'topTen' => collect(),
                'currentUserEntry' => null,
                'isOutsideTopTen' => false,
                'totalParticipants' => 0,
            ];

        [$periodStart, $periodEnd] = $service->periodBounds($period);

        return view('leaderboard.index', [
            'tryouts' => $tryouts,
            'selectedTryout' => $selectedTryout,
            'period' => $period,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'topTen' => $board['topTen'],
            'currentUserEntry' => $board['currentUserEntry'],
            'isOutsideTopTen' => $board['isOutsideTopTen'],
            'totalParticipants' => $board['totalParticipants'],
        ]);
    }
}
