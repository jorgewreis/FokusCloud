<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('voucher_redemptions', function (Blueprint $table) {
            $table->timestamp('benefit_starts_at')->nullable()->after('discount_amount');
            $table->timestamp('benefit_ends_at')->nullable()->after('benefit_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('voucher_redemptions', function (Blueprint $table) {
            $table->dropColumn(['benefit_starts_at', 'benefit_ends_at']);
        });
    }
};
