<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Presentation\Controllers;

use App\Modules\System\AuditLog\Application\Queries\GetAuditLog;
use App\Modules\System\AuditLog\Application\Queries\ListAuditLogs;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use App\Modules\System\AuditLog\Presentation\Requests\AuditLogFilterRequest;
use App\Modules\System\AuditLog\Presentation\Resources\AuditLogResource;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class AuditLogApiController implements HasMiddleware
{
    public function __construct(
        private ListAuditLogs $listAuditLogs,
        private GetAuditLog $getAuditLog,
    ) {}

    public static function middleware(): array
    {
        return [new Middleware('can:viewAny,'.AuditRecord::class)];
    }

    public function index(AuditLogFilterRequest $request): JsonResponse
    {
        $page = $this->listAuditLogs->execute($this->actor($request), $request->toFilter());

        return response()->json([
            'success' => true,
            'data' => $page->toArray(),
        ]);
    }

    public function show(AuditLogFilterRequest $request, string $auditLog): JsonResponse
    {
        $record = $this->getAuditLog->execute($this->actor($request), $auditLog);

        abort_if($record === null, 404);

        return response()->json([
            'success' => true,
            'data' => (new AuditLogResource($record))->toArray(),
        ]);
    }

    private function actor(AuditLogFilterRequest $request): Authenticatable
    {
        $actor = $request->user();
        abort_if(! $actor instanceof Authenticatable, 401);

        return $actor;
    }
}
