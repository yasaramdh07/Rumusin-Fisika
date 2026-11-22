<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonVideo;
use App\Services\YoutubeUrlParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LessonVideoController extends Controller
{
    public function store(Request $request, Lesson $lesson, YoutubeUrlParser $parser): RedirectResponse
    {
        [$validated, $youtubeId] = $this->validateVideo($request, $parser);

        $nextPosition = ((int) $lesson->videos()->max('position')) + 1;

        $lesson->videos()->create([
            'position' => $nextPosition,
            'title' => trim($validated['title']),
            'youtube_url' => trim($validated['youtube_url']),
            'youtube_id' => $youtubeId,
            'duration_seconds' => isset($validated['duration_minutes']) && $validated['duration_minutes'] !== null && $validated['duration_minutes'] !== ''
                ? ((int) $validated['duration_minutes']) * 60
                : null,
        ]);

        return redirect()
            ->to(route('admin.lessons.edit', $lesson).'#video-materi')
            ->with('status', 'Video tahapan ditambahkan.');
    }

    public function update(Request $request, Lesson $lesson, LessonVideo $video, YoutubeUrlParser $parser): RedirectResponse
    {
        $this->ensureBelongsToLesson($lesson, $video);

        [$validated, $youtubeId] = $this->validateVideo($request, $parser);

        $video->update([
            'title' => trim($validated['title']),
            'youtube_url' => trim($validated['youtube_url']),
            'youtube_id' => $youtubeId,
            'duration_seconds' => isset($validated['duration_minutes']) && $validated['duration_minutes'] !== null && $validated['duration_minutes'] !== ''
                ? ((int) $validated['duration_minutes']) * 60
                : null,
        ]);

        return redirect()
            ->to(route('admin.lessons.edit', $lesson).'#video-materi')
            ->with('status', 'Video tahapan diperbarui.');
    }

    public function destroy(Lesson $lesson, LessonVideo $video): RedirectResponse
    {
        $this->ensureBelongsToLesson($lesson, $video);

        DB::transaction(function () use ($lesson, $video): void {
            $video->delete();
            $this->resequence($lesson);
        });

        return redirect()
            ->to(route('admin.lessons.edit', $lesson).'#video-materi')
            ->with('status', 'Video tahapan dihapus.');
    }

    public function moveUp(Lesson $lesson, LessonVideo $video): RedirectResponse
    {
        $this->ensureBelongsToLesson($lesson, $video);

        DB::transaction(function () use ($lesson, $video): void {
            $this->resequence($lesson);
            $video->refresh();

            $previous = $lesson->videos()
                ->reorder()
                ->where('position', '<', $video->position)
                ->orderByDesc('position')
                ->first();

            if ($previous) {
                $currentPos = $video->position;
                $video->update(['position' => $previous->position]);
                $previous->update(['position' => $currentPos]);
            }
        });

        return redirect()
            ->to(route('admin.lessons.edit', $lesson).'#video-materi')
            ->with('status', 'Urutan video diperbarui.');
    }

    public function moveDown(Lesson $lesson, LessonVideo $video): RedirectResponse
    {
        $this->ensureBelongsToLesson($lesson, $video);

        DB::transaction(function () use ($lesson, $video): void {
            $this->resequence($lesson);
            $video->refresh();

            $next = $lesson->videos()
                ->reorder()
                ->where('position', '>', $video->position)
                ->orderBy('position')
                ->first();

            if ($next) {
                $currentPos = $video->position;
                $video->update(['position' => $next->position]);
                $next->update(['position' => $currentPos]);
            }
        });

        return redirect()
            ->to(route('admin.lessons.edit', $lesson).'#video-materi')
            ->with('status', 'Urutan video diperbarui.');
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function validateVideo(Request $request, YoutubeUrlParser $parser): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'youtube_url' => ['required', 'string', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
        ], [
            'title.required' => 'Judul tahapan video wajib diisi.',
            'title.max' => 'Judul tahapan video maksimal 180 karakter.',
            'youtube_url.required' => 'Link YouTube wajib diisi.',
            'youtube_url.max' => 'Link YouTube maksimal 500 karakter.',
            'duration_minutes.integer' => 'Durasi harus berupa angka menit bulat.',
            'duration_minutes.min' => 'Durasi minimal 1 menit.',
            'duration_minutes.max' => 'Durasi maksimal 600 menit.',
        ]);

        $youtubeId = $parser->parseId($validated['youtube_url']);

        if ($youtubeId === null) {
            throw ValidationException::withMessages([
                'youtube_url' => 'Link bukan dari YouTube yang valid. Tempel link youtube.com/watch?v=... atau youtu.be/...',
            ]);
        }

        return [$validated, $youtubeId];
    }

    private function ensureBelongsToLesson(Lesson $lesson, LessonVideo $video): void
    {
        if ($video->lesson_id !== $lesson->id) {
            abort(404);
        }
    }

    private function resequence(Lesson $lesson): void
    {
        $videos = $lesson->videos()->orderBy('position')->orderBy('id')->get();
        foreach ($videos as $index => $item) {
            $expected = $index + 1;
            if ($item->position !== $expected) {
                $item->update(['position' => $expected]);
            }
        }
    }
}
