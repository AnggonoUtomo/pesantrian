<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Resources;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzTargetData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TahfidzTargetData */
final class TahfidzTargetResource extends JsonResource
{
    public function __construct(TahfidzTargetData $resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var TahfidzTargetData $target */
        $target = $this->resource;

        return [
            'id' => $target->id,
            'student_id' => $target->studentId,
            'student_no' => $target->studentNo,
            'student_name' => $target->studentName,
            'academic_period_id' => $target->academicPeriodId,
            'period_label' => $target->periodLabel,
            'target_juz' => $target->targetJuz,
            'target_surah' => $target->targetSurah,
            'target_ayah_from' => $target->targetAyahFrom,
            'target_ayah_to' => $target->targetAyahTo,
            'target_note' => $target->targetNote,
            'status' => $target->status,
        ];
    }
}
