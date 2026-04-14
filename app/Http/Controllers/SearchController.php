<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));

        $threads = collect();
        $posts = null;

        if ($q !== '') {
            $threads = Thread::query()
                ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$q])
                ->with(['author:id,name,email', 'forum:id,name,slug'])
                ->orderByDesc('last_post_at')
                ->limit(10)
                ->get();

            $posts = Post::query()
                ->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$q])
                ->selectRaw("posts.*, ts_rank(search_vector, plainto_tsquery('english', ?)) AS rank", [$q])
                ->with([
                    'author:id,name,email',
                    'thread:id,title,slug,forum_id',
                    'thread.forum:id,name,slug',
                ])
                ->orderByDesc('rank')
                ->orderByDesc('created_at')
                ->paginate(20)
                ->withQueryString();
        }

        return view('theme::search', compact('q', 'threads', 'posts'));
    }
}
