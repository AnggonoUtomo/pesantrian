<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Application\Actions;

use App\Models\User;
use App\Modules\System\UserManagement\Application\Services\AuthorizeUserAction;
use App\Modules\System\UserManagement\Domain\Exceptions\ProtectedUserMutation;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;

final readonly class UpdateUserAvatar
{
    public function __construct(private AuthorizeUserAction $authorization) {}

    public function execute(?Authenticatable $actor, User $user, UploadedFile $avatar): void
    {
        $this->authorization->ensure($actor, 'user.update');

        if ($user->isSuperSystem() || $user->trashed()) {
            throw new ProtectedUserMutation('Avatar user terlindungi atau terarsip tidak dapat diubah.');
        }

        $user->addMedia($avatar)->usingName('avatar')->toMediaCollection('avatar');
    }
}
