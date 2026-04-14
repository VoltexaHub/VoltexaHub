<?php

namespace App\Events;

use App\Models\Post;
use App\Services\Markdown;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Post $post) {}

    public function broadcastOn(): array
    {
        return [new Channel('threads.'.$this->post->thread_id)];
    }

    public function broadcastAs(): string
    {
        return 'post.created';
    }

    public function broadcastWith(): array
    {
        $this->post->loadMissing('author:id,name,email');
        $author = $this->post->author;

        return [
            'id' => $this->post->id,
            'thread_id' => $this->post->thread_id,
            'body_html' => app(Markdown::class)->toHtml($this->post->body),
            'created_at' => $this->post->created_at->toIso8601String(),
            'created_at_formatted' => $this->post->created_at->format('M j, Y g:i A'),
            'author' => $author ? [
                'id' => $author->id,
                'name' => $author->name,
                'avatar_url' => $author->avatar_url,
                'profile_url' => route('users.show', $author),
            ] : null,
        ];
    }
}
