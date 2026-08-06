<?php

declare(strict_types=1);

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use App\Modules\System\AccessControl\Application\DTO\AuthorizationDecision;
use App\Modules\System\UserManagement\Application\Actions\AssignUserRole;
use App\Modules\System\UserManagement\Application\Actions\ChangeUserStatus;
use App\Modules\System\UserManagement\Application\Actions\CreateUser;
use App\Modules\System\UserManagement\Application\Actions\SoftDeleteUser;
use App\Modules\System\UserManagement\Application\Actions\StartImpersonation;
use App\Modules\System\UserManagement\Application\Actions\UpdateUser;
use App\Modules\System\UserManagement\Application\Contracts\ImpersonationSession;
use App\Modules\System\UserManagement\Application\Contracts\UserRepository;
use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Application\DTO\ImpersonationRequestData;
use App\Modules\System\UserManagement\Application\DTO\UpdateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Application\Queries\GetUser;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;

it('mengembalikan typed DTO dari detail query', function (): void {
    $user = new UserData(
        id: '01JUSERMANAGEMENT000000000010',
        name: 'User Satu',
        email: 'user@example.test',
        status: UserStatus::ACTIVE,
        isProtected: false,
        deletedAt: null,
    );
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with($user->id)->andReturn($user);

    expect((new GetUser($repository))->execute($user->id))->toBe($user);
});

it('menolak DTO create dengan email tidak valid', function (): void {
    expect(fn (): CreateUserData => new CreateUserData('User Satu', 'invalid-email', 'password'))
        ->toThrow(InvalidArgumentException::class);
});

it('menolak action create tanpa permission', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.create')
        ->andReturn(AuthorizationDecision::deny('permission_missing'));
    $authorizer = new AuthorizeUserAction($authorization);

    $repository = Mockery::mock(UserRepository::class);

    expect(fn (): UserData => (new CreateUser($authorizer, $repository))
        ->execute($actor, new CreateUserData('User Satu', 'user@example.test', 'password')))
        ->toThrow(AuthorizationException::class);
    $repository->shouldNotReceive('create');
});

it('menggunakan public contract AccessControl untuk assignment role', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $target = Mockery::mock(Authenticatable::class);
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.update')
        ->andReturn(AuthorizationDecision::allow());
    $authorizer = new AuthorizeUserAction($authorization);
    $roles = Mockery::mock(RoleAssignmentCapability::class);
    $roles->expects('assignRole')->with($actor, $target, 'SecurityAdmin');

    (new AssignUserRole($authorizer, $roles))->execute($actor, $target, 'SecurityAdmin');
});

it('mengotorisasi update user melalui action dan repository contract', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.update')
        ->andReturn(AuthorizationDecision::allow());
    $repository = Mockery::mock(UserRepository::class);
    $expected = new UserData(
        id: '01JUSERMANAGEMENT000000000013',
        name: 'User Updated',
        email: 'updated@example.test',
        status: UserStatus::ACTIVE,
        isProtected: false,
        deletedAt: null,
    );
    $data = new UpdateUserData('User Updated', 'updated@example.test');
    $repository->expects('update')->with($expected->id, $data)->andReturn($expected);

    expect((new UpdateUser(new AuthorizeUserAction($authorization), $repository))
        ->execute($actor, $expected->id, $data))->toBe($expected);
});

it('mengubah status user biasa melalui action terotorisasi', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.status.manage')
        ->andReturn(AuthorizationDecision::allow());
    $user = new UserData(
        id: '01JUSERMANAGEMENT000000000014',
        name: 'User Status',
        email: 'status@example.test',
        status: UserStatus::ACTIVE,
        isProtected: false,
        deletedAt: null,
    );
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with($user->id)->andReturn($user);
    $repository->expects('changeStatus')
        ->with($user->id, UserStatus::SUSPENDED)
        ->andReturn($user);

    (new ChangeUserStatus(new AuthorizeUserAction($authorization), $repository))
        ->execute($actor, $user->id, UserStatus::SUSPENDED);
});

it('menolak soft delete pada user protected sebelum repository dipanggil', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.delete')
        ->andReturn(AuthorizationDecision::allow());
    $authorizer = new AuthorizeUserAction($authorization);
    $user = new UserData(
        id: '01JUSERMANAGEMENT000000000011',
        name: 'Super System',
        email: 'super@example.test',
        status: UserStatus::ACTIVE,
        isProtected: true,
        deletedAt: null,
    );
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with($user->id)->andReturn($user);

    expect(function () use ($actor, $authorizer, $repository, $user): void {
        (new SoftDeleteUser($authorizer, $repository))->execute($actor, $user->id);
    })
        ->toThrow(ProtectedUserMutation::class);
    $repository->shouldNotReceive('softDelete');
});

it('memisahkan actor asli dan target pada session impersonation', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.impersonate')
        ->andReturn(AuthorizationDecision::allow());
    $authorizer = new AuthorizeUserAction($authorization);
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->andReturn(new UserData(
        id: '01JUSERMANAGEMENT000000000012',
        name: 'Target User',
        email: 'target@example.test',
        status: UserStatus::ACTIVE,
        isProtected: false,
        deletedAt: null,
    ));
    $session = Mockery::mock(ImpersonationSession::class);
    $session->expects('start')->with($actor, '01JUSERMANAGEMENT000000000012', 'support request');

    (new StartImpersonation($authorizer, $repository, $session))->execute(
        $actor,
        new ImpersonationRequestData('01JUSERMANAGEMENT000000000012', 'support request'),
    );
});
