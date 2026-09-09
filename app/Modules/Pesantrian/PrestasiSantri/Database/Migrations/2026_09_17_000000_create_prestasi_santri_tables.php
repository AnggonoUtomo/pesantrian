<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_achievement_categories', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code', 40);
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index('sac_status_idx');
            $table->timestamp('archived_at')->nullable();
            $table->foreignUlid('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('archive_reason')->nullable();
            $table->timestamps();

            $table->unique('code', 'sac_code_unique');
            $table->index(['status', 'name'], 'sac_status_name_idx');
            $table->index(['archived_at', 'status'], 'sac_archived_status_idx');
        });

        Schema::create('student_achievements', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('achievement_no', 40);
            $table->foreignUlid('category_id')->constrained('student_achievement_categories')->restrictOnDelete();
            $table->string('category_name', 120);
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('student_no', 40);
            $table->string('student_name', 180);
            $table->foreignUlid('academic_period_id')->nullable()->constrained('academic_terms')->nullOnDelete();
            $table->string('academic_period_label', 180)->nullable();
            $table->foreignUlid('mentor_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('mentor_name', 180)->nullable();
            $table->string('title', 180);
            $table->string('achievement_type', 40)->default('competition')->index('sa_type_idx');
            $table->string('level', 40)->index('sa_level_idx');
            $table->string('result', 120);
            $table->string('organizer', 180)->nullable();
            $table->string('event_name', 180)->nullable();
            $table->string('event_location', 180)->nullable();
            $table->date('achieved_on')->nullable();
            $table->date('period_started_on')->nullable();
            $table->date('period_ended_on')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft')->index('sa_status_idx');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignUlid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignUlid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_note')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->foreignUlid('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('achievement_no', 'sa_achievement_no_unique');
            $table->index(['student_id', 'status', 'achieved_on'], 'sa_student_status_date_idx');
            $table->index(['category_id', 'status', 'achieved_on'], 'sa_category_status_date_idx');
            $table->index(['academic_period_id', 'status'], 'sa_period_status_idx');
            $table->index(['mentor_employee_id', 'status'], 'sa_mentor_status_idx');
            $table->index(['level', 'status', 'achieved_on'], 'sa_level_status_date_idx');
        });

        Schema::create('student_achievement_revisions', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('achievement_id')->constrained('student_achievements')->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->text('reason');
            $table->foreignUlid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->json('summary')->nullable();
            $table->timestamps();

            $table->index(['achievement_id', 'changed_at'], 'psar_achievement_changed_idx');
            $table->index(['changed_by', 'changed_at'], 'psar_actor_changed_idx');
            $table->index(['to_status', 'changed_at'], 'psar_status_changed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_achievement_revisions');
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('student_achievement_categories');
    }
};
