<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->enum('status', [
                'aguardando_pagamento',
                'ativa',
                'inadimplente',
                'suspensa',
                'cancelamento_agendado',
                'cancelada',
                'encerrada',
            ])->default('aguardando_pagamento')->change();
        });
    }

    public function down(): void
    {
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
    }
};
