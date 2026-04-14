<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE posts ADD COLUMN search_vector tsvector GENERATED ALWAYS AS (to_tsvector('english', coalesce(body, ''))) STORED");
        DB::statement('CREATE INDEX posts_search_vector_idx ON posts USING GIN (search_vector)');

        DB::statement("ALTER TABLE threads ADD COLUMN search_vector tsvector GENERATED ALWAYS AS (to_tsvector('english', coalesce(title, ''))) STORED");
        DB::statement('CREATE INDEX threads_search_vector_idx ON threads USING GIN (search_vector)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS posts_search_vector_idx');
        DB::statement('ALTER TABLE posts DROP COLUMN IF EXISTS search_vector');
        DB::statement('DROP INDEX IF EXISTS threads_search_vector_idx');
        DB::statement('ALTER TABLE threads DROP COLUMN IF EXISTS search_vector');
    }
};
