<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->enum('document_type', ['cpf', 'cnpj']);
            $table->char('document_number', 14)->charset('ascii');
            $table->string('legal_name', 255);
            $table->enum('status', ['pendente', 'ativa', 'suspensa', 'encerrando', 'encerrada'])->default('pendente');
            $table->unsignedInteger('version')->default(1);
            $table->char('created_by', 30)->charset('ascii')->nullable();
            $table->char('updated_by', 30)->charset('ascii')->nullable();
            $table->char('deleted_by', 30)->charset('ascii')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->unique(['document_type', 'document_number']);
        });

        Schema::create('company_memberships', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('user_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('role_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->enum('status', ['pendente', 'ativo', 'suspenso', 'removido'])->default('pendente');
            $table->char('active_admin_company_id', 30)->charset('ascii')->nullable()->unique();
            $table->unsignedInteger('version')->default(1);
            $table->char('created_by', 30)->charset('ascii');
            $table->char('updated_by', 30)->charset('ascii');
            $table->char('deleted_by', 30)->charset('ascii')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->unique(['company_id', 'user_id']);
            $table->unique(['company_id', 'id']);
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->restrictOnDelete();
        });

        Schema::create('company_invitations', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('membership_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('created_by', 30)->charset('ascii')->collation('ascii_bin');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->foreign(['company_id', 'membership_id'])->references(['company_id', 'id'])->on('company_memberships')->restrictOnDelete();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('product_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->enum('status', ['pendente', 'ativa', 'suspensa', 'encerrada'])->default('pendente');
            $table->char('open_company_product', 61)->charset('ascii')->nullable()->unique();
            $table->unsignedInteger('version')->default(1);
            $table->char('created_by', 30)->charset('ascii');
            $table->char('updated_by', 30)->charset('ascii');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('subscription_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('module_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('name_snapshot', 120);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price_snapshot', 12, 2);
            $table->json('conditions_snapshot')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'id']);
            $table->foreign(['company_id', 'subscription_id'])->references(['company_id', 'id'])->on('subscriptions')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('company_invitations');
        Schema::dropIfExists('company_memberships');
        Schema::dropIfExists('companies');
    }
};
