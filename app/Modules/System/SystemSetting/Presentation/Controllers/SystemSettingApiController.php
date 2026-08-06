<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Controllers;

use App\Modules\System\SystemSetting\Application\Actions\UpdateSystemSetting;
use App\Modules\System\SystemSetting\Application\Queries\ListSystemSettings;
use App\Modules\System\SystemSetting\Domain\Exceptions\UnknownSettingDefinition;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use App\Modules\System\SystemSetting\Presentation\Requests\UpdateSystemSettingRequest;
use App\Modules\System\SystemSetting\Presentation\Resources\SystemSettingResource;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final readonly class SystemSettingApiController implements HasMiddleware
{
    public function __construct(
        private ListSystemSettings $listSystemSettings,
        private UpdateSystemSetting $updateSystemSetting,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.SystemSettingRecord::class, only: ['index']),
            new Middleware('can:update,'.SystemSettingRecord::class, only: ['update']),
        ];
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => array_map(
                static fn ($setting): array => (new SystemSettingResource($setting))->toArray(),
                $this->listSystemSettings->execute(),
            ),
        ]);
    }

    public function update(UpdateSystemSettingRequest $request, string $key): JsonResponse
    {
        try {
            $setting = $this->updateSystemSetting->execute($this->actor($request), $request->toData($key));
        } catch (UnknownSettingDefinition) {
            abort(404);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['value' => $exception->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'data' => (new SystemSettingResource($setting))->toArray(),
        ]);
    }

    private function actor(UpdateSystemSettingRequest $request): Authenticatable
    {
        $actor = $request->user();
        abort_if(! $actor instanceof Authenticatable, 401);

        return $actor;
    }
}
