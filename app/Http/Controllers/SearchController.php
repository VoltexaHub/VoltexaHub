<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q      = trim((string) $request->input('q', ''));
        $type   = in_array($request->input('type'), ['posts', 'threads'], true) ? $request->input('type') : 'all';
        $forumId = $request->input('forum') ? (int) $request->input('forum') : null;
        $author = trim((string) $request->input('author', ''));
        $from   = $request->input('from');
        $to     = $request->input('to');

        $threads = collect();
        $posts = null;

        $authorId = null;
        if ($author !== '') {
            $authorId = User::where('name', $author)->value('id');
        }

        if ($q !== '' || $forumId || $authorId || $from || $to) {
            $isPgsql = DB::getDriverName() === 'pgsql';

            if ($type !== 'posts') {
                $threads = Thread::query()
                    ->when($q !== '', function ($qq) use ($q, $isPgsql) {
                        $isPgsql
                            ? $qq->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$q])
                            : $qq->where('title', 'like', "%{$q}%");
                    })
                    ->when($forumId, fn ($qq) => $qq->where('forum_id', $forumId))
                    ->when($authorId, fn ($qq) => $qq->where('user_id', $authorId))
                    ->when($from, fn ($qq) => $qq->where('created_at', '>=', $from))
                    ->when($to, fn ($qq) => $qq->where('created_at', '<=', $to.' 23:59:59'))
                    ->with(['author:id,name,email', 'forum:id,name,slug'])
                    ->orderByDesc('last_post_at')
                    ->limit(20)
                    ->get();
            }

            if ($type !== 'threads') {
                $postsQuery = Post::query()
                    ->when($q !== '', function ($qq) use ($q, $isPgsql) {
                        $isPgsql
                            ? $qq->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$q])
                                ->selectRaw("posts.*, ts_rank(search_vector, plainto_tsquery('english', ?)) AS rank", [$q])
                                ->orderByDesc('rank')
                            : $qq->where('body', 'like', "%{$q}%");
                    })
                    ->when($forumId, fn ($qq) => $qq->whereHas('thread', fn ($t) => $t->where('forum_id', $forumId)))
                    ->when($authorId, fn ($qq) => $qq->where('user_id', $authorId))
                    ->when($from, fn ($qq) => $qq->where('created_at', '>=', $from))
                    ->when($to, fn ($qq) => $qq->where('created_at', '<=', $to.' 23:59:59'))
                    ->with([
                        'author:id,name,email',
                        'thread:id,title,slug,forum_id',
                        'thread.forum:id,name,slug',
                    ])
                    ->orderByDesc('created_at');

                $posts = $postsQuery->paginate(20)->withQueryString();
            }
        }

        $forums = Forum::orderBy('name')->get(['id', 'name']);

        return view('theme::search', [
            'q' => $q,
            'type' => $type,
            'forumId' => $forumId,
            'author' => $author,
            'from' => $from,
            'to' => $to,
            'threads' => $threads,
            'posts' => $posts,
            'forums' => $forums,
        ]);
    }
}
