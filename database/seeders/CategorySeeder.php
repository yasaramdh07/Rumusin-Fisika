<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Gerak', 'gerak', 'Kinematika: jarak, kecepatan, percepatan, dan gerak lurus.'],
            ['Listrik', 'listrik', 'Muatan, arus, tegangan, hambatan, dan rangkaian listrik.'],
            ['Gaya (Newton)', 'gaya-newton', 'Hukum Newton dan penerapannya pada benda sehari-hari.'],
            ['Energi', 'energi', 'Usaha, energi kinetik dan potensial, serta kekekalan energi.'],
            ['Archimedes', 'archimedes', 'Tekanan zat cair, gaya apung, terapung, melayang, dan tenggelam.'],
            ['Elektronika Dasar', 'elektronika-dasar', 'Komponen dasar elektronika dan cara membaca rangkaian sederhana.'],
        ];

        foreach ($categories as $i => [$name, $slug, $description]) {
            Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'sort_order' => $i + 1],
            );
        }
    }
}
