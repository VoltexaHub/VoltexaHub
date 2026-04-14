<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ThreadController extends Controller
{
    public function show(Forum $forum, Thread $thread): View
    {
        abort_unless($thread->forum_id === $forum->id, 404);

        $thread->increment('views_count');

        $user = request()->user();
        $blockedIds = $user ? \App\Models\UserBlock::blockedBy($user->id) : [];

        $posts = $thread->posts()
            ->with(['author:id,name', 'reactions:id,post_id,user_id,emoji'])
            ->when(! empty($blockedIds), fn ($q) => $q->whereNotIn('user_id', $blockedIds))
            ->orderBy('created_at')
            ->paginate(20);

        $thread->load(['author:id,name', 'poll.options']);
        $forum->load('category:id,name,slug');

        $mutedByUser = false;
        $previouslyReadAt = null;
        $bookmarked = false;

        if ($user) {
            $sub = \App\Models\ThreadSubscription::query()
                ->where('user_id', $user->id)
                ->where('thread_id', $thread->id)
                ->first();

            $mutedByUser = $sub && $sub->state === \App\Models\ThreadSubscription::STATE_MUTED;
            $previouslyReadAt = $sub?->last_read_at;

            \App\Models\ThreadSubscription::updateOrCreate(
                ['user_id' => $user->id, 'thread_id' => $thread->id],
                [
                    'state' => $sub->state ?? \App\Models\ThreadSubscription::STATE_SUBSCRIBED,
                    'last_read_at' => now(),
                ],
            );

            $bookmarked = \App\Models\Bookmark::where('user_id', $user->id)
                ->where('thread_id', $thread->id)
                ->exists();
        }

        return view('theme::thread-show', compact('forum', 'thread', 'posts', 'mutedByUser', 'previouslyReadAt', 'bookmarked'));
    }

    public function create(Forum $forum): View
    {
        return view('theme::thread-create', compact('forum'));
    }

    public function unread(Forum $forum, Thread $thread): RedirectResponse
    {
        abort_unless($thread->forum_id === $forum->id, 404);

        $target = $thread->posts()->oldest('created_at')->first();

        if ($user = request()->user()) {
            $sub = \App\Models\ThreadSubscription::query()
                ->where('user_id', $user->id)
                ->where('thread_id', $thread->id)
                ->first();

            if ($sub?->last_read_at) {
                $candidate = $thread->posts()
                    ->where('created_at', '>', $sub->last_read_at)
                    ->oldest('created_at')
                    ->first();
                if ($candidate) {
                    $target = $candidate;
                }
            }
        }

        $anchor = $target ? '#post-'.$target->id : '';

        return redirect()->to(route('threads.show', [$forum->slug, $thread->slug]).$anchor);
    }

    public function store(Request $request, Forum $forum): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'body' => ['required', 'string', 'min:2'],
            'poll_question' => ['nullable', 'string', 'max:250'],
            'poll_options' => ['nullable', 'array', 'min:2', 'max:10'],
            'poll_options.*' => ['nullable', 'string', 'max:200'],
            'poll_allow_multiple' => ['nullable', 'boolean'],
        ]);

        $thread = $forum->threads()->create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(6),
        ]);

        $post = $thread->posts()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $thread->update([
            'posts_count' => 1,
            'last_post_id' => $post->id,
            'last_post_at' => $post->created_at,
        ]);

        $forum->update([
            'threads_count' => $forum->threads()->count(),
            'posts_count' => $forum->posts_count + 1,
            'last_post_id' => $post->id,
            'last_post_at' => $post->created_at,
        ]);

        $cleanOptions = array_values(array_filter(
            array_map('trim', (array) ($data['poll_options'] ?? [])),
            fn ($s) => $s !== '',
        ));
        if (! empty($data['poll_question']) && count($cleanOptions) >= 2) {
            $poll = $thread->poll()->create([
                'question' => $data['poll_question'],
                'allow_multiple' => (bool) ($data['poll_allow_multiple'] ?? false),
            ]);
            foreach ($cleanOptions as $i => $text) {
                $poll->options()->create(['text' => $text, 'position' => $i]);
            }
        }

        return redirect()->route('threads.show', [$forum->slug, $thread->slug]);
    }
}
