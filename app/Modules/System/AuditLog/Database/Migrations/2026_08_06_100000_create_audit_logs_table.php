<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('event_id')->unique();
            $table->foreignUlid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120)->index();
            $table->string('subject_type', 120);
            $table->ulid('subject_id')->nullable()->index();
            $table->string('module', 120)->index();
            $table->ulid('project_id')->nullable()->index();
            $table->ulid('tenant_id')->nullable()->index();
            $table->ulid('correlation_id')->index();
            $table->text('reason')->nullable();
            $table->json('metadata');
            $table->timestampTz('created_at')->index();

            $table->index(['module', 'created_at']);
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
