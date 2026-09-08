<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_discipline_categories', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code', 40);
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('default_severity', 20)->default('minor')->index('sdc_default_severity_idx');
            $table->unsignedSmallInteger('default_points')->nullable();
            $table->string('status', 20)->default('active')->index('sdc_status_idx');
            $table->timestamps();

            $table->unique('code', 'sdc_code_unique');
            $table->index(['status', 'name'], 'sdc_status_name_idx');
        });

        Schema::create('student_discipline_cases', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('case_no', 40);
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('student_no', 40);
            $table->string('student_name', 180);
            $table->foreignUlid('unit_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->string('unit_name', 180)->nullable();
            $table->foreignUlid('category_id')->constrained('student_discipline_categories')->restrictOnDelete();
            $table->string('category_name', 120);
            $table->string('severity', 20)->index('sdc_case_severity_idx');
            $table->unsignedSmallInteger('points')->nullable();
            $table->timestamp('occurred_at')->index('sdc_case_occurred_idx');
            $table->string('location', 180)->nullable();
            $table->text('description');
            $table->foreignUlid('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('assigned_employee_name', 180)->nullable();
            $table->string('status', 30)->default('draft')->index('sdc_case_status_idx');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignUlid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->text('action_plan')->nullable();
            $table->timestamp('action_assigned_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignUlid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->foreignUlid('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('case_no', 'sdc_case_no_unique');
            $table->index(['student_id', 'status', 'occurred_at'], 'sdc_student_status_date_idx');
            $table->index(['category_id', 'status', 'occurred_at'], 'sdc_category_status_date_idx');
            $table->index(['severity', 'status', 'occurred_at'], 'sdc_severity_status_date_idx');
            $table->index(['assigned_employee_id', 'status'], 'sdc_assignee_status_idx');
            $table->index(['unit_id', 'occurred_at'], 'sdc_unit_date_idx');
        });

        Schema::create('student_discipline_revisions', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('case_id')->constrained('student_discipline_cases')->cascadeOnDelete();
            $table->text('reason');
            $table->foreignUlid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->json('summary')->nullable();
            $table->timestamps();

            $table->index(['case_id', 'changed_at'], 'sdr_case_changed_idx');
            $table->index(['changed_by', 'changed_at'], 'sdr_actor_changed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_discipline_revisions');
        Schema::dropIfExists('student_discipline_cases');
        Schema::dropIfExists('student_discipline_categories');
    }
};
