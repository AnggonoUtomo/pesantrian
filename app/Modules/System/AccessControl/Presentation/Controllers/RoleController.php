<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Controllers;

use App\Modules\System\AccessControl\Application\Services\AuthorizeRoleMutation;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class RoleController implements HasMiddleware
{
    public function __construct(private readonly AuthorizeRoleMutation $authorizeRoleMutation) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Role::class, only: ['index']),
            new Middleware('can:create,'.Role::class, only: ['store']),
        ];
    }

    public function index(): array
    {
        return ['status' => 'authorized'];
    }

    public function store(Request $request): array
    {
        $this->authorizeRoleMutation->ensureAllowed($request->user());

        return ['status' => 'authorized'];
    }
}
