<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('subscription_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->string('provider', 32)->default('mercado_pago');
            $table->string('provider_payment_id', 128)->nullable()->unique();
            $table->enum('status', ['pendente', 'aprovado', 'recusado', 'cancelado'])->default('pendente');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('BRL');
            $table->json('provider_payload')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'id']);
            $table->foreign(['company_id', 'subscription_id'])->references(['company_id', 'id'])->on('subscriptions')->restrictOnDelete();
        });

        Schema::create('audit_events', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('actor_user_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('entity_type', 80);
            $table->char('entity_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->enum('operation', ['create', 'update', 'delete', 'restore', 'access']);
            $table->json('before_masked')->nullable();
            $table->json('after_masked')->nullable();
            $table->timestamp('expires_at');
            $table->dateTime('created_at');
            $table->index(['company_id', 'entity_type', 'entity_id']);
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
        });

        Schema::create('support_accesses', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('support_user_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->text('reason');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_accesses');
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('payments');
    }
};
