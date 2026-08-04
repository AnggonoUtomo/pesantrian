<?php

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $permission = Permission::create([
        'name' => 'system.dashboard.view',
        'guard_name' => 'web',
    ]);
    $user->givePermissionTo($permission);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
