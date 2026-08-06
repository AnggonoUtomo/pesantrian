<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

final readonly class SettingValidationReport
{
    /**
     * @param  list<string>  $missing
     * @param  list<string>  $invalid
     * @param  list<string>  $unknown
     */
    public function __construct(
        public array $missing,
        public array $invalid,
        public array $unknown,
    ) {}

    public function isValid(): bool
    {
        return $this->missing === [] && $this->invalid === [] && $this->unknown === [];
    }

    /** @return array{valid: bool, missing: list<string>, invalid: list<string>, unknown: list<string>} */
    public function toArray(): array
    {
        return [
            'valid' => $this->isValid(),
            'missing' => $this->missing,
            'invalid' => $this->invalid,
            'unknown' => $this->unknown,
        ];
    }
}
