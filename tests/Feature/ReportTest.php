<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\Post;
use App\Models\Report;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_report_another_users_post(): void
    {
        $author = User::factory()->create();
        $reporter = User::factory()->create();
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->for($author, 'author')->create();
        $post = Post::factory()->for($thread)->for($author, 'author')->create();

        $this->actingAs($reporter)
            ->post("/posts/{$post->id}/report", ['reason' => 'spam', 'note' => 'Looks like spam'])
            ->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'post_id' => $post->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_report_own_post(): void
    {
        $user = User::factory()->create();
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->for($user, 'author')->create();
        $post = Post::factory()->for($thread)->for($user, 'author')->create();

        $this->actingAs($user)
            ->from('/')
            ->post("/posts/{$post->id}/report", ['reason' => 'spam']);

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_admin_can_resolve_delete_a_report(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();
        $forum = Forum::factory()->create();
        $thread = Thread::factory()->for($forum)->for($author, 'author')->create();
        $post = Post::factory()->for($thread)->for($author, 'author')->create();
        $report = Report::create([
            'post_id' => $post->id,
            'reporter_id' => $admin->id,
            'reason' => 'spam',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post("/admin/reports/{$report->id}/resolve-delete")
            ->assertRedirect();

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'resolved']);
    }
}
