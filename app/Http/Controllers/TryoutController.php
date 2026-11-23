<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Services\LessonRenderer;
use App\Services\TryoutAttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function show(Request $request, Tryout $tryout, TryoutAttemptService $service): View
    {
        if (! $tryout->isAvailable()) {
            abort(404);
        }

        $user = $request->user();
        $tryout->load('lesson')->loadCount('questions');

        $attempts = $user->attempts()
            ->where('tryout_id', $tryout->id)
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        $unfinishedAttempt = $attempts->firstWhere('finished_at', null);
        $bestScore = $attempts->whereNotNull('finished_at')->max('score');

        return view('tryouts.show', [
            'tryout' => $tryout,
            'attempts' => $attempts,
            'unfinishedAttempt' => $unfinishedAttempt,
            'bestScore' => $bestScore,
            'remainingToday' => $service->remainingDailyAttempts($user, $tryout),
            'dailyLimit' => max(1, (int) config('rumusin.tryout_daily_attempts', 3)),
        ]);
    }

    public function start(Request $request, Tryout $tryout, TryoutAttemptService $service): RedirectResponse
    {
        if (! $tryout->isAvailable()) {
            abort(404);
        }

        $attempt = $service->startOrResume($request->user(), $tryout);

        return redirect()->route('tryouts.attempt', $attempt);
    }

    public function attempt(
        Request $request,
        TryoutAttempt $attempt,
        TryoutAttemptService $service,
        LessonRenderer $renderer
    ): View|RedirectResponse {
        $this->ensureOwner($request, $attempt);

        if ($attempt->finished_at !== null) {
            return redirect()->route('tryouts.result', $attempt);
        }

        $attempt->load(['tryout.lesson', 'answers']);
        $questions = $service->orderedQuestionsForAttempt($attempt);

        $savedAnswers = $attempt->answers
            ->pluck('selected_option', 'question_id')
            ->all();

        $renderedQuestions = $questions->map(function ($question, int $idx) use ($renderer): array {
            return [
                'id' => $question->id,
                'number' => $idx + 1,
                'difficulty' => $question->difficulty,
                'body_html' => $renderer->render($question->body),
                'options' => [
                    'a' => $renderer->render($question->option_a),
                    'b' => $renderer->render($question->option_b),
                    'c' => $renderer->render($question->option_c),
                    'd' => $renderer->render($question->option_d),
                ],
            ];
        });

        $deadlineTimestamp = $attempt->started_at
            ->copy()
            ->addMinutes((int) $attempt->tryout->duration_minutes)
            ->getTimestamp();

        $remainingSeconds = max(0, $deadlineTimestamp - now()->getTimestamp());

        return view('tryouts.attempt', [
            'attempt' => $attempt,
            'tryout' => $attempt->tryout,
            'questions' => $renderedQuestions,
            'savedAnswers' => $savedAnswers,
            'remainingSeconds' => $remainingSeconds,
        ]);
    }

    public function submit(
        Request $request,
        TryoutAttempt $attempt,
        TryoutAttemptService $service
    ): RedirectResponse {
        $this->ensureOwner($request, $attempt);

        if ($attempt->finished_at !== null) {
            return redirect()->route('tryouts.result', $attempt);
        }

        $answersInput = $request->input('answers', []);
        if (! is_array($answersInput)) {
            $answersInput = [];
        }

        $service->submit($attempt, $answersInput);

        return redirect()
            ->route('tryouts.result', $attempt)
            ->with('status', 'Jawaban berhasil dikirim dan dinilai.');
    }

    public function result(
        Request $request,
        TryoutAttempt $attempt,
        TryoutAttemptService $service,
        LessonRenderer $renderer
    ): View|RedirectResponse {
        $this->ensureOwner($request, $attempt);

        if ($attempt->finished_at === null) {
            return redirect()->route('tryouts.attempt', $attempt);
        }

        $attempt->load(['tryout.lesson', 'answers']);
        $answersByQuestion = $attempt->answers->keyBy('question_id');
        $questions = $service->orderedQuestionsForReview($attempt);

        $reviewItems = $questions->map(function ($question, int $idx) use ($answersByQuestion, $renderer): array {
            $answer = $answersByQuestion->get($question->id);
            $selected = $answer?->selected_option ? strtolower($answer->selected_option) : null;
            $correct = strtolower((string) $question->correct_option);
            $isCorrect = (bool) ($answer?->is_correct ?? false);

            return [
                'id' => $question->id,
                'number' => $idx + 1,
                'difficulty' => $question->difficulty,
                'body_html' => $renderer->render($question->body),
                'options' => [
                    'a' => $renderer->render($question->option_a),
                    'b' => $renderer->render($question->option_b),
                    'c' => $renderer->render($question->option_c),
                    'd' => $renderer->render($question->option_d),
                ],
                'selected_option' => $selected,
                'correct_option' => $correct,
                'is_correct' => $isCorrect,
                'explanation_html' => $renderer->render($question->explanation),
            ];
        });

        return view('tryouts.result', [
            'attempt' => $attempt,
            'tryout' => $attempt->tryout,
            'reviewItems' => $reviewItems,
        ]);
    }

    private function ensureOwner(Request $request, TryoutAttempt $attempt): void
    {
        if ((int) $attempt->user_id !== (int) $request->user()?->id) {
            abort(403, 'Percobaan try out ini bukan milikmu.');
        }
    }
}
