<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));

        $threads = collect();
        $posts = null;

        if ($q !== '') {
            $isPgsql = DB::getDriverName() === 'pgsql';

            $threads = Thread::query()
                ->when($isPgsql,
                    fn ($q2) => $q2->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$q]),
                    fn ($q2) => $q2->where('title', 'like', "%{$q}%"),
                )
                ->with(['author:id,name,email', 'forum:id,name,slug'])
                ->orderByDesc('last_post_at')
                ->limit(10)
                ->get();

            $postsQuery = Post::query()
                ->when($isPgsql,
                    fn ($q2) => $q2->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$q])
                        ->selectRaw("posts.*, ts_rank(search_vector, plainto_tsquery('english', ?)) AS rank", [$q])
                        ->orderByDesc('rank'),
                    fn ($q2) => $q2->where('body', 'like', "%{$q}%"),
                )
                ->with([
                    'author:id,name,email',
                    'thread:id,title,slug,forum_id',
                    'thread.forum:id,name,slug',
                ])
                ->orderByDesc('created_at');

            $posts = $postsQuery->paginate(20)->withQueryString();
        }

        return view('theme::search', compact('q', 'threads', 'posts'));
    }
}
