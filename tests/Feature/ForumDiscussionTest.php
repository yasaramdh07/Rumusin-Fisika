<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Lesson;
use App\Models\Reply;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumDiscussionTest extends TestCase
{
    use RefreshDatabase;

    private function createPublishedLesson(): Lesson
    {
        $category = Category::create([
            'name' => 'Gerak',
            'slug' => 'gerak',
            'sort_order' => 1,
        ]);

        return Lesson::create([
            'category_id' => $category->id,
            'title' => 'Gerak Lurus',
            'slug' => 'gerak-lurus',
            'summary' => 'Ringkasan gerak lurus',
            'body' => 'Isi materi gerak lurus',
            'is_published' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_guest_sees_login_prompt_and_cannot_post_thread_or_reply(): void
    {
        $lesson = $this->createPublishedLesson();
        $author = User::factory()->create();
        $thread = Thread::create([
            'lesson_id' => $lesson->id,
            'user_id' => $author->id,
            'title' => 'Pertanyaan tentang kecepatan rata-rata',
            'body' => 'Bagaimana membedakan kelajuan dan kecepatan?',
        ]);

        $this->get(route('lessons.show', $lesson))
            ->assertOk()
            ->assertSee('Masuk untuk berdiskusi')
            ->assertSee('Pertanyaan tentang kecepatan rata-rata');

        $this->post(route('discussions.threads.store', $lesson), [
            'title' => 'Judul dari tamu',
            'body' => 'Isi dari tamu',
        ])->assertRedirect('/masuk');

        $this->post(route('discussions.replies.store', $thread), [
            'body' => 'Balasan dari tamu',
        ])->assertRedirect('/masuk');

        $this->assertSame(1, Thread::count());
        $this->assertSame(0, Reply::count());
    }

    public function test_html_tags_and_urls_are_escaped_as_plain_text_and_newlines_are_preserved(): void
    {
        $lesson = $this->createPublishedLesson();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('discussions.threads.store', $lesson), [
                'title' => 'Tanya <script>alert(1)</script> rumus',
                'body' => "Baris pertama <b>tebal</b>\nBaris kedua https://contoh.test",
            ])
            ->assertRedirect();

        $thread = Thread::firstOrFail();

        $this->actingAs($user)
            ->post(route('discussions.replies.store', $thread), [
                'body' => "Balasan <img src=x onerror=alert(2)>\nBaris baru balasan",
            ])
            ->assertRedirect();

        $response = $this->get(route('lessons.show', $lesson))->assertOk();
        $html = $response->getContent();

        // Tag berbahaya tidak boleh dieksekusi sebagai elemen HTML
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(2)>', $html);
        $this->assertStringNotContainsString('<b>tebal</b>', $html);

        // Tetapi tampil sebagai teks yang di-escape dan baris baru menjadi <br>
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('&lt;b&gt;tebal&lt;/b&gt;<br', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(2)&gt;<br', $html);

        // URL tidak otomatis menjadi <a href="https://contoh.test">
        $this->assertStringNotContainsString('href="https://contoh.test"', $html);
        $this->assertStringContainsString('https://contoh.test', $html);
    }

    public function test_users_can_only_soft_delete_their_own_posts_while_admin_can_delete_all_and_moderate(): void
    {
        $lesson = $this->createPublishedLesson();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $thread = Thread::create([
            'lesson_id' => $lesson->id,
            'user_id' => $owner->id,
            'title' => 'Diskusi milik owner',
            'body' => 'Isi diskusi owner',
        ]);

        $reply = Reply::create([
            'thread_id' => $thread->id,
            'user_id' => $owner->id,
            'body' => 'Balasan milik owner',
        ]);

        // User lain ditolak (403)
        $this->actingAs($other)
            ->delete(route('discussions.threads.destroy', $thread))
            ->assertForbidden();

        $this->actingAs($other)
            ->delete(route('discussions.replies.destroy', $reply))
            ->assertForbidden();

        // Owner bisa menghapus balasannya sendiri (soft delete)
        $this->actingAs($owner)
            ->delete(route('discussions.replies.destroy', $reply))
            ->assertRedirect();

        $this->assertSoftDeleted('replies', ['id' => $reply->id]);

        // Admin bisa membuka halaman moderasi /admin/diskusi dan menghapus thread siapa saja (soft delete)
        $this->actingAs($admin)
            ->get(route('admin.discussions.index'))
            ->assertOk()
            ->assertSee('Diskusi milik owner');

        $this->actingAs($admin)
            ->delete(route('admin.discussions.threads.destroy', $thread))
            ->assertRedirect(route('admin.discussions.index'));

        $this->assertSoftDeleted('threads', ['id' => $thread->id]);
    }

    public function test_posting_is_throttled_to_five_posts_per_minute(): void
    {
        $lesson = $this->createPublishedLesson();
        $user = User::factory()->create();

        for ($i = 1; $i <= 5; $i++) {
            $this->actingAs($user)
                ->post(route('discussions.threads.store', $lesson), [
                    'title' => "Pertanyaan ke-{$i} tentang GLBB",
                    'body' => "Isi pertanyaan ke-{$i}",
                ])
                ->assertSessionHasNoErrors();
        }

        // Kiriman ke-6 dalam 1 menit ditolak oleh throttle
        $this->actingAs($user)
            ->from(route('lessons.show', $lesson))
            ->post(route('discussions.threads.store', $lesson), [
                'title' => 'Pertanyaan keenam yang melewati batas',
                'body' => 'Isi pertanyaan keenam',
            ])
            ->assertRedirect(route('lessons.show', $lesson))
            ->assertSessionHasErrors('thread_title');

        $this->assertSame(5, Thread::count());
    }
}
