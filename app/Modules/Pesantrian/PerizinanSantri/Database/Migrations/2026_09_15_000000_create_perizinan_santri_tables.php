<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_permits', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('permit_no', 40);
            $table->foreignUlid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('student_no', 40);
            $table->string('student_name', 180);
            $table->string('permit_type', 30)->index('sp_permit_type_idx');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('destination', 180)->nullable();
            $table->text('reason');
            $table->string('guardian_name', 180)->nullable();
            $table->string('guardian_phone', 40)->nullable();
            $table->string('guardian_relation', 40)->nullable();
            $table->string('status', 20)->default('draft')->index('sp_status_idx');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignUlid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignUlid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->foreignUlid('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->foreignUlid('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('return_note')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->foreignUlid('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('permit_no', 'sp_permit_no_unique');
            $table->index(['student_id', 'status', 'starts_at', 'ends_at'], 'sp_student_status_range_idx');
            $table->index(['starts_at', 'ends_at', 'status'], 'sp_range_status_idx');
            $table->index(['permit_type', 'status', 'starts_at'], 'sp_type_status_start_idx');
            $table->index(['returned_at', 'ends_at'], 'sp_return_due_idx');
        });

        Schema::create('student_permit_revisions', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('permit_id')->constrained('student_permits')->cascadeOnDelete();
            $table->text('reason');
            $table->foreignUlid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->json('summary')->nullable();
            $table->timestamps();

            $table->index(['permit_id', 'changed_at'], 'spr_permit_changed_idx');
            $table->index(['changed_by', 'changed_at'], 'spr_actor_changed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_permit_revisions');
        Schema::dropIfExists('student_permits');
    }
};
