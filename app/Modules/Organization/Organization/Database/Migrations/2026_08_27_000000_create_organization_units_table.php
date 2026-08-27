<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_units', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('parent_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->string('code', 40)->unique();
            $table->string('name', 180);
            $table->string('type', 40);
            $table->string('status', 20)->default('active')->index();
            $table->string('location_name', 180)->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'status']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_units');
    }
};
