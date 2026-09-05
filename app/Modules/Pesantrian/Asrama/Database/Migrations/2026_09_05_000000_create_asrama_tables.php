<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitories', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('unit_id')->constrained('organization_units')->cascadeOnDelete();
            $table->string('code', 40)->unique();
            $table->string('name', 180);
            $table->string('gender_policy', 20)->default('unspecified')->index();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('archived_at')->nullable();
            $table->foreignUlid('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['unit_id', 'status']);
            $table->index(['archived_at', 'status']);
        });

        Schema::create('dormitory_rooms', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('dormitory_id')->constrained('dormitories')->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name', 120);
            $table->unsignedSmallInteger('capacity');
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('archived_at')->nullable();
            $table->foreignUlid('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['dormitory_id', 'code']);
            $table->index(['dormitory_id', 'status']);
            $table->index(['archived_at', 'status']);
        });

        Schema::create('student_room_placements', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUlid('dormitory_room_id')->constrained('dormitory_rooms')->cascadeOnDelete();
            $table->string('student_no', 40);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('reason')->nullable();
            $table->string('active_student_key', 26)->nullable()->unique();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUlid('ended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['dormitory_room_id', 'status'], 'srp_room_status_idx');
            $table->index(['student_id', 'started_at', 'ended_at'], 'srp_student_period_idx');
        });

        Schema::create('dormitory_supervisor_assignments', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignUlid('dormitory_id')->nullable()->constrained('dormitories')->cascadeOnDelete();
            $table->foreignUlid('dormitory_room_id')->nullable()->constrained('dormitory_rooms')->cascadeOnDelete();
            $table->string('employee_name', 180);
            $table->string('role', 40)->default('musyrif')->index();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'started_at', 'ended_at'], 'dsa_employee_period_idx');
            $table->index(['dormitory_id', 'status'], 'dsa_dormitory_status_idx');
            $table->index(['dormitory_room_id', 'status'], 'dsa_room_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_supervisor_assignments');
        Schema::dropIfExists('student_room_placements');
        Schema::dropIfExists('dormitory_rooms');
        Schema::dropIfExists('dormitories');
    }
};
