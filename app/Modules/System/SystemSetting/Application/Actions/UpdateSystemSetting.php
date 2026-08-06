<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Actions;

use App\Modules\System\AccessControl\Application\Contracts\AuthorizationCapability;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingRepository;
use App\Modules\System\SystemSetting\Application\DTO\SettingValueData;
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
        if (! $this->authorization->isSuperSystem($actor)) {
            throw new AuthorizationException('Hanya SuperSystem yang dapat mengubah SystemSetting.');
        }

        $actorId = (string) $actor->getAuthIdentifier();

        if (! Str::isUlid($actorId)) {
            throw new InvalidArgumentException('Actor ID wajib berupa ULID.');
        }

        $definition = $this->definitions->definition($data->key);
        $normalizedValue = $definition->normalize($data->value);
        $this->consistency->forUpdate($data->key, $normalizedValue);
        $before = $this->reader->get($data->key);

        $result = DB::transaction(function () use (
            $actorId,
            $before,
            $data,
            $definition,
            $normalizedValue,
        ): SettingValueData {
            $stored = $this->repository->upsert($definition, $normalizedValue, $actorId);

            $this->auditRecorder->record(new AuditEntryData(
                eventId: (string) Str::ulid(),
                actorId: $actorId,
                action: 'system_setting.updated',
                subjectType: 'system_setting',
                subjectId: $stored->id,
                module: 'SystemSetting',
                correlationId: $data->correlationId,
                reason: $data->reason,
                metadata: [
                    'setting_key' => $definition->key,
                    'before_value' => $before->value,
                    'after_value' => $normalizedValue,
                    'result' => 'updated',
                ],
                occurredAt: new DateTimeImmutable,
            ));

            return new SettingValueData(
                key: $definition->key,
                value: $normalizedValue,
                source: 'database',
                updatedAt: $stored->updatedAt,
            );
        });

        $this->memoizer->put($result);

        return $result;
    }
}
