<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Resources;

use App\Modules\System\UserManagement\Application\DTO\UserData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserData */
final class UserApiResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var UserData $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status->value,
            'is_protected' => $user->isProtected,
            'deleted_at' => $user->deletedAt,
            'roles' => $user->roles,
            'avatar_url' => $user->avatarUrl,
            'email_verified' => $user->emailVerified,
            'last_login_at' => $user->lastLoginAt,
        ];
    }
}
