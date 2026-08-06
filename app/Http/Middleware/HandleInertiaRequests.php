<?php

namespace App\Http\Middleware;

use App\Modules\System\SystemSetting\Application\Contracts\SystemRuntimeSettings;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    public function __construct(private readonly SystemRuntimeSettings $settings) {}

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $runtime = $this->settings->current();

        return [
            ...parent::share($request),
            'ziggy' => array_merge(
                (new Ziggy)->toArray(),
                [
                    'location' => $request->url(),
                ],
            ),
            'name' => $runtime->appName,
            'branding' => $runtime->branding(),
            'runtime' => $runtime->runtime(),
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user()?->getUserRoles() ?? [],
                'permissions' => $request->user()?->getUserPermissions() ?? [],
                'superSystem' => $request->user()?->isSuperSystem() ?? false,
                'impersonation' => $request->hasSession() && $request->session()->has('impersonation.actor_id') ? [
                    'actorId' => $request->session()->get('impersonation.actor_id'),
                    'targetId' => $request->session()->get('impersonation.target_id'),
                    'startedAt' => $request->session()->get('impersonation.started_at'),
                ] : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
