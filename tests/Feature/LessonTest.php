<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lesson;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LessonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_and_lessons_index_show_only_published_lessons(): void
    {
        $this->seed([CategorySeeder::class, LessonSeeder::class]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rumusin soalnya,')
            ->assertSee('Lembar Rumus')
            ->assertSee('Gerak');

        $this->get('/materi')
            ->assertOk()
            ->assertSee('Gerak Lurus (GLB dan GLBB)')
            ->assertSee('Hukum Ohm dan Rangkaian Listrik')
            ->assertSee('Hukum Newton tentang Gerak')
            ->assertSee('Usaha dan Energi')
            ->assertSee('Hukum Archimedes')
            ->assertSee('Komponen Elektronika Dasar');
    }

    public function test_published_lesson_renders_formulas_and_staged_videos(): void
    {
        $this->seed([CategorySeeder::class, LessonSeeder::class]);

        $response = $this->get('/materi/gerak-lurus-glb-glbb');

        $response->assertOk()
            ->assertSee('Gerak Lurus (GLB dan GLBB)')
            ->assertSee('Lembar Rumus')
            ->assertSee('$$v_t = v_0 + a\,t$$', false)
            ->assertSee('data-physics-tool="gerak"', false)
            ->assertSee('Simulasi &amp; Kalkulator Rumus', false)
            ->assertSee('youtube-nocookie.com/embed/ZM8ECpBuQYE', false)
            ->assertSee('data-youtube-id="ZM8ECpBuQYE"', false)
            ->assertSee('Diskusi')
            ->assertSee('Tanya Ruka');
    }

    public function test_unpublished_lesson_returns_404_for_public(): void
    {
        $category = Category::create([
            'name' => 'Listrik',
            'slug' => 'listrik',
            'description' => 'Listrik statis dan dinamis',
            'sort_order' => 1,
        ]);

        Lesson::create([
            'category_id' => $category->id,
            'title' => 'Materi Draf',
            'slug' => 'materi-draf',
            'summary' => 'Ringkasan draf',
            'body' => 'Isi draf',
            'is_published' => false,
            'sort_order' => 1,
        ]);

        $this->get('/materi/materi-draf')->assertNotFound();
    }
}
