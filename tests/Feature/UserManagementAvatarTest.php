<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function avatarEditor(): User
{
    $actor = User::factory()->create();
    $actor->givePermissionTo(Permission::create(['name' => 'user.update', 'guard_name' => 'web']));

    return $actor;
}

it('mengunggah dan mengganti avatar pada collection tunggal private', function (): void {
    Storage::fake('local');
    $actor = avatarEditor();
    $target = User::factory()->create();

    $this->actingAs($actor)->post(route('system.users.avatar.update', $target), [
        'avatar' => UploadedFile::fake()->image('avatar-awal.png'),
    ])->assertRedirect();

    $first = $target->fresh()->getFirstMedia('avatar');
    expect($first)->not->toBeNull();

    $this->actingAs($actor)->post(route('system.users.avatar.update', $target), [
        'avatar' => UploadedFile::fake()->image('avatar-pengganti.webp'),
    ])->assertRedirect();

    expect($target->fresh()->getMedia('avatar'))->toHaveCount(1)
        ->and($target->fresh()->getFirstMedia('avatar')->id)->not->toBe($first->id);
});

it('menolak avatar dengan tipe atau ukuran tidak valid', function (): void {
    $actor = avatarEditor();
    $target = User::factory()->create();

    $this->actingAs($actor)->post(route('system.users.avatar.update', $target), [
        'avatar' => UploadedFile::fake()->create('avatar.pdf', 100, 'application/pdf'),
    ])->assertInvalid('avatar');

    $this->actingAs($actor)->post(route('system.users.avatar.update', $target), [
        'avatar' => UploadedFile::fake()->image('avatar-besar.png')->size(2049),
    ])->assertInvalid('avatar');

    expect($target->fresh()->getFirstMedia('avatar'))->toBeNull();
});

it('menghapus avatar dengan authorization update', function (): void {
    Storage::fake('local');
    $actor = avatarEditor();
    $target = User::factory()->create();
    $target->addMedia(UploadedFile::fake()->image('avatar.png'))->toMediaCollection('avatar');

    $this->actingAs($actor)->delete(route('system.users.avatar.delete', $target))->assertRedirect();

    expect($target->fresh()->getFirstMedia('avatar'))->toBeNull();
});
