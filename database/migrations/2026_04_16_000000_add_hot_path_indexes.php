<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('user_id', 'posts_user_id_idx');
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->index('user_id', 'threads_user_id_idx');
            $table->index(['forum_id', 'is_pinned', 'last_post_at'], 'threads_forum_pinned_activity_idx');
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->index('followed_id', 'follows_followed_id_idx');
        });

        Schema::table('forums', function (Blueprint $table) {
            $table->index('category_id', 'forums_category_id_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_id', 'notifiable_type', 'read_at'], 'notifications_recipient_unread_idx');
        });
    }

    public function down(): void
    {
        Schema::table('posts', fn (Blueprint $t) => $t->dropIndex('posts_user_id_idx'));
        Schema::table('threads', function (Blueprint $t) {
            $t->dropIndex('threads_user_id_idx');
            $t->dropIndex('threads_forum_pinned_activity_idx');
        });
        Schema::table('follows', fn (Blueprint $t) => $t->dropIndex('follows_followed_id_idx'));
        Schema::table('forums', fn (Blueprint $t) => $t->dropIndex('forums_category_id_idx'));
        Schema::table('notifications', fn (Blueprint $t) => $t->dropIndex('notifications_recipient_unread_idx'));
    }
};
