<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('status', 20)->default('active')->after('password')->index();
            $table->timestamp('deleted_at')->nullable()->after('updated_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_status_index');
            $table->dropIndex('users_deleted_at_index');
            $table->dropColumn(['status', 'deleted_at']);
        });
    }
};
