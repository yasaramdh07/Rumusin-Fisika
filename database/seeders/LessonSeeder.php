<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use RuntimeException;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $dir = database_path('seeders/content/lessons');
        $files = glob($dir.'/*.md') ?: [];
        sort($files);

        if ($files === []) {
            throw new RuntimeException("Tidak ada berkas materi (.md) di {$dir}");
        }

        foreach ($files as $file) {
            [$meta, $body] = $this->parseMarkdownWithFrontMatter((string) file_get_contents($file), $file);

            $category = Category::where('slug', $meta['category'])->firstOrFail();

            $lesson = Lesson::updateOrCreate(
                ['slug' => $meta['slug']],
                [
                    'category_id' => $category->id,
                    'title' => $meta['title'],
                    'summary' => $meta['summary'],
                    'body' => trim($body),
                    'formulas' => isset($meta['formulas']) && trim((string) $meta['formulas']) !== ''
                        ? trim((string) $meta['formulas'])
                        : null,
                    'is_published' => (bool) ($meta['is_published'] ?? true),
                    'sort_order' => (int) ($meta['sort_order'] ?? 1),
                ],
            );

            $videos = is_array($meta['videos'] ?? null) ? $meta['videos'] : [];
            $positions = [];

            foreach (array_values($videos) as $idx => $video) {
                $position = $idx + 1;
                $positions[] = $position;

                $lesson->videos()->updateOrCreate(
                    ['position' => $position],
                    [
                        'title' => (string) $video['title'],
                        'youtube_url' => (string) $video['youtube_url'],
                        'youtube_id' => (string) $video['youtube_id'],
                        'duration_seconds' => isset($video['duration_seconds']) ? (int) $video['duration_seconds'] : null,
                    ],
                );
            }

            if ($positions === []) {
                $lesson->videos()->delete();
            } else {
                $lesson->videos()->whereNotIn('position', $positions)->delete();
            }
        }
    }

    /**
     * Parser front matter YAML ringan untuk berkas materi (.md).
     *
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function parseMarkdownWithFrontMatter(string $raw, string $file): array
    {
        $normalized = str_replace("\r\n", "\n", $raw);

        if (! preg_match('/^---\n(.*?)\n---\n(.*)$/s', $normalized, $matches)) {
            throw new RuntimeException("Front matter YAML tidak valid pada berkas: {$file}");
        }

        $meta = $this->parseSimpleYaml($matches[1]);

        return [$meta, $matches[2]];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseSimpleYaml(string $yamlText): array
    {
        $lines = explode("\n", $yamlText);
        $count = count($lines);
        $i = 0;
        $result = [];

        while ($i < $count) {
            $line = $lines[$i];

            if (trim($line) === '' || str_starts_with(trim($line), '#')) {
                $i++;

                continue;
            }

            if (! preg_match('/^([a-zA-Z0-9_]+):\s*(.*)$/', $line, $m)) {
                $i++;

                continue;
            }

            $key = $m[1];
            $val = trim($m[2]);

            // Block scalar (`|`)
            if ($val === '|') {
                $i++;
                $blockLines = [];
                while ($i < $count && (str_starts_with($lines[$i], '  ') || trim($lines[$i]) === '')) {
                    $blockLines[] = str_starts_with($lines[$i], '  ')
                        ? substr($lines[$i], 2)
                        : '';
                    $i++;
                }
                $result[$key] = implode("\n", $blockLines);

                continue;
            }

            // Nested list (e.g. `videos:`)
            if ($val === '') {
                $i++;
                $list = [];
                $current = null;

                while ($i < $count && (str_starts_with($lines[$i], '  ') || trim($lines[$i]) === '')) {
                    $sub = $lines[$i];
                    if (trim($sub) === '') {
                        $i++;

                        continue;
                    }

                    if (preg_match('/^\s*-\s+([a-zA-Z0-9_]+):\s*(.*)$/', $sub, $lm)) {
                        if ($current !== null) {
                            $list[] = $current;
                        }
                        $current = [
                            $lm[1] => $this->castScalar(trim($lm[2])),
                        ];
                    } elseif ($current !== null && preg_match('/^\s+([a-zA-Z0-9_]+):\s*(.*)$/', $sub, $pm)) {
                        $current[$pm[1]] = $this->castScalar(trim($pm[2]));
                    }

                    $i++;
                }

                if ($current !== null) {
                    $list[] = $current;
                }

                $result[$key] = $list;

                continue;
            }

            $result[$key] = $this->castScalar($val);
            $i++;
        }

        return $result;
    }

    private function castScalar(string $val): mixed
    {
        $unquoted = trim($val, "\"'");
        if ($unquoted === 'true') {
            return true;
        }
        if ($unquoted === 'false') {
            return false;
        }
        if ($unquoted === 'null') {
            return null;
        }
        if (preg_match('/^-?\d+$/', $unquoted)) {
            return (int) $unquoted;
        }

        return $unquoted;
    }
}
