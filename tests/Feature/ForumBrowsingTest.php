<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_forum_index(): void
    {
        $category = Category::factory()->create(['name' => 'Community']);
        Forum::factory()->create(['category_id' => $category->id, 'name' => 'General']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Community')
            ->assertSee('General');
    }

    public function test_guest_can_view_a_forum(): void
    {
        $forum = Forum::factory()->create(['slug' => 'general']);

        $this->get('/forums/general')
            ->assertOk()
            ->assertSee($forum->name);
    }

    public function test_guest_can_view_a_thread_and_increments_view_count(): void
    {
        $user = User::factory()->create();
        $forum = Forum::factory()->create(['slug' => 'general']);
        $thread = Thread::factory()
            ->for($forum)
            ->for($user, 'author')
            ->create(['slug' => 'hello', 'title' => 'Hello world', 'views_count' => 0]);

        $this->get("/forums/general/threads/hello")
            ->assertOk()
            ->assertSee('Hello world');

        $this->assertSame(1, $thread->fresh()->views_count);
    }

    public function test_guest_cannot_create_thread(): void
    {
        $forum = Forum::factory()->create(['slug' => 'general']);

        $this->get('/forums/general/threads/create')->assertRedirect('/login');
        $this->post('/forums/general/threads', ['title' => 'x', 'body' => 'y'])
            ->assertRedirect('/login');
    }
}
