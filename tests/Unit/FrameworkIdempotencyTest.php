<?php

declare(strict_types=1);

use StarterKit\Http\Idempotency\Contracts\IdempotencyRepository;
use StarterKit\Http\Idempotency\Contracts\RuntimeApiPolicy;
use StarterKit\Http\Idempotency\DTO\IdempotencyReservationData;
use StarterKit\Http\Idempotency\Exceptions\IdempotencyStorageUnavailable;
use StarterKit\Http\Idempotency\IdempotencyManager;
use StarterKit\Http\Idempotency\Runtime\DefaultRuntimeApiPolicy;
use StarterKit\Http\Idempotency\Runtime\UnavailableIdempotencyRepository;

it('menyediakan policy runtime API default yang aman', function (): void {
    $policy = new DefaultRuntimeApiPolicy;

    expect($policy->idempotencyRetentionHours())->toBe(24)
        ->and($policy->rateLimitPerMinute())->toBe(60);
});

it('membuat payload hash canonical dan memakai retention dari policy', function (): void {
    $repository = new class implements IdempotencyRepository
    {
        /** @var list<array<string, mixed>> */
        public array $reservations = [];

        public function reserve(
            string $actorId,
            string $key,
            string $endpoint,
            string $payloadHash,
            DateTimeImmutable $expiresAt,
        ): IdempotencyReservationData {
            $this->reservations[] = compact(
                'actorId',
                'key',
                'endpoint',
                'payloadHash',
                'expiresAt',
            );

            return new IdempotencyReservationData(
                id: '01K2ZV6S1YQH9F4K3R8M7N6P5T',
                payloadHash: $payloadHash,
                responseStatus: 102,
                responseBody: ['pending' => true],
                expiresAt: $expiresAt,
                created: true,
            );
        }

        public function complete(string $reservationId, int $responseStatus, array $responseBody): void {}

        public function delete(string $reservationId): void {}

        public function pruneExpired(): int
        {
            return 0;
        }
    };
    $policy = new class implements RuntimeApiPolicy
    {
        public function idempotencyRetentionHours(): int
        {
            return 12;
        }

        public function rateLimitPerMinute(): int
        {
            return 30;
        }
    };
    $manager = new IdempotencyManager($repository, $policy);

    $first = $manager->begin(
        '01K2ZV6S1YQH9F4K3R8M7N6P5T',
        'POST api/v1/users',
        'request-1',
        ['profile' => ['name' => 'Saka', 'email' => 'saka@example.test'], 'active' => true],
    );
    $manager->begin(
        '01K2ZV6S1YQH9F4K3R8M7N6P5T',
        'POST api/v1/users',
        'request-2',
        ['active' => true, 'profile' => ['email' => 'saka@example.test', 'name' => 'Saka']],
    );

    $firstReservation = $repository->reservations[0];
    $secondReservation = $repository->reservations[1];
    $hours = ((int) $firstReservation['expiresAt']->format('U') - time()) / 3600;

    expect($first->isReplay())->toBeFalse()
        ->and($firstReservation['payloadHash'])->toBe($secondReservation['payloadHash'])
        ->and($hours)->toBeGreaterThan(11.9)
        ->and($hours)->toBeLessThanOrEqual(12.0);
});

it('meredaksi response sensitif sebelum persistence', function (): void {
    $repository = new class implements IdempotencyRepository
    {
        /** @var array<string, mixed> */
        public array $completed = [];

        public function reserve(
            string $actorId,
            string $key,
            string $endpoint,
            string $payloadHash,
            DateTimeImmutable $expiresAt,
        ): IdempotencyReservationData {
            throw new LogicException('Tidak digunakan oleh test ini.');
        }

        public function complete(string $reservationId, int $responseStatus, array $responseBody): void
        {
            $this->completed = $responseBody;
        }

        public function delete(string $reservationId): void {}

        public function pruneExpired(): int
        {
            return 0;
        }
    };
    $manager = new IdempotencyManager($repository, new DefaultRuntimeApiPolicy);

    $manager->complete('01K2ZV6S1YQH9F4K3R8M7N6P5T', 201, [
        'data' => [
            'name' => 'Saka',
            'password' => 'dummy-password',
            'nested' => ['api_token' => 'dummy-token'],
        ],
    ]);

    expect($repository->completed)->toMatchArray([
        'data' => [
            'name' => 'Saka',
            'password' => '[REDACTED]',
            'nested' => ['api_token' => '[REDACTED]'],
        ],
    ])->and(json_encode($repository->completed, JSON_THROW_ON_ERROR))
        ->not->toContain('dummy-password', 'dummy-token');
});

it('gagal tertutup ketika adapter persistence tidak tersedia', function (): void {
    $repository = new UnavailableIdempotencyRepository;

    expect(fn () => $repository->reserve(
        '01K2ZV6S1YQH9F4K3R8M7N6P5T',
        'request-1',
        'POST api/v1/users',
        str_repeat('a', 64),
        new DateTimeImmutable('+24 hours'),
    ))->toThrow(IdempotencyStorageUnavailable::class);
});

it('menjaga framework package bebas dari import namespace App', function (): void {
    $root = dirname(__DIR__, 2).'/packages/StarterKit/src';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());

        expect($source)->not->toMatch('/(?:use|new|extends|implements)\\s+\\\\?App\\\\/');
    }
});
