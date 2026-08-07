<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE sessions MODIFY user_id CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sessions MODIFY user_id BIGINT UNSIGNED NULL');
    }
};
