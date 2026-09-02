<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table): void {
            $table->enum('discount_type', ['percentage', 'fixed', 'trial_free', 'commercial_credit'])->change();
        });

        Schema::table('voucher_redemptions', function (Blueprint $table): void {
            $table->json('snapshot')->nullable()->after('benefit_ends_at');
            $table->index(['company_id', 'created_at'], 'voucher_redemptions_company_created_idx');
        });

        Schema::create('voucher_redemption_reservations', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('voucher_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('subscription_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('request_key', 128)->unique();
            $table->enum('status', ['pending', 'confirmed', 'released', 'expired'])->default('pending');
            $table->json('snapshot');
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->foreign('voucher_id')->references('id')->on('vouchers')->restrictOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->restrictOnDelete();
            $table->index(['voucher_id', 'status', 'expires_at'], 'voucher_reservations_voucher_status_idx');
            $table->index(['subscription_id', 'status'], 'voucher_reservations_subscription_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_redemption_reservations');

        Schema::table('voucher_redemptions', function (Blueprint $table): void {
            $table->dropIndex('voucher_redemptions_company_created_idx');
            $table->dropColumn('snapshot');
        });

        Schema::table('vouchers', function (Blueprint $table): void {
            $table->enum('discount_type', ['percentage', 'fixed', 'trial_free'])->change();
        });
    }
};
