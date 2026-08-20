<?php

declare(strict_types=1);

use Illuminate\Routing\Router;

it('menjaga route API tepat sama dengan matrix authoritative', function (): void {
    $specification = file_get_contents(base_path('docs/API.md'));
    expect($specification)->toBeString();

    preg_match_all(
        '/^\|\s*(GET|POST|PATCH|DELETE)\s*\|\s*`([^`]+)`\s*\|/m',
        $specification,
        $matches,
        PREG_SET_ORDER,
    );

    $expected = array_map(
        static fn (array $match): string => $match[1].' '.$match[2],
        $matches,
    );
    sort($expected);

    $actual = [];

    foreach (app(Router::class)->getRoutes() as $route) {
        if (! str_starts_with($route->uri(), 'api/v1')) {
            continue;
        }

        foreach (array_diff($route->methods(), ['HEAD', 'OPTIONS']) as $method) {
            $actual[] = $method.' /'.$route->uri();
        }
    }

    sort($actual);

    expect($actual)->toBe($expected)
        ->and(array_count_values($actual))->each->toBe(1);
});

it('mewajibkan security middleware dan idempotency pada mutation API', function (): void {
    foreach (app(Router::class)->getRoutes() as $route) {
        if (! str_starts_with($route->uri(), 'api/v1')) {
            continue;
        }

        $middleware = $route->middleware();

        expect($middleware)
            ->toContain('web')
            ->toContain('auth')
            ->toContain('verified')
            ->toContain('throttle:system-api');

        if (array_intersect($route->methods(), ['POST', 'PATCH', 'PUT', 'DELETE']) !== []) {
            expect($middleware)->toContain('api.idempotency');
        }
    }
});

it('melarang query persistence dan business mutation langsung pada API controller', function (): void {
    $controllers = [];

    foreach (app(Router::class)->getRoutes() as $route) {
        if (! str_starts_with($route->uri(), 'api/v1')) {
            continue;
        }

        $controller = $route->getControllerClass();

        if (is_string($controller)) {
            $controllers[$controller] = true;
        }
    }

    foreach (array_keys($controllers) as $controller) {
        $source = file_get_contents((new ReflectionClass($controller))->getFileName());

        expect($source)
            ->not->toContain('::query(')
            ->not->toContain('::create(')
            ->not->toContain('::find(')
            ->not->toContain('::findOrFail(')
            ->not->toContain('$request->validate(')
            ->not->toContain('DB::')
            ->not->toContain('->syncRoles(')
            ->not->toContain('->givePermissionTo(')
            ->not->toContain('->revokePermissionTo(');
    }
});
