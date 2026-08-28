<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_modules', function (Blueprint $table) {
            $table->char('plan_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('module_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->timestamps();
            $table->primary(['plan_id', 'module_id']);
            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
            $table->foreign('module_id')->references('id')->on('modules')->cascadeOnDelete();
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('name', 120)->nullable()->after('code');
            $table->char('plan_id', 30)->charset('ascii')->collation('ascii_bin')->nullable()->after('product_id');
            $table->decimal('base_amount', 12, 2)->nullable()->after('module_codes');
            $table->string('benefit_duration', 16)->nullable()->after('discount_value');
            $table->string('origin', 120)->nullable()->after('redemption_limit_per_company');
            $table->text('notes')->nullable()->after('origin');
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['name', 'plan_id', 'base_amount', 'benefit_duration', 'origin', 'notes']);
        });

        Schema::dropIfExists('plan_modules');
    }
};
