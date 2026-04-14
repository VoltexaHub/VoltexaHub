<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Thread;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request): View
    {
        $bookmarks = Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'thread:id,title,slug,forum_id,posts_count,last_post_at,user_id',
                'thread.forum:id,name,slug',
                'thread.author:id,name,email',
            ])
            ->latest()
            ->paginate(25);

        return view('theme::bookmarks-index', compact('bookmarks'));
    }

    public function store(Request $request, Thread $thread): RedirectResponse
    {
        Bookmark::firstOrCreate([
            'user_id' => $request->user()->id,
            'thread_id' => $thread->id,
        ]);

        return back()->with('flash.success', 'Bookmarked.');
    }

    public function destroy(Request $request, Thread $thread): RedirectResponse
    {
        Bookmark::where('user_id', $request->user()->id)
            ->where('thread_id', $thread->id)
            ->delete();

        return back()->with('flash.success', 'Bookmark removed.');
    }
}
