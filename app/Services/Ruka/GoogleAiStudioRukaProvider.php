<?php

namespace App\Services\Ruka;

use App\Contracts\RukaProvider;
use App\Models\RukaMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleAiStudioRukaProvider implements RukaProvider
{
    private const DEFAULT_MODEL = 'gemini-2.5-flash';

    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly ?string $model = null,
    ) {}

    public function reply(array $messages, string $systemPrompt): string
    {
        $key = trim((string) ($this->apiKey ?? config('rumusin.ai.api_key', '')));
        if ($key === '') {
            throw new RuntimeException('Konfigurasi AI_API_KEY belum diisi di server.');
        }

        $modelName = trim((string) ($this->model ?? config('rumusin.ai.model', '')));
        if ($modelName === '') {
            $modelName = self::DEFAULT_MODEL;
        }

        $contents = [];
        foreach ($messages as $msg) {
            $role = ($msg['role'] ?? RukaMessage::ROLE_USER) === RukaMessage::ROLE_ASSISTANT
                ? 'model'
                : 'user';

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => (string) ($msg['content'] ?? '')],
                ],
            ];
        }

        if ($contents === []) {
            throw new RuntimeException('Daftar pesan ke Ruka kosong.');
        }

        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            rawurlencode($modelName)
        );

        try {
            // Kirim API key melalui header x-goog-api-key agar key tidak pernah muncul di URL log atau pesan exception.
            $response = Http::timeout(25)
                ->acceptJson()
                ->withHeaders([
                    'x-goog-api-key' => $key,
                ])
                ->post($endpoint, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemPrompt],
                        ],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 900,
                    ],
                ]);
        } catch (ConnectionException) {
            throw new RuntimeException('Gagal terhubung ke layanan AI.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Layanan AI mengembalikan status '.$response->status().'.');
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Balasan dari layanan AI kosong.');
        }

        return trim($text);
    }
}
