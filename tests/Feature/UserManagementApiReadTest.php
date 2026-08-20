<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed(SystemSettingSeeder::class);
});

it('mengembalikan list user dengan filter pagination sort dan resource snake case', function (): void {
    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $role = Role::create(['name' => 'OperatorApi', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    $target = User::factory()->create([
        'name' => 'Alpha Operator',
        'email' => 'alpha-operator@example.test',
        'status' => UserStatus::SUSPENDED,
        'last_login_at' => now()->subHour(),
    ]);
    $target->assignRole($role);

    $query = http_build_query([
        'search' => 'Alpha',
        'filter' => [
            'status' => 'suspended',
            'role' => 'OperatorApi',
            'archive' => 'active',
        ],
        'page' => 1,
        'per_page' => 10,
        'sort' => 'name',
    ]);

    $response = $this->actingAs($actor)
        ->getJson(route('api.v1.users.index').'?'.$query)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar user berhasil dibaca.')
        ->assertJsonPath('data.0.id', $target->id)
        ->assertJsonPath('data.0.name', 'Alpha Operator')
        ->assertJsonPath('data.0.status', 'suspended')
        ->assertJsonPath('data.0.is_protected', false)
        ->assertJsonPath('data.0.roles.0', 'OperatorApi')
        ->assertJsonPath('data.0.email_verified', true)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [[
                'id',
                'name',
                'email',
                'status',
                'is_protected',
                'deleted_at',
                'roles',
                'avatar_url',
                'email_verified',
                'last_login_at',
            ]],
            'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
        ])
        ->assertJsonMissingPath('data.0.password')
        ->assertJsonMissingPath('data.0.isProtected');

    expect($response->headers->get('X-Correlation-ID'))->toBeString();
});

it('mengembalikan detail user dan 404 canonical untuk target tidak ada', function (): void {
    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    $target = User::factory()->unverified()->create(['name' => 'Detail API']);

    $this->actingAs($actor)
        ->getJson(route('api.v1.users.show', $target->id))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Detail user berhasil dibaca.')
        ->assertJsonPath('data.id', $target->id)
        ->assertJsonPath('data.email_verified', false);

    $this->actingAs($actor)
        ->getJson(route('api.v1.users.show', (string) Str::ulid()))
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});

it('menolak guest actor tanpa permission dan query invalid dengan envelope canonical', function (): void {
    $this->getJson(route('api.v1.users.index'))
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHENTICATED');

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)
        ->getJson(route('api.v1.users.index'))
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $view = Permission::create(['name' => 'user.view', 'guard_name' => 'web']);
    $authorized = User::factory()->create();
    $authorized->givePermissionTo($view);

    $this->actingAs($authorized)
        ->getJson(route('api.v1.users.index').'?sort=password&per_page=101')
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['sort', 'per_page']]);
});
