<?php

namespace App\Services;

use Illuminate\Support\Str;

class LessonRenderer
{
    /**
     * Render Markdown ke HTML dengan membuang HTML mentah dan melindungi segmen
     * matematika ($...$ dan $$...$$) supaya karakter seperti '_', '*', dan '\' tidak
     * dirusak oleh parser Markdown. Di browser, segmen matematika dirender dengan
     * KaTeX auto-render.
     */
    public function render(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }

        $placeholders = [];
        $counter = 0;

        // 1. Lindungi blok kode berpagar (```...```) dan inline code (`...`) agar '$' di dalam kode tidak dianggap rumus.
        $codeBlocks = [];
        $protected = (string) preg_replace_callback(
            '/```[\s\S]*?```|`[^`\n]+`/u',
            function (array $matches) use (&$codeBlocks, &$counter): string {
                $token = 'RFCODEBLOCKTOKEN'.$counter.'END';
                $codeBlocks[$token] = $matches[0];
                $counter++;

                return $token;
            },
            $markdown
        );

        // 2. Ekstrak blok matematika $$...$$ lebih dulu.
        $protected = (string) preg_replace_callback(
            '/\$\$([\s\S]+?)\$\$/u',
            function (array $matches) use (&$placeholders, &$counter): string {
                $token = 'RFMATHBLOCKTOKEN'.$counter.'END';
                $placeholders[$token] = '$$'.e($matches[1]).'$$';
                $counter++;

                return $token;
            },
            $protected
        );

        // 3. Ekstrak rumus inline $...$ (satu baris, tidak diawali/diakhiri spasi kosong seperti harga "$100").
        $protected = (string) preg_replace_callback(
            '/(?<!\\\\)\$(?!\s)([^\$\n]+?)(?<!\s)(?<!\\\\)\$/u',
            function (array $matches) use (&$placeholders, &$counter): string {
                $token = 'RFMATHINLINETOKEN'.$counter.'END';
                $placeholders[$token] = '$'.e($matches[1]).'$';
                $counter++;

                return $token;
            },
            $protected
        );

        // Kembalikan blok kode sebelum diparse oleh CommonMark.
        if ($codeBlocks !== []) {
            $protected = strtr($protected, $codeBlocks);
        }

        // 4. Parse Markdown secara aman (HTML mentah di-escape, tautan berbahaya dicegah).
        $html = Str::markdown($protected, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);

        // 5. Kembalikan segmen matematika yang sudah di-escape HTML-nya agar aman untuk KaTeX auto-render di DOM.
        if ($placeholders !== []) {
            $html = strtr($html, $placeholders);
        }

        return trim($html);
    }
}
