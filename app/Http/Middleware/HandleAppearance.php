<?php

namespace App\Http\Middleware;

use App\Modules\System\SystemSetting\Application\Contracts\SystemRuntimeSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    public function __construct(private readonly SystemRuntimeSettings $settings) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $runtime = $this->settings->current();
        $preference = $request->cookie('appearance');
        $appearance = in_array($preference, ['light', 'dark', 'system'], true)
            ? $preference
            : $runtime->appearanceDefault;

        View::share([
            'appearance' => $appearance,
            'branding' => $runtime->branding(),
        ]);

        return $next($request);
    }
}
