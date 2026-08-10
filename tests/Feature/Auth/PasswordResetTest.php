<?php

use App\Models\User;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('password cannot be reset with invalid token', function () {
    $user = User::factory()->create();

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password reset mengikuti kebijakan password dari SystemSetting', function () {
    foreach ([
        ['key' => 'security.password.min_length', 'value' => 12, 'type' => 'integer'],
        ['key' => 'security.password.require_mixed_case', 'value' => true, 'type' => 'boolean'],
        ['key' => 'security.password.require_numbers', 'value' => true, 'type' => 'boolean'],
        ['key' => 'security.password.require_symbols', 'value' => false, 'type' => 'boolean'],
    ] as $setting) {
        SystemSettingRecord::query()->create([
            ...$setting,
            'value' => json_encode($setting['value'], JSON_THROW_ON_ERROR),
            'description' => 'Kebijakan password untuk pengujian.',
            'is_sensitive' => false,
        ]);
    }

    Notification::fake();
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $weakPassword = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'lowercase1234',
            'password_confirmation' => 'lowercase1234',
        ]);

        $weakPassword->assertSessionHasErrors('password');

        $strongPassword = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'StrongPassword12',
            'password_confirmation' => 'StrongPassword12',
        ]);

        $strongPassword
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});
