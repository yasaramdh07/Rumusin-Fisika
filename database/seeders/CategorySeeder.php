<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use RuntimeException;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/content/categories.json');
        if (! is_file($path)) {
            throw new RuntimeException("Berkas konten kategori tidak ditemukan: {$path}");
        }

        /** @var array<int, array{name: string, slug: string, description: string, sort_order: int}> $categories */
        $categories = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($categories as $item) {
            Category::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'sort_order' => (int) $item['sort_order'],
                ],
            );
        }
    }
}
