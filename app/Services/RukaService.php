<?php

namespace App\Services;

use App\Contracts\RukaProvider;
use App\Models\Lesson;
use App\Models\RukaMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class RukaService
{
    public const MAX_CONTEXT_BODY_CHARS = 6000;

    public const MAX_HISTORY_MESSAGES = 8;

    public const ERROR_FALLBACK_MESSAGE = 'Ruka lagi nggak bisa jawab. Coba lagi sebentar.';

    public function __construct(
        private readonly RukaProvider $provider,
        private readonly LessonRenderer $renderer,
    ) {}

    public function dailyLimit(): int
    {
        return max(1, (int) config('rumusin.ruka_daily_limit', 30));
    }

    public function usedToday(User $user): int
    {
        return RukaMessage::query()
            ->where('user_id', $user->id)
            ->where('role', RukaMessage::ROLE_USER)
            ->where('created_at', '>=', now()->startOfDay())
            ->where('created_at', '<=', now()->endOfDay())
            ->count();
    }

    public function remainingToday(User $user): int
    {
        return max(0, $this->dailyLimit() - $this->usedToday($user));
    }

    /**
     * Ambil riwayat pesan user pada materi tertentu beserta HTML hasil render Markdown + KaTeX.
     *
     * @return Collection<int, array{id: int, role: string, content: string, html: string, created_at: string}>
     */
    public function historyForLesson(User $user, Lesson $lesson): Collection
    {
        return RukaMessage::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(fn (RukaMessage $msg): array => [
                'id' => (int) $msg->id,
                'role' => (string) $msg->role,
                'content' => (string) $msg->content,
                'html' => $this->renderer->render($msg->content),
                'created_at' => (string) $msg->created_at?->format('H:i'),
            ]);
    }

    /**
     * Susun system prompt sesuai persona PRD F8 + konteks materi yang sedang dibuka.
     */
    public function buildSystemPrompt(Lesson $lesson): string
    {
        $lesson->loadMissing('category');

        $categoryName = $lesson->category?->name ?? 'Fisika SMA';
        $truncatedBody = Str::limit(trim((string) $lesson->body), self::MAX_CONTEXT_BODY_CHARS, '...');
        $formulas = trim((string) ($lesson->formulas ?? 'Tidak ada lembar rumus khusus.'));

        return <<<PROMPT
Kamu adalah Ruka, asisten belajar fisika SMA di platform Rumusin!

Aturan persona dan gaya menjawab:
- Gunakan bahasa Indonesia yang ramah, santai, tetapi tetap sopan. Sapa pelajar dengan "kamu".
- Jelaskan konsep atau penyelesaian langkah demi langkah dengan contoh angka yang mudah diikuti.
- Jangan langsung memberi jawaban akhir soal tanpa menjabarkan langkah berpikir dan rumusnya terlebih dulu.
- Sesekali ajukan pertanyaan balik singkat di akhir untuk mengecek apakah pelajar sudah paham.
- Tulis rumus matematika menggunakan notasi LaTeX/KaTeX (\$...\$ untuk rumus inline dan \$\$...\$\$ untuk rumus blok).
- Tolak dengan sopan jika pelajar bertanya topik di luar belajar fisika.
- Jujur katakan jika kamu tidak yakin; jangan mengarang rumus atau konstanta.
- Jangan pernah mengaku sebagai manusia.

Konteks materi yang sedang dibuka pelajar:
- Judul materi: {$lesson->title}
- Kategori: {$categoryName}
- Ringkasan: {$lesson->summary}

Lembar Rumus materi ini:
{$formulas}

Isi materi (dipotong maks. ±6000 karakter):
{$truncatedBody}
PROMPT;
    }

    /**
     * Proses pesan baru dari user, panggil RukaProvider dengan 8 pesan terakhir, dan simpan keduanya.
     *
     * @return array{
     *     user_message: array{id: int, role: string, content: string, html: string, created_at: string},
     *     assistant_message: array{id: int, role: string, content: string, html: string, created_at: string},
     *     remaining_today: int,
     *     daily_limit: int
     * }
     */
    public function ask(User $user, Lesson $lesson, string $prompt): array
    {
        if ($this->remainingToday($user) <= 0) {
            $limit = $this->dailyLimit();

            throw ValidationException::withMessages([
                'message' => "Batas pertanyaan ke Ruka hari ini sudah tercapai ({$limit} pesan per hari). Lanjutkan besok ya.",
            ]);
        }

        $cleanPrompt = trim($prompt);

        // Ambil maks. 7 pesan terakhir dari database, ditambah 1 pesan baru = 8 pesan terakhir ke model (PRD F8).
        $previousMessages = RukaMessage::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::MAX_HISTORY_MESSAGES - 1)
            ->get()
            ->reverse()
            ->values();

        $contextMessages = $previousMessages
            ->map(fn (RukaMessage $m): array => [
                'role' => (string) $m->role,
                'content' => (string) $m->content,
            ])
            ->push([
                'role' => RukaMessage::ROLE_USER,
                'content' => $cleanPrompt,
            ])
            ->values()
            ->all();

        $systemPrompt = $this->buildSystemPrompt($lesson);

        try {
            $replyText = $this->provider->reply($contextMessages, $systemPrompt);
        } catch (Throwable) {
            throw new \RuntimeException(self::ERROR_FALLBACK_MESSAGE);
        }

        $userMsg = RukaMessage::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'role' => RukaMessage::ROLE_USER,
            'content' => $cleanPrompt,
        ]);

        $assistantMsg = RukaMessage::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'role' => RukaMessage::ROLE_ASSISTANT,
            'content' => $replyText,
        ]);

        return [
            'user_message' => [
                'id' => (int) $userMsg->id,
                'role' => $userMsg->role,
                'content' => $userMsg->content,
                'html' => $this->renderer->render($userMsg->content),
                'created_at' => (string) $userMsg->created_at?->format('H:i'),
            ],
            'assistant_message' => [
                'id' => (int) $assistantMsg->id,
                'role' => $assistantMsg->role,
                'content' => $assistantMsg->content,
                'html' => $this->renderer->render($assistantMsg->content),
                'created_at' => (string) $assistantMsg->created_at?->format('H:i'),
            ],
            'remaining_today' => $this->remainingToday($user),
            'daily_limit' => $this->dailyLimit(),
        ];
    }

    public function clearConversation(User $user, Lesson $lesson): void
    {
        RukaMessage::query()
            ->where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->delete();
    }
}
