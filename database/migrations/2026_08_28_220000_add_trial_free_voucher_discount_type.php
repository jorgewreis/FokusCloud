<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'fixed', 'trial_free'])->change();
        });
    }

    public function down(): void
    {
        DB::table('vouchers')->where('discount_type', 'trial_free')->update([
            'discount_type' => 'percentage',
            'discount_value' => 100,
        ]);

        Schema::table('vouchers', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'fixed'])->change();
        });
    }
};
