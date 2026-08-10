<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingRepository;
use App\Modules\System\SystemSetting\Application\DTO\SettingValueData;
use App\Modules\System\SystemSetting\Application\DTO\UpdateSystemSettingCategoryData;
use App\Modules\System\SystemSetting\Application\DTO\UpdateSystemSettingData;
use App\Modules\System\SystemSetting\Application\Services\RequestSettingMemoizer;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use App\Modules\System\SystemSetting\Application\Services\ValidateSettingConsistency;
use DateTimeImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UpdateSystemSetting
{
    public function __construct(
        private AuthorizationCapability $authorization,
        private SettingDefinitionRegistry $definitions,
        private SystemSettingReader $reader,
        private SystemSettingRepository $repository,
        private RequestSettingMemoizer $memoizer,
        private AuditRecorder $auditRecorder,
        private ValidateSettingConsistency $consistency,
    ) {}

    public function execute(Authenticatable $actor, UpdateSystemSettingData $data): SettingValueData
    {
        $result = $this->persistUpdates(
            actorId: $this->actorId($actor),
            updates: [$data->key => $data->value],
            reason: $data->reason,
            correlationId: $data->correlationId,
        );

        return $result[0];
    }

    /** @return list<SettingValueData> */
    public function executeCategory(Authenticatable $actor, UpdateSystemSettingCategoryData $data): array
    {
        foreach (array_keys($data->updates) as $key) {
            if (! $data->category->owns($key)) {
                throw new InvalidArgumentException('Key SystemSetting bukan milik kategori yang dipilih.');
            }
        }

        return $this->persistUpdates(
            actorId: $this->actorId($actor),
            updates: $data->updates,
            reason: $data->reason,
            correlationId: $data->correlationId,
        );
    }

    /**
     * @param  array<string, mixed>  $updates
     * @return list<SettingValueData>
     */
    private function persistUpdates(string $actorId, array $updates, string $reason, string $correlationId): array
    {
        $definitions = [];
        $normalizedUpdates = [];
        $before = [];

        foreach ($updates as $key => $value) {
            $definition = $this->definitions->definition($key);
            $definitions[$key] = $definition;
            $normalizedUpdates[$key] = $definition->normalize($value);
            $before[$key] = $this->reader->get($key);
        }

        $this->consistency->forUpdates($normalizedUpdates);

        $results = DB::transaction(function () use ($actorId, $before, $correlationId, $definitions, $normalizedUpdates, $reason): array {
            $results = [];

            foreach ($normalizedUpdates as $key => $normalizedValue) {
                $definition = $definitions[$key];
                $stored = $this->repository->upsert($definition, $normalizedValue, $actorId);

                $this->auditRecorder->record(new AuditEntryData(
                    eventId: (string) Str::ulid(),
                    actorId: $actorId,
                    action: 'system_setting.updated',
                    subjectType: 'system_setting',
                    subjectId: $stored->id,
                    module: 'SystemSetting',
                    correlationId: $correlationId,
                    reason: $reason,
                    metadata: [
                        'setting_key' => $definition->key,
                        'before_value' => $definition->sensitive ? '[REDACTED]' : $before[$key]->value,
                        'after_value' => $definition->sensitive ? '[REDACTED]' : $normalizedValue,
                        'result' => 'updated',
                    ],
                    occurredAt: new DateTimeImmutable,
                ));

                $results[] = new SettingValueData(
                    key: $definition->key,
                    value: $normalizedValue,
                    source: 'database',
                    updatedAt: $stored->updatedAt,
                );
            }

            return $results;
        });

        foreach ($results as $result) {
            $this->memoizer->put($result);
        }

        return $results;
    }

    private function actorId(Authenticatable $actor): string
    {
        if (! $this->authorization->isSuperSystem($actor)) {
            throw new AuthorizationException('Hanya SuperSystem yang dapat mengubah SystemSetting.');
        }

        $actorId = (string) $actor->getAuthIdentifier();

        if (! Str::isUlid($actorId)) {
            throw new InvalidArgumentException('Actor ID wajib berupa ULID.');
        }

        return $actorId;
    }
}
