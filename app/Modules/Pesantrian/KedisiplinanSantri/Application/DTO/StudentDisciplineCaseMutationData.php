<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO;

final readonly class StudentDisciplineCaseMutationData
{
    public function __construct(
        public ?string $studentId,
        public ?string $studentNo,
        public ?string $studentName,
        public ?string $unitId,
        public ?string $categoryId,
        public ?string $categoryName,
        public ?string $severity,
        public ?int $points,
        public ?string $occurredAt,
        public ?string $location,
        public ?string $description,
        public ?string $assignedEmployeeId,
        public ?string $assignedEmployeeName,
    ) {}

    /** @return array<string, int|string|null> */
    public function toDatabasePayload(bool $includeNull = false): array
    {
        $payload = [
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'unit_id' => $this->unitId,
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'severity' => $this->severity,
            'points' => $this->points,
            'occurred_at' => $this->occurredAt,
            'location' => $this->location,
            'description' => $this->description,
            'assigned_employee_id' => $this->assignedEmployeeId,
            'assigned_employee_name' => $this->assignedEmployeeName,
        ];

        if ($includeNull) {
            return $payload;
        }

        return array_filter($payload, static fn (mixed $value): bool => $value !== null);
    }
}
