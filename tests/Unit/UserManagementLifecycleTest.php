<?php

declare(strict_types=1);

use App\Modules\System\UserManagement\Domain\Entities\UserLifecycle;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;

it('mengizinkan transisi status lifecycle yang berbeda', function (): void {
    $lifecycle = UserLifecycle::for('01JUSERMANAGEMENT000000000000', UserStatus::ACTIVE);

    $lifecycle->changeStatus(UserStatus::SUSPENDED);

    expect($lifecycle->status)->toBe(UserStatus::SUSPENDED)
        ->and($lifecycle->isDeleted)->toBeFalse();
});

it('menolak status yang sama dan tetap menjaga state sebelumnya', function (): void {
    $lifecycle = UserLifecycle::for('01JUSERMANAGEMENT000000000001', UserStatus::ACTIVE);

    expect(fn (): UserLifecycle => $lifecycle->changeStatus(UserStatus::ACTIVE))
        ->toThrow(InvalidArgumentException::class);
    expect($lifecycle->status)->toBe(UserStatus::ACTIVE);
});

it('melindungi SuperSystem dari perubahan status dan soft delete', function (): void {
    $lifecycle = UserLifecycle::forProtectedUser('01JUSERMANAGEMENT000000000002');

    expect(fn (): UserLifecycle => $lifecycle->changeStatus(UserStatus::INACTIVE))
        ->toThrow(ProtectedUserMutation::class);
    expect(fn (): UserLifecycle => $lifecycle->softDelete())
        ->toThrow(ProtectedUserMutation::class);
    expect($lifecycle->status)->toBe(UserStatus::ACTIVE)
        ->and($lifecycle->isDeleted)->toBeFalse();
});

it('menandai user biasa sebagai soft deleted tanpa menghapus entity', function (): void {
    $lifecycle = UserLifecycle::for('01JUSERMANAGEMENT000000000003', UserStatus::INACTIVE);

    $lifecycle->softDelete();

    expect($lifecycle->isDeleted)->toBeTrue()
        ->and($lifecycle->status)->toBe(UserStatus::INACTIVE);
});

it('menjaga domain bebas dari dependency framework dan UI', function (): void {
    $directory = dirname(__DIR__, 2).'/app/Modules/System/UserManagement/Domain';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $source = file_get_contents($file->getPathname());

        expect($source)->not->toContain('Illuminate\\')
            ->and($source)->not->toContain('Eloquent')
            ->and($source)->not->toContain('Spatie\\')
            ->and($source)->not->toContain('Http\\')
            ->and($source)->not->toContain('Inertia');
    }
});
