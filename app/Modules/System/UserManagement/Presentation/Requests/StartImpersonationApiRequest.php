<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use App\Modules\System\UserManagement\Application\DTO\ImpersonationRequestData;
use App\Modules\System\UserManagement\Domain\Exceptions\ImpersonationReasonRequired;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class StartImpersonationApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function toData(string $targetUserId, string $correlationId): ImpersonationRequestData
    {
        return new ImpersonationRequestData(
            targetUserId: $targetUserId,
            reason: (string) $this->validated('reason'),
            correlationId: $correlationId,
        );
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->has('reason')) {
            throw new ImpersonationReasonRequired;
        }

        parent::failedValidation($validator);
    }
}
