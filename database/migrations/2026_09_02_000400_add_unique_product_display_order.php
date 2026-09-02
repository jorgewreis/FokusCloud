<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function ($table): void {
            $table->unique('display_order', 'products_display_order_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function ($table): void {
            $table->dropUnique('products_display_order_unique');
        });
    }
};
