<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Thread extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'forum_id', 'user_id', 'title', 'slug',
        'is_pinned', 'is_locked',
        'views_count', 'posts_count', 'last_post_id', 'last_post_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'last_post_at' => 'datetime',
    ];

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }

    /**
     * Generate a slug unique within the given forum.
     *
     *   "This is a test" → "this-is-a-test"
     *   (collision)      → "this-is-a-test-2"
     *   (…)              → "this-is-a-test-3"
     *
     * Falls back to an id-hash suffix on pathological contention to stay O(small).
     */
    public static function uniqueSlug(string $title, int $forumId): string
    {
        $base = Str::slug($title);
        if ($base === '') $base = 'thread';

        $existing = static::withTrashed()
            ->where('forum_id', $forumId)
            ->where(fn ($q) => $q->where('slug', $base)->orWhere('slug', 'like', $base.'-%'))
            ->pluck('slug')
            ->all();

        if (! in_array($base, $existing, true)) return $base;

        for ($n = 2; $n <= 50; $n++) {
            $candidate = $base.'-'.$n;
            if (! in_array($candidate, $existing, true)) return $candidate;
        }

        return $base.'-'.Str::lower(Str::random(6));
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function lastPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'last_post_id');
    }

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }
}
