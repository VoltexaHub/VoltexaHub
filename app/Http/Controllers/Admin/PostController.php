<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = Post::query()
            ->with([
                'author:id,name',
                'thread:id,title,slug,forum_id',
                'thread.forum:id,name,slug',
            ])
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('body', 'ilike', "%{$term}%"))
            ->when($request->integer('forum_id'), fn ($q, $id) => $q->whereHas('thread', fn ($t) => $t->where('forum_id', $id)))
            ->when($request->integer('user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'forums' => Forum::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'q' => $request->string('q')->toString(),
                'forum_id' => $request->integer('forum_id') ?: null,
                'user_id' => $request->integer('user_id') ?: null,
            ],
        ]);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $thread = $post->thread;
        AdminActivity::record('post.delete', $post, 'Post #'.$post->id.' in "'.($thread?->title ?? '?').'"');
        $post->delete();

        $thread?->update(['posts_count' => $thread->posts()->count()]);
        $thread?->forum?->update(['posts_count' => $thread->forum->threads()->sum('posts_count')]);

        return back()->with('flash.success', 'Post deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['integer', 'exists:posts,id'],
        ]);

        $touched = Post::whereIn('id', $data['ids'])
            ->get(['id', 'thread_id'])
            ->groupBy('thread_id')
            ->map(fn ($group) => $group->count());

        AdminActivity::record('post.bulk-delete', null, count($data['ids']).' posts', ['ids' => $data['ids']]);

        DB::transaction(function () use ($data, $touched) {
            Post::whereIn('id', $data['ids'])->delete();

            foreach ($touched as $threadId => $removed) {
                Thread::where('id', $threadId)->decrement('posts_count', $removed);
            }

            $forumIds = Thread::whereIn('id', $touched->keys())->pluck('forum_id')->unique();
            foreach ($forumIds as $forumId) {
                Forum::where('id', $forumId)->update([
                    'posts_count' => Thread::where('forum_id', $forumId)->sum('posts_count'),
                ]);
            }
        });

        $count = count($data['ids']);
        return back()->with('flash.success', "Deleted {$count} post".($count === 1 ? '' : 's').'.');
    }
}
