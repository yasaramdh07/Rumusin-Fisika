<?php

namespace App\Services\Ruka;

use App\Contracts\RukaProvider;

class FakeRukaProvider implements RukaProvider
{
    /** @var array<int, array{messages: array<int, array{role: string, content: string}>, systemPrompt: string}> */
    public array $recordedCalls = [];

    public bool $shouldFail = false;

    public function reply(array $messages, string $systemPrompt): string
    {
        $this->recordedCalls[] = [
            'messages' => $messages,
            'systemPrompt' => $systemPrompt,
        ];

        if ($this->shouldFail) {
            throw new \RuntimeException('Simulasi gangguan koneksi provider AI.');
        }

        $lastMessage = end($messages);
        $question = is_array($lastMessage) ? (string) ($lastMessage['content'] ?? '') : '';

        $lessonTitle = 'materi fisika ini';
        if (preg_match('/Judul materi:\s*(.+)/u', $systemPrompt, $matches)) {
            $lessonTitle = trim($matches[1]);
        }

        return "Kita bahas bareng dari materi **{$lessonTitle}** ya.\n\n"
            ."Untuk pertanyaanmu tentang \"{$question}\", coba ikuti langkah ini:\n"
            ."1. Tulis dulu besaran yang diketahui dan satuannya dalam SI.\n"
            ."2. Pilih rumus dari Lembar Rumus yang sesuai, misalnya \$v_t = v_0 + a\\,t\$ atau \$s = v\\,t\$.\n"
            ."3. Masukkan angkanya lalu cek apakah satuannya sudah cocok.\n\n"
            .'Sampai langkah pemilihan rumus ini, bagian mana yang masih bikin ragu?';
    }
}
