<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('student_no', 40)->unique();
            $table->ulid('admission_id')->nullable()->unique();
            $table->string('registration_no', 40)->nullable()->index();
            $table->string('full_name', 180);
            $table->string('preferred_name', 120)->nullable();
            $table->string('gender', 20)->nullable()->index();
            $table->string('birth_place', 120)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('previous_school', 180)->nullable();
            $table->foreignUlid('primary_unit_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->date('entry_date')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('status_reason')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->foreignUlid('status_changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->foreignUlid('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['primary_unit_id', 'status']);
            $table->index(['entry_date', 'status']);
            $table->index(['archived_at', 'status']);
        });

        Schema::create('student_guardians', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('guardian_name', 180);
            $table->string('guardian_phone', 40)->nullable();
            $table->string('guardian_relation', 40)->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->boolean('is_emergency_contact')->default(false)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'is_primary']);
            $table->index(['student_id', 'is_emergency_contact']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
        Schema::dropIfExists('students');
    }
};
