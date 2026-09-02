<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_curricula', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code', 40)->unique();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('class_levels', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('unit_id')->constrained('organization_units')->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name', 180);
            $table->unsignedSmallInteger('sequence');
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();

            $table->unique(['unit_id', 'code']);
            $table->unique(['unit_id', 'sequence']);
            $table->index(['unit_id', 'status']);
        });

        Schema::create('class_groups', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignUlid('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignUlid('unit_id')->constrained('organization_units')->cascadeOnDelete();
            $table->foreignUlid('curriculum_id')->nullable()->constrained('academic_curricula')->nullOnDelete();
            $table->foreignUlid('class_level_id')->constrained('class_levels')->cascadeOnDelete();
            $table->string('code', 60);
            $table->string('name', 180);
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('archived_at')->nullable();
            $table->foreignUlid('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['unit_id', 'academic_year_id', 'academic_term_id', 'code'], 'class_groups_period_unit_code_unique');
            $table->index(['academic_year_id', 'academic_term_id', 'status'], 'class_groups_period_status_index');
            $table->index(['unit_id', 'status']);
            $table->index(['class_level_id', 'status']);
            $table->index(['archived_at', 'status']);
        });

        Schema::create('class_group_students', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('class_group_id')->constrained('class_groups')->cascadeOnDelete();
            $table->foreignUlid('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('student_no', 40);
            $table->date('joined_on');
            $table->date('left_on')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('reason')->nullable();
            $table->string('active_period_student_key', 64)->nullable()->unique();
            $table->timestamps();

            $table->index(['class_group_id', 'status']);
            $table->index(['academic_term_id', 'student_id', 'status'], 'class_group_students_term_student_status_index');
            $table->index(['student_id', 'joined_on', 'left_on']);
        });

        Schema::create('class_group_homerooms', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('class_group_id')->constrained('class_groups')->cascadeOnDelete();
            $table->foreignUlid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('employee_name', 180);
            $table->date('assigned_on');
            $table->date('ended_on')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('reason')->nullable();
            $table->string('active_class_group_key', 26)->nullable()->unique();
            $table->timestamps();

            $table->index(['class_group_id', 'status']);
            $table->index(['employee_id', 'assigned_on', 'ended_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_group_homerooms');
        Schema::dropIfExists('class_group_students');
        Schema::dropIfExists('class_groups');
        Schema::dropIfExists('class_levels');
        Schema::dropIfExists('academic_curricula');
    }
};
