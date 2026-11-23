<?php

namespace Tests\Feature;

use App\Contracts\RukaProvider;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\RukaMessage;
use App\Models\User;
use App\Services\Ruka\FakeRukaProvider;
use App\Services\Ruka\GoogleAiStudioRukaProvider;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LessonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RukaTest extends TestCase
{
    use RefreshDatabase;

    private function seedGerakLurusLesson(): Lesson
    {
        $this->seed([CategorySeeder::class, LessonSeeder::class]);

        return Lesson::where('slug', 'gerak-lurus-glb-glbb')->firstOrFail();
    }

    public function test_guest_sees_login_prompt_and_cannot_call_ruka_endpoints(): void
    {
        $lesson = $this->seedGerakLurusLesson();

        $this->get(route('lessons.show', $lesson))
            ->assertOk()
            ->assertSee('Masuk untuk bertanya');

        $this->postJson(route('ruka.store', $lesson), [
            'message' => 'Apa beda GLB dan GLBB?',
        ])->assertUnauthorized();

        $this->deleteJson(route('ruka.destroy', $lesson))
            ->assertUnauthorized();
    }

    public function test_asking_ruka_sends_lesson_context_and_last_eight_messages_and_never_leaks_api_key(): void
    {
        $secretKey = 'AIzaSyDUMMY_SECRET_GOOGLE_AI_STUDIO_KEY_998877';
        config([
            'rumusin.ai.provider' => 'fake',
            'rumusin.ai.api_key' => $secretKey,
            'rumusin.ai.model' => 'gemini-2.5-flash',
        ]);

        $fake = new FakeRukaProvider();
        $this->app->instance(RukaProvider::class, $fake);

        $lesson = $this->seedGerakLurusLesson();
        $user = User::factory()->create();

        // Buat 10 pesan riwayat lama di materi ini
        for ($i = 1; $i <= 5; $i++) {
            RukaMessage::create([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'role' => RukaMessage::ROLE_USER,
                'content' => "Pertanyaan lama ke-{$i}",
            ]);
            RukaMessage::create([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'role' => RukaMessage::ROLE_ASSISTANT,
                'content' => "Jawaban lama ke-{$i}",
            ]);
        }

        $response = $this->actingAs($user)
            ->postJson(route('ruka.store', $lesson), [
                'message' => 'Kapan kita memakai rumus $v_t^2 = v_0^2 + 2as$?',
            ])
            ->assertOk();

        // Konteks materi (judul, kategori, ringkasan, lembar rumus) terkirim di system prompt
        $this->assertCount(1, $fake->recordedCalls);
        $call = $fake->recordedCalls[0];

        $this->assertStringContainsString('Gerak Lurus (GLB dan GLBB)', $call['systemPrompt']);
        $this->assertStringContainsString('Kategori: Gerak', $call['systemPrompt']);
        $this->assertStringContainsString('v_t = v_0 + a\,t', $call['systemPrompt']);

        // Maksimal 8 pesan terakhir yang dikirim ke provider (7 riwayat terakhir + 1 pesan baru)
        $this->assertCount(8, $call['messages']);
        $this->assertSame('Kapan kita memakai rumus $v_t^2 = v_0^2 + 2as$?', end($call['messages'])['content']);

        // Respons JSON memuat jawaban dalam konteks materi Gerak Lurus dan TIDAK memuat API key
        $jsonText = $response->getContent();
        $this->assertStringContainsString('Gerak Lurus (GLB dan GLBB)', $jsonText);
        $this->assertStringNotContainsString($secretKey, $jsonText);

        // View-source halaman materi juga tidak menampilkan API key
        $pageHtml = $this->actingAs($user)
            ->get(route('lessons.show', $lesson))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString($secretKey, $pageHtml);

        // Hapus percakapan membersihkan riwayat user pada materi tersebut
        $this->actingAs($user)
            ->deleteJson(route('ruka.destroy', $lesson))
            ->assertOk();

        $this->assertSame(
            0,
            RukaMessage::where('user_id', $user->id)->where('lesson_id', $lesson->id)->count()
        );
    }

    public function test_daily_limit_is_enforced_per_user_per_day(): void
    {
        config(['rumusin.ruka_daily_limit' => 2]);

        $fake = new FakeRukaProvider();
        $this->app->instance(RukaProvider::class, $fake);

        $lesson = $this->seedGerakLurusLesson();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('ruka.store', $lesson), ['message' => 'Pertanyaan pertama'])
            ->assertOk()
            ->assertJsonPath('remaining_today', 1);

        $this->actingAs($user)
            ->postJson(route('ruka.store', $lesson), ['message' => 'Pertanyaan kedua'])
            ->assertOk()
            ->assertJsonPath('remaining_today', 0);

        // Pertanyaan ke-3 di hari yang sama ditolak karena melewati RUKA_DAILY_LIMIT
        $this->actingAs($user)
            ->postJson(route('ruka.store', $lesson), ['message' => 'Pertanyaan ketiga melewati batas'])
            ->assertStatus(422)
            ->assertJsonPath('limit_reached', true)
            ->assertJsonPath('remaining_today', 0);

        $this->assertCount(2, $fake->recordedCalls);
    }

    public function test_ruka_is_throttled_to_six_messages_per_minute(): void
    {
        config(['rumusin.ruka_daily_limit' => 30]);

        $fake = new FakeRukaProvider();
        $this->app->instance(RukaProvider::class, $fake);

        $lesson = $this->seedGerakLurusLesson();
        $user = User::factory()->create();

        for ($i = 1; $i <= 6; $i++) {
            $this->actingAs($user)
                ->postJson(route('ruka.store', $lesson), ['message' => "Pertanyaan cepat ke-{$i}"])
                ->assertOk();
        }

        // Pesan ke-7 dalam 1 menit ditolak dengan HTTP 429
        $this->actingAs($user)
            ->postJson(route('ruka.store', $lesson), ['message' => 'Pertanyaan ketujuh dalam semenit'])
            ->assertStatus(429);
    }

    public function test_google_ai_studio_provider_sends_key_in_header_and_parses_candidate_text(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Gunakan rumus $s = v \cdot t$ untuk gerak lurus beraturan.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $provider = new GoogleAiStudioRukaProvider(
            apiKey: 'GOOGLE_STUDIO_TEST_KEY',
            model: 'gemini-2.5-flash'
        );

        $reply = $provider->reply(
            [['role' => 'user', 'content' => 'Rumus GLB apa?']],
            'System prompt Ruka'
        );

        $this->assertSame('Gunakan rumus $s = v \cdot t$ untuk gerak lurus beraturan.', $reply);

        Http::assertSent(function (HttpClientRequest $request): bool {
            return str_contains($request->url(), 'models/gemini-2.5-flash:generateContent')
                && ! str_contains($request->url(), 'GOOGLE_STUDIO_TEST_KEY')
                && $request->hasHeader('x-goog-api-key', 'GOOGLE_STUDIO_TEST_KEY');
        });
    }
}
