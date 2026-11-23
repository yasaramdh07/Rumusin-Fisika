<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Reply;
use App\Models\Thread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class DiscussionController extends Controller
{
    private const MAX_POSTS_PER_MINUTE = 5;

    private const DECAY_SECONDS = 60;

    public function storeThread(Request $request, Lesson $lesson): RedirectResponse
    {
        if (! $lesson->is_published) {
            abort(404);
        }

        $this->checkRateLimit($request, 'thread_title');

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'title.required' => 'Judul pertanyaan wajib diisi.',
            'title.min' => 'Judul pertanyaan minimal 5 karakter.',
            'title.max' => 'Judul pertanyaan maksimal 120 karakter.',
            'body.required' => 'Isi diskusi wajib diisi.',
            'body.max' => 'Isi diskusi maksimal 2000 karakter.',
        ]);

        $this->hitRateLimit($request);

        $lesson->threads()->create([
            'user_id' => $request->user()->id,
            'title' => trim($validated['title']),
            'body' => trim($validated['body']),
        ]);

        return redirect()
            ->to(route('lessons.show', $lesson).'#diskusi')
            ->with('status', 'Diskusi berhasil dikirim.');
    }

    public function storeReply(Request $request, Thread $thread): RedirectResponse
    {
        $lesson = $thread->lesson()->firstOrFail();
        if (! $lesson->is_published) {
            abort(404);
        }

        $this->checkRateLimit($request, 'reply_body');

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ], [
            'body.required' => 'Isi balasan wajib diisi.',
            'body.max' => 'Isi balasan maksimal 1000 karakter.',
        ]);

        $this->hitRateLimit($request);

        $thread->replies()->create([
            'user_id' => $request->user()->id,
            'body' => trim($validated['body']),
        ]);

        return redirect()
            ->to(route('lessons.show', $lesson).'#thread-'.$thread->id)
            ->with('status', 'Balasan berhasil dikirim.');
    }

    public function destroyThread(Request $request, Thread $thread): RedirectResponse
    {
        if (! $thread->canBeDeletedBy($request->user())) {
            abort(403, 'Kamu tidak memiliki hak untuk menghapus diskusi ini.');
        }

        $lesson = $thread->lesson;
        $thread->delete();

        $target = $lesson ? route('lessons.show', $lesson).'#diskusi' : url()->previous();

        return redirect()
            ->to($target)
            ->with('status', 'Diskusi dihapus.');
    }

    public function destroyReply(Request $request, Reply $reply): RedirectResponse
    {
        if (! $reply->canBeDeletedBy($request->user())) {
            abort(403, 'Kamu tidak memiliki hak untuk menghapus balasan ini.');
        }

        $lesson = $reply->thread?->lesson;
        $reply->delete();

        $target = $lesson ? route('lessons.show', $lesson).'#diskusi' : url()->previous();

        return redirect()
            ->to($target)
            ->with('status', 'Balasan dihapus.');
    }

    private function throttleKey(Request $request): string
    {
        return 'forum-post:'.($request->user()?->id ?? $request->ip());
    }

    private function checkRateLimit(Request $request, string $errorField): void
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, self::MAX_POSTS_PER_MINUTE)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                $errorField => "Terlalu banyak kiriman diskusi dalam satu menit. Tunggu {$seconds} detik lalu coba lagi.",
            ]);
        }
    }

    private function hitRateLimit(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), self::DECAY_SECONDS);
    }
}
