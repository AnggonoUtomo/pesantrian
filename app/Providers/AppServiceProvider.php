<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use StarterKit\Http\Idempotency\Contracts\RuntimeApiPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            if ($ability === 'impersonate') {
                return null;
            }

            return $user->isSuperSystem() ? true : null;
        });

        $this->configureDefaults();
        $this->configureRuntimeApiRateLimit();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    private function configureRuntimeApiRateLimit(): void
    {
        RateLimiter::for('system-api', function (Request $request): Limit {
            $actor = (string) $request->user()?->getAuthIdentifier();
            $endpoint = $request->route()?->getName() ?? $request->path();
            $perMinute = max(1, min(1000, app(RuntimeApiPolicy::class)->rateLimitPerMinute()));

            return Limit::perMinute($perMinute)
                ->by(($actor !== '' ? $actor : $request->ip()).'|'.$endpoint);
        });
    }
}
