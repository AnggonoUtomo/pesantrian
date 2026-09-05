<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendance_sessions', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->date('attendance_date');
            $table->string('context_type', 30)->index();
            $table->ulid('context_id')->nullable();
            $table->string('context_name', 180);
            $table->string('session_code', 60);
            $table->string('session_name', 120);
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignUlid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignUlid('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['attendance_date', 'context_type', 'context_id', 'session_code'],
                'sas_date_context_session_unique',
            );
            $table->index(['attendance_date', 'context_type', 'status'], 'sas_date_context_status_idx');
            $table->index(['context_type', 'context_id'], 'sas_context_idx');
        });

        Schema::create('student_attendance_entries', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('session_id')->constrained('student_attendance_sessions')->cascadeOnDelete();
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('student_no', 40);
            $table->string('student_name', 180);
            $table->string('status', 20)->default('present')->index();
            $table->unsignedSmallInteger('minutes_late')->nullable();
            $table->text('note')->nullable();
            $table->string('source_reference_type', 80)->nullable();
            $table->ulid('source_reference_id')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'student_id'], 'sae_session_student_unique');
            $table->index(['session_id', 'status'], 'sae_session_status_idx');
            $table->index(['student_id', 'status'], 'sae_student_status_idx');
            $table->index(['source_reference_type', 'source_reference_id'], 'sae_source_ref_idx');
        });

        Schema::create('student_attendance_revisions', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('session_id')->constrained('student_attendance_sessions')->cascadeOnDelete();
            $table->text('reason');
            $table->foreignUlid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->json('summary')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'changed_at'], 'sar_session_changed_idx');
            $table->index(['changed_by', 'changed_at'], 'sar_actor_changed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendance_revisions');
        Schema::dropIfExists('student_attendance_entries');
        Schema::dropIfExists('student_attendance_sessions');
    }
};
