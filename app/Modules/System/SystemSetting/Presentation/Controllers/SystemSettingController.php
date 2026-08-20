<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Controllers;

use App\Modules\System\SystemSetting\Application\Actions\UpdateSystemSetting;
use App\Modules\System\SystemSetting\Application\Queries\ListSystemSettings;
use App\Modules\System\SystemSetting\Domain\Exceptions\UnknownSettingDefinition;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use App\Modules\System\SystemSetting\Presentation\Requests\UpdateSystemSettingCategoryRequest;
use App\Modules\System\SystemSetting\Presentation\Requests\UpdateSystemSettingRequest;
use App\Modules\System\SystemSetting\Presentation\Resources\SystemSettingResource;
use App\Modules\System\SystemSetting\Presentation\Support\SystemSettingOutputPresenter;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final readonly class SystemSettingController implements HasMiddleware
{
    public function __construct(
        private ListSystemSettings $listSystemSettings,
        private UpdateSystemSetting $updateSystemSetting,
        private SystemSettingOutputPresenter $outputPresenter,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.SystemSettingRecord::class, only: ['index']),
            new Middleware('can:update,'.SystemSettingRecord::class, only: ['update', 'updateCategory']),
        ];
    }

    public function index(): Response
    {
        return Inertia::render('System/SystemSetting/pages/Index', [
            'settings' => array_map(
                fn ($setting): array => (new SystemSettingResource($setting, $this->outputPresenter))->toArray(),
                $this->listSystemSettings->execute(),
            ),
        ]);
    }

    public function update(UpdateSystemSettingRequest $request, string $key): RedirectResponse
    {
        try {
            $this->updateSystemSetting->execute($this->actor($request), $request->toData($key));
        } catch (UnknownSettingDefinition) {
            abort(404);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['value' => $exception->getMessage()]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'SystemSetting berhasil diperbarui.',
        ]);

        return back();
    }

    public function updateCategory(UpdateSystemSettingCategoryRequest $request, string $category): RedirectResponse
    {
        try {
            $updated = $this->updateSystemSetting->executeCategory($this->actor($request), $request->toData($category));
        } catch (UnknownSettingDefinition) {
            abort(404);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['updates' => $exception->getMessage()]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => count($updated).' SystemSetting berhasil diperbarui.',
        ]);

        return back();
    }

    private function actor(Request $request): Authenticatable
    {
        $actor = $request->user();
        abort_if(! $actor instanceof Authenticatable, 401);

        return $actor;
    }
}
