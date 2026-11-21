<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            LessonSeeder::class,
            QuestionSeeder::class,
            TryoutSeeder::class,
            UserSeeder::class,
            AttemptSeeder::class,
        ]);
    }
}
