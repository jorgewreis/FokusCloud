<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_admins', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password');
            $table->enum('status', ['ativo', 'suspenso'])->default('ativo');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('platform_login_challenges', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('platform_admin_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->string('code_hash', 64);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->foreign('platform_admin_id')->references('id')->on('platform_admins')->restrictOnDelete();
            $table->index(['platform_admin_id', 'expires_at']);
        });

        Schema::create('platform_audit_events', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('platform_admin_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('action', 100);
            $table->string('entity_type', 80)->nullable();
            $table->char('entity_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->text('reason')->nullable();
            $table->string('support_ticket', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            $table->foreign('platform_admin_id')->references('id')->on('platform_admins')->nullOnDelete();
            $table->index(['company_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('code', 64)->unique();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 12, 2);
            $table->char('product_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->json('module_codes')->nullable();
            $table->unsignedInteger('redemption_limit')->nullable();
            $table->unsignedInteger('redemption_limit_per_company')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->enum('status', ['ativa', 'suspensa', 'encerrada'])->default('ativa');
            $table->char('created_by_platform_admin_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('created_by_platform_admin_id')->references('id')->on('platform_admins')->restrictOnDelete();
        });

        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('voucher_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('subscription_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->decimal('discount_amount', 12, 2);
            $table->timestamp('created_at');
            $table->foreign('voucher_id')->references('id')->on('vouchers')->restrictOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->restrictOnDelete();
            $table->index(['voucher_id', 'company_id']);
        });

        Schema::create('subscription_changes', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('subscription_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->enum('type', ['upgrade', 'downgrade', 'cancelamento', 'suspensao', 'reativacao']);
            $table->enum('status', ['agendada', 'pendente_pagamento', 'aplicada', 'cancelada'])->default('agendada');
            $table->timestamp('effective_at');
            $table->decimal('proration_amount', 12, 2)->default(0);
            $table->json('items_snapshot')->nullable();
            $table->string('reason', 1000)->nullable();
            $table->char('requested_by_user_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->char('requested_by_platform_admin_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->restrictOnDelete();
            $table->index(['subscription_id', 'status', 'effective_at']);
        });

        Schema::create('usage_snapshots', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('product_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->date('reported_on');
            $table->unsignedInteger('active_users')->default(0);
            $table->unsignedInteger('licensed_seats')->default(0);
            $table->unsignedInteger('used_seats')->default(0);
            $table->unsignedBigInteger('key_records')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->unique(['company_id', 'product_id', 'reported_on']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'annual'])->nullable()->after('status');
            $table->timestamp('current_period_starts_at')->nullable()->after('billing_cycle');
            $table->timestamp('current_period_ends_at')->nullable()->after('current_period_starts_at');
            $table->string('provider_subscription_id', 128)->nullable()->unique()->after('current_period_ends_at');
            $table->timestamp('cancel_at')->nullable()->after('provider_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'current_period_starts_at', 'current_period_ends_at', 'provider_subscription_id', 'cancel_at']);
        });
        Schema::dropIfExists('usage_snapshots');
        Schema::dropIfExists('subscription_changes');
        Schema::dropIfExists('voucher_redemptions');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('platform_audit_events');
        Schema::dropIfExists('platform_login_challenges');
        Schema::dropIfExists('platform_admins');
    }
};
