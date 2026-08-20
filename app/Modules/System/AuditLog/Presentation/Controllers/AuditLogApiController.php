<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\System\AuditLog\Application\Queries\GetAuditLog;
use App\Modules\System\AuditLog\Application\Queries\ListAuditLogs;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\AuditLog\Presentation\Requests\AuditLogFilterRequest;
use App\Modules\System\AuditLog\Presentation\Resources\AuditLogApiResource;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class AuditLogApiController implements HasMiddleware
{
    public function __construct(
        private ListAuditLogs $listAuditLogs,
        private GetAuditLog $getAuditLog,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [new Middleware('can:viewAny,'.AuditRecord::class)];
    }

    public function index(AuditLogFilterRequest $request): JsonResponse
    {
        $page = $this->listAuditLogs->execute($this->actor($request), $request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar audit berhasil dibaca.',
            array_map(
                static fn ($record): array => (new AuditLogApiResource($record))->toArray(),
                $page->items,
            ),
            [
                'current_page' => $page->currentPage,
                'per_page' => $page->perPage,
                'total' => $page->total,
                'last_page' => $page->lastPage,
            ],
        );
    }

    public function show(AuditLogFilterRequest $request, string $auditLog): JsonResponse
    {
        $record = $this->getAuditLog->execute($this->actor($request), $auditLog);

        abort_if($record === null, 404);

        return $this->responses->success(
            $request,
            'Detail audit berhasil dibaca.',
            (new AuditLogApiResource($record))->toArray(),
        );
    }

    private function actor(AuditLogFilterRequest $request): Authenticatable
    {
        $actor = $request->user();
        abort_if(! $actor instanceof Authenticatable, 401);

        return $actor;
    }
}
