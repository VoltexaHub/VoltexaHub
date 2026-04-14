<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\WeeklyDigest;
use Illuminate\Console\Command;

class SendDigest extends Command
{
    protected $signature = 'app:send-digest {--dry-run : Show who would receive a digest without sending}';
    protected $description = 'Send the weekly activity digest to users who opted in.';

    public function handle(): int
    {
        $users = User::query()
            ->where('digest_frequency', 'weekly')
            ->whereNotNull('email_verified_at')
            ->get();

        $sent = 0;
        foreach ($users as $user) {
            // Throttle: at most once every 6 days, regardless of cron cadence.
            if ($user->last_digest_sent_at && $user->last_digest_sent_at->gt(now()->subDays(6))) {
                continue;
            }

            $since = $user->last_digest_sent_at ?? now()->subWeek();

            $notifications = $user->notifications()
                ->where('created_at', '>=', $since)
                ->orderBy('created_at')
                ->limit(40)
                ->get();

            if ($notifications->isEmpty()) {
                continue;
            }

            $items = $notifications->map(function ($n) {
                $d = $n->data;
                return match ($d['type'] ?? null) {
                    'thread_reply'    => ['type' => 'reply',    'line' => ($d['author_name'] ?? 'Someone')." replied in \"{$d['thread_title']}\""],
                    'private_message' => ['type' => 'message',  'line' => ($d['author_name'] ?? 'Someone').' sent you a message'],
                    'post_reaction'   => ['type' => 'reaction', 'line' => ($d['reactor_name'] ?? 'Someone')." reacted ({$d['emoji']}) to your post in \"{$d['thread_title']}\""],
                    default           => ['type' => 'other',    'line' => 'New activity'],
                };
            })->all();

            if ($this->option('dry-run')) {
                $this->info("Would send digest with ".count($items)." items to {$user->email}");
            } else {
                $user->notify(new WeeklyDigest($items));
                $user->update(['last_digest_sent_at' => now()]);
                $sent++;
            }
        }

        $this->info("Digest run complete. Sent: {$sent}.");

        return self::SUCCESS;
    }
}
