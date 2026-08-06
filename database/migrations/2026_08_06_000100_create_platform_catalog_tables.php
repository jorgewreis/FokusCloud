<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('code', 32)->unique();
            $table->string('name', 80);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->string('code', 32)->unique();
            $table->string('name', 120);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('product_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->string('code', 64);
            $table->string('name', 120);
            $table->timestamps();
            $table->unique(['product_id', 'code']);
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('product_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->string('code', 64);
            $table->string('name', 120);
            $table->decimal('monthly_price', 12, 2);
            $table->timestamps();
            $table->unique(['product_id', 'code']);
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });

        Schema::create('legal_acceptances', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('user_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->enum('document_type', ['terms', 'privacy']);
            $table->string('document_version', 64);
            $table->timestamp('accepted_at');
            $table->timestamps();
            $table->unique(['user_id', 'document_type', 'document_version']);
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('security_tokens', function (Blueprint $table) {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('user_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->enum('purpose', ['email_verification', 'password_reset', 'password_creation', 'membership_acceptance', 'admin_transfer']);
            $table->string('token_hash', 64)->unique();
            $table->json('payload')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_tokens');
        Schema::dropIfExists('legal_acceptances');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('products');
        Schema::dropIfExists('roles');
    }
};
