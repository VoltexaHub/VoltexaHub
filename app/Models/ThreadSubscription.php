<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThreadSubscription extends Model
{
    public const STATE_SUBSCRIBED = 'subscribed';
    public const STATE_MUTED = 'muted';

    protected $fillable = ['user_id', 'thread_id', 'state', 'last_read_at'];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];
}
