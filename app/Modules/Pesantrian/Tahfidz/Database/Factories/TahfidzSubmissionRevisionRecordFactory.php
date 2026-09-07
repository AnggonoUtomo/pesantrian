<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Database\Factories;

use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRevisionRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TahfidzSubmissionRevisionRecord>
 */
final class TahfidzSubmissionRevisionRecordFactory extends Factory
{
    protected $model = TahfidzSubmissionRevisionRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'submission_id' => null,
            'reason' => 'Koreksi catatan setoran.',
            'changed_by' => null,
            'changed_at' => now(),
            'summary' => null,
        ];
    }
}
