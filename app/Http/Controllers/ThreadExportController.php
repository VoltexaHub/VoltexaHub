<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ThreadExportController extends Controller
{
    public function __invoke(Request $request, Forum $forum, Thread $thread): Response
    {
        abort_unless($thread->forum_id === $forum->id, 404);

        $user = $request->user();
        abort_unless($user && ($user->is_admin || $user->id === $thread->user_id), 403);

        $thread->load([
            'author:id,name',
            'posts' => fn ($q) => $q->orderBy('created_at')->with('author:id,name'),
        ]);

        $lines = [];
        $lines[] = "# {$thread->title}";
        $lines[] = '';
        $lines[] = '*In '.($thread->forum->name ?? 'forum').' · started '.$thread->created_at->toDayDateTimeString().'*';
        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';

        foreach ($thread->posts as $i => $post) {
            $author = $post->author?->name ?? '[deleted user]';
            $when = $post->created_at->toDayDateTimeString();
            $lines[] = "## #".($i + 1)." — {$author}";
            $lines[] = "*{$when}*";
            $lines[] = '';
            $lines[] = rtrim($post->body);
            $lines[] = '';
            if ($post->edited_at) {
                $lines[] = "> _edited ".$post->edited_at->toDayDateTimeString().'_';
                $lines[] = '';
            }
            $lines[] = '---';
            $lines[] = '';
        }

        $lines[] = '_Exported '.now()->toDayDateTimeString().' · VoltexaHub_';

        $body = implode("\n", $lines);
        $filename = \Illuminate\Support\Str::slug($thread->title, '-').'.md';

        return response($body, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
