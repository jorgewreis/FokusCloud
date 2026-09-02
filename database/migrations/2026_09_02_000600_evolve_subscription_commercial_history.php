<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->enum('status', [
                'pendente',
                'aguardando_pagamento',
                'ativa',
                'inadimplente',
                'suspensa',
                'cancelamento_agendado',
                'encerrada',
            ])->change();
            $table->json('commercial_snapshot')->nullable();
        });

        DB::table('subscriptions')->where('status', 'pendente')->update(['status' => 'aguardando_pagamento']);

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->enum('status', [
                'aguardando_pagamento',
                'ativa',
                'inadimplente',
                'suspensa',
                'cancelamento_agendado',
                'encerrada',
            ])->default('aguardando_pagamento')->change();
        });

        Schema::table('subscription_changes', function (Blueprint $table): void {
            $table->enum('type', ['upgrade', 'downgrade', 'cancelamento', 'suspensao', 'reativacao', 'override'])->change();
            $table->enum('status', [
                'agendada',
                'pendente_pagamento',
                'solicitada',
                'aguardando_pagamento',
                'aplicada',
                'cancelada',
                'falhou',
            ])->change();
            $table->json('before_snapshot')->nullable();
            $table->json('after_snapshot')->nullable();
            $table->char('approved_by_platform_admin_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->unsignedInteger('version')->default(1);
        });

        DB::table('subscription_changes')->where('status', 'pendente_pagamento')->update(['status' => 'aguardando_pagamento']);

        Schema::table('subscription_changes', function (Blueprint $table): void {
            $table->enum('status', [
                'solicitada',
                'aguardando_pagamento',
                'agendada',
                'aplicada',
                'cancelada',
                'falhou',
            ])->default('agendada')->change();
            $table->foreign('approved_by_platform_admin_id')->references('id')->on('platform_admins')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->enum('status', [
                'pendente',
                'aguardando_pagamento',
                'aprovado',
                'recusado',
                'cancelado',
                'estornado',
                'em_disputa',
            ])->change();
            $table->string('provider_subscription_id', 128)->nullable();
            $table->timestamp('billing_period_starts_at')->nullable();
            $table->timestamp('billing_period_ends_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('provider_payload_sanitized')->nullable();
        });

        DB::table('payments')->where('status', 'pendente')->update(['status' => 'aguardando_pagamento']);

        Schema::table('payments', function (Blueprint $table): void {
            $table->enum('status', [
                'aguardando_pagamento',
                'aprovado',
                'recusado',
                'cancelado',
                'estornado',
                'em_disputa',
            ])->default('aguardando_pagamento')->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->enum('status', [
                'aguardando_pagamento',
                'aprovado',
                'recusado',
                'cancelado',
                'estornado',
                'em_disputa',
                'pendente',
            ])->change();
        });
        DB::table('payments')->where('status', 'aguardando_pagamento')->update(['status' => 'pendente']);
        Schema::table('payments', function (Blueprint $table): void {
            $table->enum('status', ['pendente', 'aprovado', 'recusado', 'cancelado'])->default('pendente')->change();
            $table->dropColumn(['provider_subscription_id', 'billing_period_starts_at', 'billing_period_ends_at', 'paid_at', 'provider_payload_sanitized']);
        });

        Schema::table('subscription_changes', function (Blueprint $table): void {
            $table->enum('type', ['upgrade', 'downgrade', 'cancelamento', 'suspensao', 'reativacao'])->change();
            $table->dropForeign(['approved_by_platform_admin_id']);
            $table->enum('status', [
                'agendada',
                'pendente_pagamento',
                'solicitada',
                'aguardando_pagamento',
                'aplicada',
                'cancelada',
                'falhou',
            ])->change();
        });
        DB::table('subscription_changes')->where('status', 'aguardando_pagamento')->update(['status' => 'pendente_pagamento']);
        Schema::table('subscription_changes', function (Blueprint $table): void {
            $table->enum('status', ['agendada', 'pendente_pagamento', 'aplicada', 'cancelada'])->default('agendada')->change();
            $table->dropColumn(['before_snapshot', 'after_snapshot', 'approved_by_platform_admin_id', 'version']);
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->enum('status', [
                'aguardando_pagamento',
                'ativa',
                'inadimplente',
                'suspensa',
                'cancelamento_agendado',
                'encerrada',
                'pendente',
            ])->change();
        });
        DB::table('subscriptions')->where('status', 'aguardando_pagamento')->update(['status' => 'pendente']);
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->enum('status', ['pendente', 'ativa', 'suspensa', 'encerrada'])->default('pendente')->change();
            $table->dropColumn('commercial_snapshot');
        });
    }
};
