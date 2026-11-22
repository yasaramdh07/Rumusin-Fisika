<?php

namespace App\Services;

class YoutubeUrlParser
{
    /**
     * Ekstrak 11-karakter YouTube video ID dari URL youtube.com atau youtu.be.
     * Mengembalikan null jika URL bukan YouTube atau format ID tidak valid.
     */
    public function parseId(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        $parts = parse_url($trimmed);
        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) $parts['host']);
        $path = (string) ($parts['path'] ?? '');
        $candidate = null;

        if ($host === 'youtu.be' || $host === 'www.youtu.be') {
            $segments = array_values(array_filter(explode('/', trim($path, '/'))));
            $candidate = $segments[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            $segments = array_values(array_filter(explode('/', trim($path, '/'))));

            if (($segments[0] ?? null) === 'watch') {
                parse_str((string) ($parts['query'] ?? ''), $query);
                $candidate = is_string($query['v'] ?? null) ? $query['v'] : null;
            } elseif (in_array($segments[0] ?? null, ['embed', 'shorts', 'live', 'v'], true)) {
                $candidate = $segments[1] ?? null;
            }
        }

        if (! is_string($candidate) || ! preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate)) {
            return null;
        }

        return $candidate;
    }
}
