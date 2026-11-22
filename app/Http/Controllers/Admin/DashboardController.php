<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\TryoutAttempt;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'userCount' => User::count(),
            'lessonCount' => Lesson::count(),
            'questionCount' => Question::count(),
            'attemptCount' => TryoutAttempt::whereNotNull('finished_at')->count(),
        ]);
    }
}
