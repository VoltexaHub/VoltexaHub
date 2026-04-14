<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString() ?: 'pending';

        $reports = Report::query()
            ->where('status', $status)
            ->with([
                'reporter:id,name',
                'resolver:id,name',
                'post:id,body,thread_id,user_id,created_at',
                'post.author:id,name',
                'post.thread:id,title,slug,forum_id',
                'post.thread.forum:id,name,slug',
            ])
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $counts = Report::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports,
            'counts' => [
                'pending' => (int) ($counts['pending'] ?? 0),
                'resolved' => (int) ($counts['resolved'] ?? 0),
                'dismissed' => (int) ($counts['dismissed'] ?? 0),
            ],
            'filter' => $status,
        ]);
    }

    public function dismiss(Request $request, Report $report): RedirectResponse
    {
        $report->update([
            'status' => Report::STATUS_DISMISSED,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return back()->with('flash.success', 'Report dismissed.');
    }

    public function resolveDelete(Request $request, Report $report): RedirectResponse
    {
        $post = $report->post;
        if ($post) {
            $thread = $post->thread;
            $forum = $thread?->forum;

            $post->delete();

            if ($thread) {
                $newLast = $thread->posts()->latest('created_at')->first();
                $thread->update([
                    'posts_count' => $thread->posts()->count(),
                    'last_post_id' => $newLast?->id,
                    'last_post_at' => $newLast?->created_at,
                ]);
            }

            if ($forum) {
                $forumLast = Post::whereIn('thread_id', $forum->threads()->pluck('id'))->latest('created_at')->first();
                $forum->update([
                    'posts_count' => Post::whereIn('thread_id', $forum->threads()->pluck('id'))->count(),
                    'last_post_id' => $forumLast?->id,
                    'last_post_at' => $forumLast?->created_at,
                ]);
            }
        }

        Report::where('post_id', $report->post_id)
            ->where('status', Report::STATUS_PENDING)
            ->update([
                'status' => Report::STATUS_RESOLVED,
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);

        return back()->with('flash.success', 'Post removed and related reports resolved.');
    }
}
