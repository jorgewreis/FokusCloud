<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('technical_description', 2000)->nullable()->after('name');
            $table->text('commercial_content')->nullable()->after('technical_description');
            $table->enum('status', ['ativo', 'inativo', 'pausado', 'arquivado'])->default('ativo')->after('commercial_content');
            $table->enum('publication_state', ['rascunho', 'publicado', 'pausado', 'arquivado'])->default('rascunho')->after('status');
            $table->unsignedInteger('display_order')->default(0)->after('publication_state');
            $table->boolean('featured')->default(false)->after('display_order');
            $table->unsignedInteger('published_catalog_version')->default(0)->after('featured');
            $table->index(['status', 'publication_state'], 'products_status_publication_idx');
        });

        Schema::table('modules', function (Blueprint $table): void {
            $table->string('technical_description', 2000)->nullable()->after('name');
            $table->text('commercial_content')->nullable()->after('technical_description');
            $table->enum('publication_state', ['rascunho', 'publicado', 'pausado', 'arquivado'])->default('rascunho')->after('status');
            $table->unsignedInteger('display_order')->default(0)->after('publication_state');
            $table->boolean('featured')->default(false)->after('display_order');
            $table->string('capacity_unit', 64)->nullable()->after('featured');
            $table->unsignedInteger('default_capacity')->nullable()->after('capacity_unit');
            $table->json('capacity_options')->nullable()->after('default_capacity');
            $table->boolean('available_standalone')->default(true)->after('capacity_options');
            $table->index(['product_id', 'status', 'publication_state'], 'modules_product_status_publication_idx');
        });

        Schema::table('plans', function (Blueprint $table): void {
            $table->string('technical_description', 2000)->nullable()->after('name');
            $table->text('commercial_content')->nullable()->after('technical_description');
        });

        Schema::create('catalog_publications', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('product_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->char('published_by_platform_admin_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('reason', 1000);
            $table->timestamp('published_at');
            $table->timestamps();
            $table->unique(['product_id', 'version'], 'catalog_publications_product_version_unique');
            $table->index(['product_id', 'published_at'], 'catalog_publications_product_published_idx');
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('published_by_platform_admin_id')->references('id')->on('platform_admins')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_publications');

        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn(['technical_description', 'commercial_content']);
        });

        Schema::table('modules', function (Blueprint $table): void {
            $table->dropIndex('modules_product_status_publication_idx');
            $table->dropColumn([
                'technical_description',
                'commercial_content',
                'publication_state',
                'display_order',
                'featured',
                'capacity_unit',
                'default_capacity',
                'capacity_options',
                'available_standalone',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_status_publication_idx');
            $table->dropColumn([
                'technical_description',
                'commercial_content',
                'status',
                'publication_state',
                'display_order',
                'featured',
                'published_catalog_version',
            ]);
        });
    }
};
