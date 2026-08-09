<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\UserListFilter;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('menambahkan status dan deleted_at secara additive tanpa mengubah identity existing', function (): void {
    expect(Schema::hasColumns('users', ['id', 'status', 'deleted_at']))->toBeTrue()
        ->and(Schema::getColumnType('users', 'id'))->toBeIn(['string', 'varchar']);
});

it('repository memetakan user dan melakukan soft delete tanpa hard delete', function (): void {
    $user = User::factory()->create(['status' => UserStatus::ACTIVE->value]);
    $repository = app(UserRepository::class);

    $data = $repository->find($user->getKey());

    expect($data)->not->toBeNull()
        ->and($data?->status)->toBe(UserStatus::ACTIVE)
        ->and($data?->deletedAt)->toBeNull();

    $repository->softDelete($user->getKey());

    expect(User::withTrashed()->find($user->getKey()))->not->toBeNull()
        ->and($repository->find($user->getKey())?->deletedAt)->not->toBeNull();
});

it('repository mendukung filter pencarian sederhana dengan DTO typed', function (): void {
    User::factory()->create(['name' => 'Alice Example', 'email' => 'alice@example.test']);
    User::factory()->create(['name' => 'Bob Example', 'email' => 'bob@example.test']);
    $repository = app(UserRepository::class);

    $users = $repository->paginate(UserListFilter::from('alice'));

    expect($users->data)->toHaveCount(1)
        ->and($users->data[0]->email)->toBe('alice@example.test')
        ->and($users->total)->toBe(1);
});

it('migration additive mempertahankan data 2FA dan passkey saat upgrade', function (): void {
    $user = User::factory()->withTwoFactor()->create();
    DB::table('passkeys')->insert([
        'id' => (string) Str::ulid(),
        'user_id' => $user->getKey(),
        'name' => 'Laptop',
        'credential_id' => 'credential-existing-1',
        'credential' => json_encode(['publicKey' => 'existing']),
        'last_used_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Schema::table('users', function ($table): void {
        $table->dropIndex('users_status_index');
        $table->dropIndex('users_deleted_at_index');
        $table->dropColumn(['status', 'deleted_at']);
    });

    $migration = require base_path('app/Modules/System/UserManagement/Database/Migrations/2026_08_06_000000_add_lifecycle_columns_to_users_table.php');
    $migration->up();

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and(DB::table('passkeys')->where('user_id', $user->getKey())->count())->toBe(1)
        ->and(Schema::hasColumns('users', ['status', 'deleted_at']))->toBeTrue();
});
