<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    protected $fillable = ['thread_id', 'question', 'allow_multiple', 'closes_at'];

    protected $casts = [
        'allow_multiple' => 'boolean',
        'closes_at' => 'datetime',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('position');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function isClosed(): bool
    {
        return $this->closes_at && $this->closes_at->isPast();
    }

    public function totalVotes(): int
    {
        return (int) $this->options->sum('votes_count');
    }

    /** Returns the set of option ids the given user has voted for. */
    public function userVoteOptionIds(?int $userId): array
    {
        if (! $userId) return [];
        return $this->votes->where('user_id', $userId)->pluck('poll_option_id')->all();
    }
}
