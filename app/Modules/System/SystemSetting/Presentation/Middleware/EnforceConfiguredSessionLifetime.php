<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Middleware;

use App\Modules\System\SystemSetting\Application\Contracts\SystemRuntimeSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnforceConfiguredSessionLifetime
{
    private const STARTED_AT = 'system_setting.session_started_at';

    private const LAST_ACTIVITY_AT = 'system_setting.last_activity_at';

    public function __construct(private SystemRuntimeSettings $settings) {}

    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null || ! $request->hasSession()) {
            return $next($request);
        }

        $now = now()->getTimestamp();
        $startedAt = (int) $request->session()->get(self::STARTED_AT, $now);
        $lastActivityAt = (int) $request->session()->get(self::LAST_ACTIVITY_AT, $now);
        $runtime = $this->settings->current();
        $idleExpired = ($now - $lastActivityAt) >= ($runtime->sessionIdleMinutes * 60);
        $absoluteExpired = ($now - $startedAt) >= ($runtime->sessionAbsoluteHours * 3600);

        if ($idleExpired || $absoluteExpired) {
            Auth::guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session telah berakhir.'], 401);
            }

            return redirect()->route('login')->with('status', 'Session telah berakhir. Silakan login kembali.');
        }

        $request->session()->put([
            self::STARTED_AT => $startedAt,
            self::LAST_ACTIVITY_AT => $now,
        ]);

        return $next($request);
    }
}
