<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (DB::connection()->getDriverName() !== 'mysql') {
    throw new RuntimeException('Verifier upgrade legacy wajib memakai MySQL.');
}

$userCount = DB::table('users')
    ->whereIn('email', ['legacy-owner@example.test', 'legacy-member@example.test'])
    ->count();

if ($userCount !== 2) {
    throw new RuntimeException('Jumlah user legacy setelah upgrade tidak sesuai.');
}

$invalidUserIds = DB::table('users')
    ->whereIn('email', ['legacy-owner@example.test', 'legacy-member@example.test'])
    ->pluck('id')
    ->filter(static fn (mixed $id): bool => ! is_string($id) || preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id) !== 1);

if ($invalidUserIds->isNotEmpty()) {
    throw new RuntimeException('Pemetaan users.id ke ULID tidak valid.');
}

$sessionOwnerCount = DB::table('sessions')
    ->join('users', 'users.id', '=', 'sessions.user_id')
    ->where('sessions.id', 'legacy-session-fixture')
    ->where('users.email', 'legacy-owner@example.test')
    ->count();

if ($sessionOwnerCount !== 1) {
    throw new RuntimeException('Relasi session legacy tidak terpelihara.');
}

$passkey = DB::table('passkeys')
    ->join('users', 'users.id', '=', 'passkeys.user_id')
    ->where('passkeys.credential_id', 'legacy-credential-fixture')
    ->where('users.email', 'legacy-member@example.test')
    ->select('passkeys.id')
    ->first();

if ($passkey === null || ! is_string($passkey->id) || preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $passkey->id) !== 1) {
    throw new RuntimeException('Relasi atau identifier Passkey legacy tidak terpelihara.');
}

foreach (['roles', 'permissions', 'audit_logs', 'system_settings', 'idempotency_keys'] as $table) {
    if (! Schema::hasTable($table)) {
        throw new RuntimeException("Schema module [$table] belum tersedia setelah upgrade.");
    }
}

foreach (['legacy_user_ulid_map', 'legacy_passkey_ulid_map', 'users_ulid', 'sessions_ulid', 'passkeys_ulid'] as $table) {
    if (Schema::hasTable($table)) {
        throw new RuntimeException("Tabel sementara [$table] tertinggal setelah upgrade.");
    }
}

fwrite(STDOUT, "Upgrade legacy terverifikasi: 2 user, 1 session, dan 1 Passkey terpelihara sebagai ULID.\n");
