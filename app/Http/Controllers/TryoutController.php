<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use Illuminate\View\View;

class TryoutController extends Controller
{
    public function index(): View
    {
        $tryouts = Tryout::query()
            ->available()
            ->with('lesson')
            ->withCount('questions')
            ->orderByDesc('id')
            ->get();

        return view('tryouts.index', [
            'tryouts' => $tryouts,
        ]);
    }
}
