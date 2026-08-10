<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Presentation\Requests;

use App\Modules\System\AuditLog\Application\DTO\AuditLogFilter;
use App\Modules\System\SystemSetting\Application\Contracts\SystemRuntimeSettings;
use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AuditLogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in(app(SystemRuntimeSettings::class)->current()->paginationPerPageOptions)],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    public function toFilter(): AuditLogFilter
    {
        $validated = $this->validated();
        $dateFrom = isset($validated['date_from'])
            ? new DateTimeImmutable((string) $validated['date_from'].' 00:00:00')
            : null;
        $dateTo = isset($validated['date_to'])
            ? new DateTimeImmutable((string) $validated['date_to'].' 23:59:59')
            : null;

        return new AuditLogFilter(
            search: $this->optionalString($validated, 'search'),
            module: $this->optionalString($validated, 'module'),
            action: $this->optionalString($validated, 'action'),
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            page: (int) ($validated['page'] ?? 1),
            perPage: (int) ($validated['per_page'] ?? app(SystemRuntimeSettings::class)->current()->paginationDefaultPerPage),
            sortDirection: $validated['sort_direction'] ?? 'desc',
        );
    }

    /** @param array<string, mixed> $validated */
    private function optionalString(array $validated, string $key): ?string
    {
        if (! isset($validated[$key]) || ! is_string($validated[$key])) {
            return null;
        }

        $value = trim($validated[$key]);

        return $value === '' ? null : $value;
    }
}
