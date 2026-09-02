<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class AssignHomeroomData
{
    public function __construct(
        public string $classGroupId,
        public string $employeeId,
        public string $employeeName,
        public string $assignedOn,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'class_group_id' => $this->classGroupId,
            'employee_id' => $this->employeeId,
            'employee_name' => $this->employeeName,
            'assigned_on' => $this->assignedOn,
            'status' => 'active',
            'active_class_group_key' => $this->classGroupId,
        ];
    }
}
