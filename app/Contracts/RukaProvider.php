<?php

namespace App\Contracts;

interface RukaProvider
{
    /**
     * Kirim riwayat pesan dan system prompt ke model AI lalu kembalikan balasan teks.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function reply(array $messages, string $systemPrompt): string;
}
