<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('modules')->where('product_id', DB::table('products')->where('code', 'law')->value('id'))
            ->where('segment_code', 'judiciario')->update(['segment_code' => 'setor_publico']);
    }

    public function down(): void
    {
        // A normalizacao nao deve reintroduzir a taxonomia ambigua anterior.
    }
};
