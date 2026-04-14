<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use Illuminate\Contracts\View\View;

class ForumController extends Controller
{
    public function show(Forum $forum): View
    {
        $forum->load('category:id,name,slug');

        $threads = $forum->threads()
            ->with(['author:id,name', 'lastPost.author:id,name'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_post_at')
            ->paginate(20);

        // Which threads on this page have unread activity for the viewer?
        $lastReadMap = [];
        if ($user = request()->user()) {
            $lastReadMap = \App\Models\ThreadSubscription::query()
                ->where('user_id', $user->id)
                ->whereIn('thread_id', $threads->pluck('id'))
                ->pluck('last_read_at', 'thread_id')
                ->all();
        }

        return view('theme::forum-show', compact('forum', 'threads', 'lastReadMap'));
    }
}
