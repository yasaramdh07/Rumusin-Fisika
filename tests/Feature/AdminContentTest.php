<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\LessonVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_normal_user_cannot_access_admin_content_routes(): void
    {
        $user = User::factory()->create();

        $this->get('/admin/kategori')->assertRedirect('/masuk');
        $this->get('/admin/materi')->assertRedirect('/masuk');

        $this->actingAs($user)->get('/admin/kategori')->assertForbidden();
        $this->actingAs($user)->get('/admin/materi')->assertForbidden();
        $this->actingAs($user)->post('/admin/kategori', [
            'name' => 'Optik',
            'sort_order' => 1,
        ])->assertForbidden();
    }

    public function test_admin_can_crud_and_reorder_categories(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/kategori', [
                'name' => 'Gelombang dan Bunyi',
                'description' => 'Getaran, gelombang mekanik, dan bunyi.',
                'sort_order' => 1,
            ])
            ->assertRedirect('/admin/kategori');

        $this->actingAs($admin)
            ->post('/admin/kategori', [
                'name' => 'Optika Geometri',
                'description' => 'Pemantulan dan pembiasan cahaya.',
                'sort_order' => 2,
            ])
            ->assertRedirect('/admin/kategori');

        $first = Category::where('slug', 'gelombang-dan-bunyi')->firstOrFail();
        $second = Category::where('slug', 'optika-geometri')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.categories.up', $second))
            ->assertRedirect('/admin/kategori');

        $this->assertLessThan($first->fresh()->sort_order, $second->fresh()->sort_order);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $first), [
                'name' => 'Gelombang Mekanik',
                'description' => 'Diperbarui',
                'sort_order' => 5,
            ])
            ->assertRedirect('/admin/kategori');

        $this->assertSame('gelombang-mekanik', $first->fresh()->slug);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $second))
            ->assertRedirect('/admin/kategori');

        $this->assertDatabaseMissing('categories', ['id' => $second->id]);
    }

    public function test_admin_can_create_full_lesson_preview_markdown_and_manage_three_videos_end_to_end(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Gaya (Newton)',
            'slug' => 'gaya-newton',
            'description' => 'Hukum Newton tentang gerak.',
            'sort_order' => 1,
        ]);

        // Pratinjau Markdown + KaTeX memakai LessonRenderer yang sama
        $this->actingAs($admin)
            ->postJson(route('admin.lessons.preview'), [
                'body' => 'Gaya total $\Sigma F = m \cdot a_1$ dan <script>alert(1)</script>',
                'formulas' => '$$\Sigma F = m\,a$$',
            ])
            ->assertOk()
            ->assertJsonFragment([
                'formulas_html' => '<p>$$\Sigma F = m\,a$$</p>',
            ])
            ->assertDontSee('<script>', false);

        // Buat materi baru lengkap dan langsung terbit
        $this->actingAs($admin)
            ->post(route('admin.lessons.store'), [
                'category_id' => $category->id,
                'title' => 'Hukum II Newton',
                'summary' => 'Hubungan antara resultan gaya, massa, dan percepatan.',
                'body' => "Percepatan berbanding lurus dengan resultan gaya:\n\n$$\Sigma F = m\\,a$$",
                'formulas' => '$$\Sigma F = m\,a$$',
                'sort_order' => 1,
                'is_published' => '1',
            ])
            ->assertRedirect();

        $lesson = Lesson::where('slug', 'hukum-ii-newton')->firstOrFail();
        $this->assertTrue($lesson->is_published);

        // Tambahkan 3 video bertahap (campuran youtube.com dan youtu.be)
        $videosInput = [
            ['Pengantar gaya dan kelembaman', 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 8],
            ['Penurunan rumus F = m a', 'https://youtu.be/9bZkp7q19f0?si=contoh', 12],
            ['Contoh soal bidang datar', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 15],
        ];

        foreach ($videosInput as [$title, $url, $minutes]) {
            $this->actingAs($admin)
                ->post(route('admin.lessons.videos.store', $lesson), [
                    'title' => $title,
                    'youtube_url' => $url,
                    'duration_minutes' => $minutes,
                ])
                ->assertSessionHasNoErrors();
        }

        $videos = $lesson->videos()->get();
        $this->assertCount(3, $videos);
        $this->assertSame(['jNQXAC9IVRw', '9bZkp7q19f0', 'dQw4w9WgXcQ'], $videos->pluck('youtube_id')->all());
        $this->assertSame([1, 2, 3], $videos->pluck('position')->all());

        // Naikkan video ke-3 menjadi posisi ke-2
        $thirdVideo = $videos[2];
        $this->actingAs($admin)
            ->post(route('admin.lessons.videos.up', [$lesson, $thirdVideo]))
            ->assertRedirect();

        $orderedIds = $lesson->videos()->pluck('youtube_id')->all();
        $this->assertSame(['jNQXAC9IVRw', 'dQw4w9WgXcQ', '9bZkp7q19f0'], $orderedIds);

        // Turunkan kembali
        $this->actingAs($admin)
            ->post(route('admin.lessons.videos.down', [$lesson, $thirdVideo]))
            ->assertRedirect();

        $this->assertSame(['jNQXAC9IVRw', '9bZkp7q19f0', 'dQw4w9WgXcQ'], $lesson->videos()->pluck('youtube_id')->all());

        // Pastikan tampil benar di sisi user publik
        $this->get('/materi/hukum-ii-newton')
            ->assertOk()
            ->assertSee('Hukum II Newton')
            ->assertSee('$$\Sigma F = m\,a$$', false)
            ->assertSee('youtube-nocookie.com/embed/jNQXAC9IVRw', false)
            ->assertSee('data-youtube-id="9bZkp7q19f0"', false)
            ->assertSee('data-youtube-id="dQw4w9WgXcQ"', false);
    }

    public function test_non_youtube_or_malformed_video_urls_are_rejected_with_clear_indonesian_error(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create([
            'name' => 'Energi',
            'slug' => 'energi',
            'sort_order' => 1,
        ]);
        $lesson = Lesson::create([
            'category_id' => $category->id,
            'title' => 'Usaha dan Energi',
            'slug' => 'usaha-dan-energi',
            'summary' => 'Ringkasan',
            'body' => 'Isi',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        foreach ([
            'https://vimeo.com/123456789',
            'https://example.com/watch?v=jNQXAC9IVRw',
            'https://www.youtube.com/watch?v=terlalupendek',
            'javascript:alert(1)',
        ] as $invalidUrl) {
            $response = $this->actingAs($admin)
                ->from(route('admin.lessons.edit', $lesson))
                ->post(route('admin.lessons.videos.store', $lesson), [
                    'title' => 'Video tidak valid',
                    'youtube_url' => $invalidUrl,
                ]);

            $response->assertSessionHasErrors('youtube_url');
            $this->assertStringContainsString(
                'Link bukan dari YouTube yang valid',
                (string) session('errors')?->first('youtube_url')
            );
        }

        $this->assertSame(0, LessonVideo::count());
    }
}
