<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'in:spam,harassment,off-topic,illegal,other'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($post->user_id === $request->user()->id) {
            return back()->with('flash.error', 'You cannot report your own post.');
        }

        Report::updateOrCreate(
            ['post_id' => $post->id, 'reporter_id' => $request->user()->id],
            [
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
                'status' => Report::STATUS_PENDING,
                'resolved_by' => null,
                'resolved_at' => null,
            ],
        );

        return back()->with('flash.success', 'Thanks, a moderator will review this report.');
    }
}
