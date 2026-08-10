<?php

declare(strict_types=1);

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AccessControl\Application\Contracts\RoleAssignmentCapability;
use App\Modules\System\AccessControl\Application\DTO\AuthorizationDecision;
use App\Modules\System\UserManagement\Application\Actions\AssignUserRole;
use App\Modules\System\UserManagement\Application\Actions\ChangeUserStatus;
use App\Modules\System\UserManagement\Application\Actions\CreateUser;
use App\Modules\System\UserManagement\Application\Actions\ForceDeleteUser;
use App\Modules\System\UserManagement\Application\Actions\RestoreUser;
use App\Modules\System\UserManagement\Application\Actions\SoftDeleteUser;
use App\Modules\System\UserManagement\Application\Actions\StartImpersonation;
use App\Modules\System\UserManagement\Application\Actions\UpdateUser;
use App\Modules\System\UserManagement\Application\Contracts\ImpersonationSession;
use App\Modules\System\UserManagement\Application\Contracts\UserManagementActivityPublisher;
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

function userManagementActivityPublisher(): UserManagementActivityPublisher
{
    $publisher = Mockery::mock(UserManagementActivityPublisher::class);
    $publisher->allows('publish')->andReturnUsing(
        static fn (...$arguments): mixed => $arguments[3](),
    );

    return $publisher;
}

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

    expect(fn (): UserData => (new CreateUser($authorizer, $repository, userManagementActivityPublisher()))
        ->execute($actor, new CreateUserData('User Satu', 'user@example.test', 'password')))
        ->toThrow(AuthorizationException::class);
    $repository->shouldNotReceive('create');
});

it('menggunakan public contract AccessControl untuk assignment role', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $actor->expects('getAuthIdentifier')->andReturn('01JUSERMANAGEMENT000000000098');
    $target = Mockery::mock(Authenticatable::class);
    $target->expects('getAuthIdentifier')->andReturn('01JUSERMANAGEMENT000000000099');
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.update')
        ->andReturn(AuthorizationDecision::allow());
    $authorizer = new AuthorizeUserAction($authorization);
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with('01JUSERMANAGEMENT000000000099')->andReturn(new UserData(
        id: '01JUSERMANAGEMENT000000000099',
        name: 'Target User',
        email: 'target@example.test',
        status: UserStatus::ACTIVE,
        isProtected: false,
        deletedAt: null,
    ));
    $roles = Mockery::mock(RoleAssignmentCapability::class);
    $roles->expects('assignRole')->with($actor, $target, 'SecurityAdmin');

    (new AssignUserRole($authorizer, $repository, $roles, userManagementActivityPublisher()))
        ->execute($actor, $target, 'SecurityAdmin');
});

it('mengotorisasi update user melalui action dan repository contract', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $actor->expects('getAuthIdentifier')->andReturn('01JUSERMANAGEMENT000000000098');
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
    $repository->expects('find')->with($expected->id)->andReturn($expected);
    $repository->expects('update')->with($expected->id, $data)->andReturn($expected);

    expect((new UpdateUser(new AuthorizeUserAction($authorization), $repository, userManagementActivityPublisher()))
        ->execute($actor, $expected->id, $data))->toBe($expected);
});

it('menolak update target SuperSystem sebelum repository mutation dipanggil', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.update')
        ->andReturn(AuthorizationDecision::allow());
    $user = new UserData(
        id: '01JUSERMANAGEMENT000000000015',
        name: 'Super System',
        email: 'super@example.test',
        status: UserStatus::ACTIVE,
        isProtected: true,
        deletedAt: null,
    );
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with($user->id)->andReturn($user);
    $repository->shouldNotReceive('update');

    expect(fn (): UserData => (new UpdateUser(
        new AuthorizeUserAction($authorization),
        $repository,
        userManagementActivityPublisher(),
    ))->execute($actor, $user->id, new UpdateUserData('Berubah', 'berubah@example.test')))
        ->toThrow(ProtectedUserMutation::class);
});

it('menolak assignment role target SuperSystem sebelum capability dipanggil', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $target = Mockery::mock(Authenticatable::class);
    $target->expects('getAuthIdentifier')->andReturn('01JUSERMANAGEMENT000000000016');
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.update')
        ->andReturn(AuthorizationDecision::allow());
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with('01JUSERMANAGEMENT000000000016')->andReturn(new UserData(
        id: '01JUSERMANAGEMENT000000000016',
        name: 'Super System',
        email: 'super@example.test',
        status: UserStatus::ACTIVE,
        isProtected: true,
        deletedAt: null,
    ));
    $roles = Mockery::mock(RoleAssignmentCapability::class);
    $roles->shouldNotReceive('assignRole');

    expect(fn (): null => (new AssignUserRole(
        new AuthorizeUserAction($authorization),
        $repository,
        $roles,
        userManagementActivityPublisher(),
    ))->execute($actor, $target, 'SecurityAdmin'))
        ->toThrow(ProtectedUserMutation::class);
});

it('menolak assignment role untuk user terarsip sebelum capability dipanggil', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $target = Mockery::mock(Authenticatable::class);
    $target->expects('getAuthIdentifier')->andReturn('01JUSERMANAGEMENT000000000021');
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.update')
        ->andReturn(AuthorizationDecision::allow());
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with('01JUSERMANAGEMENT000000000021')->andReturn(new UserData(
        id: '01JUSERMANAGEMENT000000000021',
        name: 'Archived User',
        email: 'archived@example.test',
        status: UserStatus::ACTIVE,
        isProtected: false,
        deletedAt: '2026-08-10T00:00:00+00:00',
    ));
    $roles = Mockery::mock(RoleAssignmentCapability::class);
    $roles->shouldNotReceive('assignRole');

    expect(fn (): null => (new AssignUserRole(
        new AuthorizeUserAction($authorization),
        $repository,
        $roles,
        userManagementActivityPublisher(),
    ))->execute($actor, $target, 'SecurityAdmin'))->toThrow(ProtectedUserMutation::class);
});

it('mengubah status user biasa melalui action terotorisasi', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $actor->expects('getAuthIdentifier')->andReturn('01JUSERMANAGEMENT000000000098');
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

    (new ChangeUserStatus(new AuthorizeUserAction($authorization), $repository, userManagementActivityPublisher()))
        ->execute($actor, $user->id, UserStatus::SUSPENDED);
});

it('menolak perubahan status untuk user terarsip sebelum repository mutation dipanggil', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.status.manage')
        ->andReturn(AuthorizationDecision::allow());
    $user = new UserData(
        id: '01JUSERMANAGEMENT000000000022',
        name: 'Archived User',
        email: 'archived-status@example.test',
        status: UserStatus::ACTIVE,
        isProtected: false,
        deletedAt: '2026-08-10T00:00:00+00:00',
    );
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with($user->id)->andReturn($user);
    $repository->shouldNotReceive('changeStatus');

    expect(fn (): null => (new ChangeUserStatus(
        new AuthorizeUserAction($authorization),
        $repository,
        userManagementActivityPublisher(),
    ))->execute($actor, $user->id, UserStatus::SUSPENDED))->toThrow(ProtectedUserMutation::class);
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
        (new SoftDeleteUser($authorizer, $repository, userManagementActivityPublisher()))
            ->execute($actor, $user->id);
    })
        ->toThrow(ProtectedUserMutation::class);
    $repository->shouldNotReceive('softDelete');
});

it('memulihkan user terarsip melalui permission restore yang terpisah', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $actor->expects('getAuthIdentifier')->andReturn('01JUSERMANAGEMENT000000000098');
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.restore')
        ->andReturn(AuthorizationDecision::allow());
    $user = new UserData(
        id: '01JUSERMANAGEMENT000000000017',
        name: 'Archived User',
        email: 'archived@example.test',
        status: UserStatus::SUSPENDED,
        isProtected: false,
        deletedAt: '2026-08-06T00:00:00+00:00',
    );
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with($user->id)->andReturn($user);
    $repository->expects('restore')->with($user->id);

    (new RestoreUser(new AuthorizeUserAction($authorization), $repository, userManagementActivityPublisher()))
        ->execute($actor, $user->id);
});

it('menghapus permanen hanya user terarsip melalui permission khusus', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $actor->expects('getAuthIdentifier')->andReturn('01JUSERMANAGEMENT000000000098');
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')
        ->with($actor, 'user.force.delete')
        ->andReturn(AuthorizationDecision::allow());
    $user = new UserData(
        id: '01JUSERMANAGEMENT000000000018',
        name: 'Archived User',
        email: 'archived@example.test',
        status: UserStatus::INACTIVE,
        isProtected: false,
        deletedAt: '2026-08-06T00:00:00+00:00',
    );
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with($user->id)->andReturn($user);
    $repository->expects('forceDelete')->with($user->id);

    (new ForceDeleteUser(new AuthorizeUserAction($authorization), $repository, userManagementActivityPublisher()))
        ->execute($actor, $user->id);
});

it('menolak restore dan force delete untuk user aktif atau protected', function (): void {
    $actor = Mockery::mock(Authenticatable::class);
    $authorization = Mockery::mock(AuthorizationCapability::class);
    $authorization->expects('can')->with($actor, 'user.restore')->andReturn(AuthorizationDecision::allow());
    $authorization->expects('can')->with($actor, 'user.force.delete')->andReturn(AuthorizationDecision::allow());
    $active = new UserData('01JUSERMANAGEMENT000000000019', 'Active', 'active@example.test', UserStatus::ACTIVE, false, null);
    $protected = new UserData('01JUSERMANAGEMENT000000000020', 'Protected', 'protected@example.test', UserStatus::ACTIVE, true, '2026-08-06T00:00:00+00:00');
    $repository = Mockery::mock(UserRepository::class);
    $repository->expects('find')->with($active->id)->andReturn($active);
    $repository->expects('find')->with($protected->id)->andReturn($protected);
    $repository->shouldNotReceive('restore');
    $repository->shouldNotReceive('forceDelete');
    $authorizer = new AuthorizeUserAction($authorization);

    expect(fn (): null => (new RestoreUser($authorizer, $repository, userManagementActivityPublisher()))->execute($actor, $active->id))
        ->toThrow(ProtectedUserMutation::class)
        ->and(fn (): null => (new ForceDeleteUser($authorizer, $repository, userManagementActivityPublisher()))->execute($actor, $protected->id))
        ->toThrow(ProtectedUserMutation::class);
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
