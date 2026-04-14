<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use App\Notifications\NewThreadReply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ThreadPostingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authed_user_can_create_a_thread_with_first_post(): void
    {
        $user = User::factory()->create();
        $forum = Forum::factory()->create(['slug' => 'general', 'threads_count' => 0, 'posts_count' => 0]);

        $response = $this->actingAs($user)->post('/forums/general/threads', [
            'title' => 'My first thread',
            'body' => 'Hello VoltexaHub',
        ]);

        $thread = Thread::first();
        $this->assertNotNull($thread);
        $response->assertRedirect(route('threads.show', [$forum->slug, $thread->slug]));

        $this->assertSame($user->id, $thread->user_id);
        $this->assertSame('My first thread', $thread->title);
        $this->assertSame(1, $thread->posts_count);
        $this->assertSame(1, $forum->fresh()->threads_count);
        $this->assertSame(1, $forum->fresh()->posts_count);
        $this->assertDatabaseHas('posts', [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => 'Hello VoltexaHub',
        ]);
    }

    public function test_replying_notifies_thread_participants_excluding_the_replier(): void
    {
        Notification::fake();

        $author = User::factory()->create();
        $previousPoster = User::factory()->create();
        $replier = User::factory()->create();

        $forum = Forum::factory()->create(['slug' => 'general']);
        $thread = Thread::factory()
            ->for($forum)
            ->for($author, 'author')
            ->create(['slug' => 'my-thread']);
        $thread->posts()->create(['user_id' => $author->id, 'body' => 'OP']);
        $thread->posts()->create(['user_id' => $previousPoster->id, 'body' => 'reply']);

        $this->actingAs($replier)
            ->post("/forums/{$forum->slug}/threads/{$thread->slug}/posts", ['body' => 'another reply'])
            ->assertRedirect();

        Notification::assertSentTo([$author, $previousPoster], NewThreadReply::class);
        Notification::assertNotSentTo([$replier], NewThreadReply::class);
    }

    public function test_posting_to_a_locked_thread_is_rejected(): void
    {
        $user = User::factory()->create();
        $forum = Forum::factory()->create(['slug' => 'general']);
        $thread = Thread::factory()
            ->for($forum)
            ->for($user, 'author')
            ->create(['slug' => 'locked', 'is_locked' => true]);

        $this->actingAs($user)
            ->post("/forums/{$forum->slug}/threads/{$thread->slug}/posts", ['body' => 'trying'])
            ->assertForbidden();
    }
}
