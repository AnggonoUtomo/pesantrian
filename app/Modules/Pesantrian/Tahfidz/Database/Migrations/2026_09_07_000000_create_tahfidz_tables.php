<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahfidz_programs', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code', 40);
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index('tp_status_idx');
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->foreignUlid('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('code', 'tp_code_unique');
            $table->index(['archived_at', 'status'], 'tp_archived_status_idx');
        });

        Schema::create('tahfidz_targets', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('program_id')->constrained('tahfidz_programs')->cascadeOnDelete();
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('student_no', 40);
            $table->string('student_name', 180);
            $table->foreignUlid('academic_period_id')->nullable()->constrained('academic_terms')->nullOnDelete();
            $table->string('period_label', 180)->nullable();
            $table->unsignedTinyInteger('target_juz')->nullable();
            $table->string('target_surah', 120)->nullable();
            $table->unsignedSmallInteger('target_ayah_from')->nullable();
            $table->unsignedSmallInteger('target_ayah_to')->nullable();
            $table->text('target_note')->nullable();
            $table->string('status', 20)->default('active')->index('tt_status_idx');
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['program_id', 'status'], 'tt_program_status_idx');
            $table->index(['student_id', 'status'], 'tt_student_status_idx');
            $table->index(['academic_period_id', 'status'], 'tt_period_status_idx');
        });

        Schema::create('tahfidz_submissions', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('program_id')->constrained('tahfidz_programs')->cascadeOnDelete();
            $table->foreignUlid('target_id')->nullable()->constrained('tahfidz_targets')->nullOnDelete();
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('student_no', 40);
            $table->string('student_name', 180);
            $table->foreignUlid('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('supervisor_name', 180)->nullable();
            $table->date('submission_date');
            $table->string('type', 30)->default('new_memorization')->index('ts_type_idx');
            $table->unsignedTinyInteger('juz')->nullable();
            $table->string('surah', 120)->nullable();
            $table->unsignedSmallInteger('ayah_from')->nullable();
            $table->unsignedSmallInteger('ayah_to')->nullable();
            $table->string('status', 20)->default('draft')->index('ts_status_idx');
            $table->text('quality_note')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignUlid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignUlid('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['program_id', 'status', 'submission_date'], 'ts_program_status_date_idx');
            $table->index(['student_id', 'status', 'submission_date'], 'ts_student_status_date_idx');
            $table->index(['supervisor_id', 'submission_date'], 'ts_supervisor_date_idx');
            $table->index(['target_id', 'status'], 'ts_target_status_idx');
        });

        Schema::create('tahfidz_submission_revisions', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('submission_id')->constrained('tahfidz_submissions')->cascadeOnDelete();
            $table->text('reason');
            $table->foreignUlid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->json('summary')->nullable();
            $table->timestamps();

            $table->index(['submission_id', 'changed_at'], 'tsr_submission_changed_idx');
            $table->index(['changed_by', 'changed_at'], 'tsr_actor_changed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahfidz_submission_revisions');
        Schema::dropIfExists('tahfidz_submissions');
        Schema::dropIfExists('tahfidz_targets');
        Schema::dropIfExists('tahfidz_programs');
    }
};
