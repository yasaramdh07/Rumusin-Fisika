<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\RukaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

class RukaController extends Controller
{
    private const MAX_REQUESTS_PER_MINUTE = 6;

    private const DECAY_SECONDS = 60;

    public function store(Request $request, Lesson $lesson, RukaService $service): JsonResponse
    {
        if (! $lesson->is_published) {
            abort(404);
        }

        $throttleKey = 'ruka:'.$request->user()->id;

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_REQUESTS_PER_MINUTE)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => "Kamu bertanya terlalu cepat. Tunggu {$seconds} detik lalu coba lagi.",
            ], 429);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:800'],
        ], [
            'message.required' => 'Tulis pertanyaanmu terlebih dulu.',
            'message.max' => 'Pertanyaan maksimal 800 karakter.',
        ]);

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

        try {
            $result = $service->ask($request->user(), $lesson, $validated['message']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first('message'),
                'errors' => $e->errors(),
                'limit_reached' => true,
                'remaining_today' => 0,
            ], 422);
        } catch (Throwable) {
            return response()->json([
                'message' => RukaService::ERROR_FALLBACK_MESSAGE,
            ], 503);
        }

        return response()->json($result);
    }

    public function destroy(Request $request, Lesson $lesson, RukaService $service): JsonResponse|RedirectResponse
    {
        if (! $lesson->is_published) {
            abort(404);
        }

        $service->clearConversation($request->user(), $lesson);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'Percakapan dengan Ruka dihapus.',
                'remaining_today' => $service->remainingToday($request->user()),
                'daily_limit' => $service->dailyLimit(),
            ]);
        }

        return redirect()
            ->to(route('lessons.show', $lesson))
            ->with('status', 'Percakapan dengan Ruka dihapus.');
    }
}
