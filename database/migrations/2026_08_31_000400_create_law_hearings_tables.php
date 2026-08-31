<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('law_hearings', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('law_unit_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->char('law_case_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('title', 180);
            $table->string('hearing_type', 64);
            $table->dateTime('scheduled_at');
            $table->dateTime('ended_at')->nullable();
            $table->enum('modality', ['presencial', 'virtual', 'hibrida']);
            $table->string('location', 180)->nullable();
            $table->string('room', 80)->nullable();
            $table->enum('status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'rescheduled', 'not_held'])->default('scheduled');
            $table->text('cancellation_reason')->nullable();
            $table->text('rescheduling_reason')->nullable();
            $table->char('responsible_user_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->boolean('external_tracking_enabled')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->char('created_by', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('updated_by', 30)->charset('ascii')->collation('ascii_bin');
            $table->timestamps();
            $table->index(['company_id', 'law_unit_id', 'scheduled_at']);
            $table->index(['company_id', 'status', 'scheduled_at']);
        });

        Schema::create('law_hearing_participants', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('law_hearing_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('law_contact_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->string('role', 40);
            $table->string('name_snapshot', 180);
            $table->boolean('visible_externally')->default(false);
            $table->timestamps();
            $table->index(['company_id', 'law_hearing_id']);
        });

        Schema::create('law_hearing_status_history', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('law_hearing_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->string('previous_status', 32)->nullable();
            $table->string('new_status', 32);
            $table->text('reason')->nullable();
            $table->string('origin', 40)->default('internal');
            $table->char('created_by', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'law_hearing_id', 'created_at']);
        });

        Schema::create('law_hearing_alerts', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('law_hearing_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->string('type', 48);
            $table->enum('status', ['open', 'acknowledged', 'resolved', 'dismissed'])->default('open');
            $table->dateTime('triggered_at');
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status', 'triggered_at']);
        });

        Schema::create('law_hearing_external_accesses', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('law_hearing_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('law_contact_id', 30)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->char('token_hash', 64)->charset('ascii')->collation('ascii_bin')->unique();
            $table->dateTime('expires_at');
            $table->dateTime('revoked_at')->nullable();
            $table->unsignedInteger('access_count')->default(0);
            $table->timestamps();
            $table->index(['company_id', 'law_hearing_id', 'expires_at']);
        });

        Schema::create('law_hearing_external_events', function (Blueprint $table): void {
            $table->char('id', 30)->charset('ascii')->collation('ascii_bin')->primary();
            $table->char('company_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->char('law_hearing_id', 30)->charset('ascii')->collation('ascii_bin');
            $table->string('event_type', 48);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'law_hearing_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('law_hearing_external_events');
        Schema::dropIfExists('law_hearing_external_accesses');
        Schema::dropIfExists('law_hearing_alerts');
        Schema::dropIfExists('law_hearing_status_history');
        Schema::dropIfExists('law_hearing_participants');
        Schema::dropIfExists('law_hearings');
    }
};
