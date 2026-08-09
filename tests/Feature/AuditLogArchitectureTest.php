<?php

declare(strict_types=1);

use App\Modules\System\AuditLog\Infrastructure\Persistence\Repositories\EloquentAuditLogRepository;

it('menjaga repository audit tanpa operasi update atau delete', function (): void {
    $repository = file_get_contents((new ReflectionClass(EloquentAuditLogRepository::class))->getFileName());

    expect($repository)
        ->not->toContain('->update(')
        ->not->toContain('->delete(')
        ->not->toContain('->forceDelete(');
});
