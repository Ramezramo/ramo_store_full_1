<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public $withinTransaction = false;

    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE INDEX CONCURRENTLY products_data_search_text_gin
            ON products_data USING GIN (
                to_tsvector('simple', COALESCE(NULLIF(BTRIM(search_text), ''), name, ''))
            )
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP INDEX CONCURRENTLY IF EXISTS products_data_search_text_gin');
    }
};
