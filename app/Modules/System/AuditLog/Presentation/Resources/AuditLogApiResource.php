<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Presentation\Resources;

use App\Modules\System\AuditLog\Application\DTO\AuditRecordData;
use App\Modules\System\AuditLog\Presentation\Support\AuditLogOperatorLabels;

final readonly class AuditLogApiResource
{
    public function __construct(private AuditRecordData $record) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $security = AuditLogOperatorLabels::securityContext($this->record->metadata);
        $setting = AuditLogOperatorLabels::settingChange($this->record->metadata);

        return [
            'actor_name' => $this->record->actorName,
            'action_label' => AuditLogOperatorLabels::action($this->record->action),
            'subject_label' => AuditLogOperatorLabels::subject($this->record->subjectType),
            'module_label' => AuditLogOperatorLabels::module($this->record->module),
            'reason' => $this->record->reason,
            'security_context' => $security === null ? null : [
                'browser' => $security['browser'],
                'ip_address' => $security['ipAddress'],
            ],
            'setting_change' => $setting === null ? null : [
                'category' => $setting['category'],
                'setting' => $setting['setting'],
                'before_value' => $setting['beforeValue'],
                'after_value' => $setting['afterValue'],
            ],
            'created_at' => $this->record->createdAt->format(DATE_ATOM),
        ];
    }
}
