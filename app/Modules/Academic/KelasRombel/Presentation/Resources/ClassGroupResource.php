<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Resources;

use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupHomeroomData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupStudentData;
use App\Modules\Academic\KelasRombel\Application\DTO\ReferenceData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClassGroupData */
final class ClassGroupResource extends JsonResource
{
    public function __construct(mixed $resource, private readonly bool $includeDetails = false)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var ClassGroupData $classGroup */
        $classGroup = $this->resource;

        $data = [
            'id' => $classGroup->id,
            'academic_year' => $this->reference($classGroup->academicYear),
            'academic_term' => $this->reference($classGroup->academicTerm),
            'unit' => $this->reference($classGroup->unit),
            'curriculum' => $classGroup->curriculum instanceof ReferenceData ? $this->reference($classGroup->curriculum) : null,
            'class_level' => $this->reference($classGroup->classLevel),
            'code' => $classGroup->code,
            'name' => $classGroup->name,
            'capacity' => $classGroup->capacity,
            'status' => $classGroup->status,
            'archived_at' => $classGroup->archivedAt,
            'created_at' => $classGroup->createdAt,
            'updated_at' => $classGroup->updatedAt,
        ];

        if ($this->includeDetails) {
            $data['students'] = array_map(
                static fn (ClassGroupStudentData $student): array => [
                    'id' => $student->id,
                    'student_id' => $student->studentId,
                    'student_no' => $student->studentNo,
                    'student_name' => $student->studentName,
                    'joined_on' => $student->joinedOn,
                    'left_on' => $student->leftOn,
                    'status' => $student->status,
                    'reason' => $student->reason,
                ],
                $classGroup->students,
            );
            $data['homerooms'] = array_map(
                static fn (ClassGroupHomeroomData $homeroom): array => [
                    'id' => $homeroom->id,
                    'employee_id' => $homeroom->employeeId,
                    'employee_name' => $homeroom->employeeName,
                    'assigned_on' => $homeroom->assignedOn,
                    'ended_on' => $homeroom->endedOn,
                    'status' => $homeroom->status,
                    'reason' => $homeroom->reason,
                ],
                $classGroup->homerooms,
            );
        }

        return $data;
    }

    /** @return array{id: string, code: string, name: string} */
    private function reference(ReferenceData $reference): array
    {
        return [
            'id' => $reference->id,
            'code' => $reference->code,
            'name' => $reference->name,
        ];
    }
}
