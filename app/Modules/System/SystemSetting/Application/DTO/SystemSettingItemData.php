<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

final readonly class SystemSettingItemData
{
    /**
     * @param  int|bool|string|list<int>|null  $value
     * @param  int|bool|string|list<int>|null  $defaultValue
     * @param  list<string>  $options
     */
    public function __construct(
        public string $key,
        public string $type,
        public int|bool|string|array|null $value,
        public int|bool|string|array|null $defaultValue,
        public string $description,
        public string $source,
        public ?string $updatedAt,
        public ?int $min,
        public ?int $max,
        public array $options,
        public bool $nullable,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'value' => $this->value,
            'default_value' => $this->defaultValue,
            'description' => $this->description,
            'source' => $this->source,
            'updated_at' => $this->updatedAt,
            'min' => $this->min,
            'max' => $this->max,
            'options' => $this->options,
            'nullable' => $this->nullable,
        ];
    }
}
