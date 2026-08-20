<?php

declare(strict_types=1);

namespace Tests\Fixtures\RuntimeModules {
    trait RecordsRuntimeTrace
    {
        private function record(string $event): void
        {
            /** @var list<string> $trace */
            $trace = $this->app->bound('module-runtime-trace')
                ? $this->app->make('module-runtime-trace')
                : [];
            $trace[] = $event;
            $this->app->instance('module-runtime-trace', $trace);
        }
    }
}

namespace Tests\Fixtures\RuntimeModules\Root {
    use Illuminate\Support\ServiceProvider as BaseServiceProvider;
    use Tests\Fixtures\RuntimeModules\RecordsRuntimeTrace;

    final class ServiceProvider extends BaseServiceProvider
    {
        use RecordsRuntimeTrace;

        public function register(): void
        {
            $this->record('root.register');
        }

        public function boot(): void
        {
            $this->record('root.boot');
        }
    }
}

namespace Tests\Fixtures\RuntimeModules\Independent {
    use Illuminate\Support\ServiceProvider as BaseServiceProvider;
    use Tests\Fixtures\RuntimeModules\RecordsRuntimeTrace;

    final class ServiceProvider extends BaseServiceProvider
    {
        use RecordsRuntimeTrace;

        public function register(): void
        {
            $this->record('independent.register');
        }

        public function boot(): void
        {
            $this->record('independent.boot');
        }
    }
}

namespace Tests\Fixtures\RuntimeModules\Disabled {
    use Illuminate\Support\ServiceProvider as BaseServiceProvider;
    use Tests\Fixtures\RuntimeModules\RecordsRuntimeTrace;

    final class ServiceProvider extends BaseServiceProvider
    {
        use RecordsRuntimeTrace;

        public function register(): void
        {
            $this->record('disabled.register');
        }

        public function boot(): void
        {
            $this->record('disabled.boot');
        }
    }
}

namespace Tests\Fixtures\RuntimeModules\Dependent {
    use Illuminate\Support\ServiceProvider as BaseServiceProvider;
    use Tests\Fixtures\RuntimeModules\RecordsRuntimeTrace;

    final class ServiceProvider extends BaseServiceProvider
    {
        use RecordsRuntimeTrace;

        public function register(): void
        {
            $this->record('dependent.register');
        }

        public function boot(): void
        {
            $this->record('dependent.boot');
        }
    }
}

namespace Tests\Fixtures\RuntimeModules\RegisterFailure {
    use Illuminate\Support\ServiceProvider as BaseServiceProvider;
    use RuntimeException;

    final class ServiceProvider extends BaseServiceProvider
    {
        public function register(): void
        {
            throw new RuntimeException('credential-register-fixture-tidak-boleh-bocor');
        }
    }
}

namespace Tests\Fixtures\RuntimeModules\BootFailure {
    use Illuminate\Support\ServiceProvider as BaseServiceProvider;
    use RuntimeException;
    use Tests\Fixtures\RuntimeModules\RecordsRuntimeTrace;

    final class ServiceProvider extends BaseServiceProvider
    {
        use RecordsRuntimeTrace;

        public function register(): void
        {
            $this->record('boot-failure.register');
        }

        public function boot(): void
        {
            throw new RuntimeException('credential-boot-fixture-tidak-boleh-bocor');
        }
    }
}
