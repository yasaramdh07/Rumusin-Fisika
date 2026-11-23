<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Reply;
use App\Models\Thread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function index(Request $request): View
    {
        $lessonId = $request->query('lesson_id');

        $threads = Thread::query()
            ->with(['user:id,name,username', 'lesson:id,title,slug', 'replies.user:id,name,username'])
            ->when(is_numeric($lessonId), fn ($q) => $q->where('lesson_id', (int) $lessonId))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.discussions.index', [
            'threads' => $threads,
            'lessons' => Lesson::orderBy('title')->get(),
            'selectedLessonId' => is_numeric($lessonId) ? (int) $lessonId : null,
        ]);
    }

    public function destroyThread(Thread $thread): RedirectResponse
    {
        $thread->delete();

        return redirect()->route('admin.discussions.index')->with('status', 'Thread diskusi dihapus.');
    }

    public function destroyReply(Reply $reply): RedirectResponse
    {
        $reply->delete();

        return redirect()->route('admin.discussions.index')->with('status', 'Balasan diskusi dihapus.');
    }
}
