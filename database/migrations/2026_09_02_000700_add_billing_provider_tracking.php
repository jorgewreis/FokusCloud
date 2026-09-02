<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->string('provider_status', 64)->nullable();
            $table->json('provider_payload_sanitized')->nullable();
            $table->timestamp('provider_synced_at')->nullable();
            $table->timestamp('delinquent_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->index(['provider_status', 'provider_synced_at']);
            $table->index(['status', 'grace_ends_at']);
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->string('provider_authorized_payment_id', 128)->nullable()->unique();
            $table->index(['provider_subscription_id', 'created_at']);
        });

        Schema::create('billing_checkout_attempts', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('user_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('request_key', 128)->unique();
            $table->char('payment_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->char('subscription_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('provider_subscription_id', 128)->nullable();
            $table->string('status', 40)->default('started');
            $table->json('request_snapshot_sanitized')->nullable();
            $table->json('response_snapshot_sanitized')->nullable();
            $table->string('error_message', 1000)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();
            $table->index(['company_id', 'status', 'created_at']);
            $table->index(['provider_subscription_id', 'status']);
        });

        Schema::create('billing_provider_events', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('provider', 32)->default('mercado_pago');
            $table->string('event_key', 160)->unique();
            $table->string('notification_id', 128)->nullable();
            $table->string('request_id', 128)->nullable();
            $table->string('event_type', 100)->nullable();
            $table->string('action', 160)->nullable();
            $table->string('resource_id', 128)->nullable();
            $table->string('status', 40)->default('received');
            $table->json('payload_sanitized')->nullable();
            $table->string('error_message', 1000)->nullable();
            $table->timestamp('signature_verified_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['provider', 'resource_id', 'event_type']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_provider_events');
        Schema::dropIfExists('billing_checkout_attempts');

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex(['provider_subscription_id', 'created_at']);
            $table->dropUnique(['provider_authorized_payment_id']);
            $table->dropColumn('provider_authorized_payment_id');
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropIndex(['provider_status', 'provider_synced_at']);
            $table->dropIndex(['status', 'grace_ends_at']);
            $table->dropColumn(['provider_status', 'provider_payload_sanitized', 'provider_synced_at', 'delinquent_at', 'grace_ends_at']);
        });
    }
};
