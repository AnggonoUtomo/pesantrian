<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class PlaceStudentData
{
    public function __construct(
        public string $classGroupId,
        public string $academicTermId,
        public string $studentId,
        public string $studentNo,
        public string $joinedOn,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'class_group_id' => $this->classGroupId,
            'academic_term_id' => $this->academicTermId,
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'joined_on' => $this->joinedOn,
            'status' => 'active',
            'active_period_student_key' => $this->academicTermId.':'.$this->studentId,
        ];
    }
}
