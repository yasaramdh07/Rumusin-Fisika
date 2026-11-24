<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->withCount([
                'attempts as finished_attempts_count' => fn ($q) => $q->whereNotNull('finished_at'),
                'threads',
                'replies',
            ])
            ->withMax([
                'attempts as best_score' => fn ($q) => $q->whereNotNull('finished_at'),
            ], 'score')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->paginate(15);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }
}
