<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->char('deleted_by', 30)->charset('ascii')->collation('ascii_bin')->nullable()->after('updated_by');
            $table->timestamp('deleted_at')->nullable()->index()->after('updated_at');
        });
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('conditions_snapshot');
            $table->char('created_by', 30)->charset('ascii')->collation('ascii_bin')->nullable()->after('version');
            $table->char('updated_by', 30)->charset('ascii')->collation('ascii_bin')->nullable()->after('created_by');
            $table->char('deleted_by', 30)->charset('ascii')->collation('ascii_bin')->nullable()->after('updated_by');
            $table->timestamp('deleted_at')->nullable()->index()->after('updated_at');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('provider_payload');
            $table->char('created_by', 30)->charset('ascii')->collation('ascii_bin')->nullable()->after('version');
            $table->char('updated_by', 30)->charset('ascii')->collation('ascii_bin')->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['version', 'created_by', 'updated_by']);
        });
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropColumn(['version', 'created_by', 'updated_by', 'deleted_by', 'deleted_at']);
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropColumn(['deleted_by', 'deleted_at']);
        });
    }
};
