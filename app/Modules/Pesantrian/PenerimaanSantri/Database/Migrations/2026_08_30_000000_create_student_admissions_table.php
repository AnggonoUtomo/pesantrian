<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_admissions', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('registration_no', 40)->unique();
            $table->string('registration_period', 80)->nullable()->index();
            $table->string('candidate_name', 180);
            $table->string('candidate_gender', 20)->nullable()->index();
            $table->string('candidate_birth_place', 120)->nullable();
            $table->date('candidate_birth_date')->nullable();
            $table->string('previous_school', 180)->nullable();
            $table->foreignUlid('target_unit_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->string('guardian_name', 180);
            $table->string('guardian_phone', 40)->nullable();
            $table->string('guardian_relation', 40)->nullable();
            $table->boolean('registration_fee_required')->default(false)->index();
            $table->decimal('registration_fee_amount', 12, 2)->nullable();
            $table->string('registration_fee_status', 20)->default('not_required')->index();
            $table->json('document_checklist')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->foreignUlid('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['target_unit_id', 'status']);
            $table->index(['registration_period', 'status']);
            $table->index(['registration_fee_required', 'registration_fee_status']);
            $table->index(['registered_at', 'decided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_admissions');
    }
};
