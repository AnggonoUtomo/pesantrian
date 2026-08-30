<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Domain\Services;

final class StudentAdmissionLifecycle
{
    /** @var array<string, array<int, string>> */
    private const TRANSITIONS = [
        'draft' => ['submitted', 'cancelled'],
        'submitted' => ['verified', 'cancelled'],
        'verified' => ['accepted', 'rejected', 'cancelled'],
        'accepted' => [],
        'rejected' => [],
        'cancelled' => [],
    ];

    public function canTransition(string $currentStatus, string $targetStatus): bool
    {
        return in_array($targetStatus, self::TRANSITIONS[$currentStatus] ?? [], true);
    }

    public function isTerminal(string $status): bool
    {
        return in_array($status, ['accepted', 'rejected', 'cancelled'], true);
    }
}
