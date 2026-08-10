<?php

namespace App\Concerns;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, Password|ValidationRule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        $settings = app(SystemSettingReader::class);
        $password = Password::min($settings->integer('security.password.min_length'));
        if ($settings->boolean('security.password.require_mixed_case')) {
            $password->mixedCase();
        }
        if ($settings->boolean('security.password.require_numbers')) {
            $password->numbers();
        }
        if ($settings->boolean('security.password.require_symbols')) {
            $password->symbols();
        }

        return ['required', 'string', $password, 'confirmed'];
    }

    /**
     * Get the validation rules used to validate the current password.
     *
     * @return array<int, Password|ValidationRule|array<mixed>|string>
     */
    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
