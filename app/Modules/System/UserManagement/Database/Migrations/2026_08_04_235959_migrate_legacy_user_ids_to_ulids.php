<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const USER_MAP = 'legacy_user_ulid_map';

    private const PASSKEY_MAP = 'legacy_passkey_ulid_map';

    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $idType = strtolower(Schema::getColumnType('users', 'id'));

        if (in_array($idType, ['char', 'string', 'varchar'], true)) {
            return;
        }

        if (! in_array($idType, ['bigint', 'integer'], true)) {
            throw new RuntimeException("Tipe legacy users.id [$idType] tidak didukung.");
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('Upgrade users.id BIGINT ke ULID hanya didukung pada lane MySQL terisolasi.');
        }

        $this->assertTemporaryTablesAreAbsent();
        $this->createUserMap();
        $this->createUlidUsersTable();
        $this->copyUsers();
        $this->createUlidSessionsTable();
        $this->createUlidPasskeysTable();
        $this->assertCopyIsComplete();
        $this->swapTablesAndReferences();
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Identifier ULID tidak boleh dikonversi kembali ke BIGINT. Gunakan restore backup atau forward-fix.',
        );
    }

    private function assertTemporaryTablesAreAbsent(): void
    {
        foreach ([self::USER_MAP, self::PASSKEY_MAP, 'users_ulid', 'sessions_ulid', 'passkeys_ulid'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException("Tabel sementara upgrade [$table] sudah ada. Pulihkan backup sebelum mencoba lagi.");
            }
        }
    }

    private function createUserMap(): void
    {
        Schema::create(self::USER_MAP, static function (Blueprint $table): void {
            $table->unsignedBigInteger('legacy_id')->primary();
            $table->ulid('ulid')->unique();
        });

        DB::table('users')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, static function ($users): void {
                $rows = [];

                foreach ($users as $user) {
                    $rows[] = [
                        'legacy_id' => (int) $user->id,
                        'ulid' => (string) Str::ulid(),
                    ];
                }

                if ($rows !== []) {
                    DB::table(self::USER_MAP)->insert($rows);
                }
            });
    }

    private function createUlidUsersTable(): void
    {
        $hasTwoFactor = Schema::hasColumn('users', 'two_factor_secret');
        $hasStatus = Schema::hasColumn('users', 'status');
        $hasDeletedAt = Schema::hasColumn('users', 'deleted_at');
        $hasLastLoginAt = Schema::hasColumn('users', 'last_login_at');

        Schema::create('users_ulid', static function (Blueprint $table) use ($hasDeletedAt, $hasLastLoginAt, $hasStatus, $hasTwoFactor): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            if ($hasLastLoginAt) {
                $table->timestamp('last_login_at')->nullable()->index();
            }

            $table->string('password');

            if ($hasStatus) {
                $table->string('status', 20)->default('active')->index();
            }

            if ($hasTwoFactor) {
                $table->text('two_factor_secret')->nullable();
                $table->text('two_factor_recovery_codes')->nullable();
                $table->timestamp('two_factor_confirmed_at')->nullable();
            }

            $table->rememberToken();
            $table->timestamps();

            if ($hasDeletedAt) {
                $table->timestamp('deleted_at')->nullable()->index();
            }
        });
    }

    private function copyUsers(): void
    {
        $columns = ['id', 'name', 'email', 'email_verified_at'];
        $select = ['map.ulid', 'users.name', 'users.email', 'users.email_verified_at'];

        foreach (['last_login_at', 'password', 'status', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at', 'remember_token', 'created_at', 'updated_at', 'deleted_at'] as $column) {
            if (Schema::hasColumn('users_ulid', $column)) {
                $columns[] = $column;
                $select[] = "users.$column";
            }
        }

        DB::table('users_ulid')->insertUsing(
            $columns,
            DB::table('users')
                ->join(self::USER_MAP.' as map', 'map.legacy_id', '=', 'users.id')
                ->select($select),
        );
    }

    private function createUlidSessionsTable(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        $orphans = DB::table('sessions')
            ->leftJoin(self::USER_MAP.' as map', 'map.legacy_id', '=', 'sessions.user_id')
            ->whereNotNull('sessions.user_id')
            ->whereNull('map.ulid')
            ->count();

        if ($orphans !== 0) {
            throw new RuntimeException('Upgrade dihentikan karena sessions memiliki user_id tanpa owner.');
        }

        Schema::create('sessions_ulid', static function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignUlid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        DB::table('sessions_ulid')->insertUsing(
            ['id', 'user_id', 'ip_address', 'user_agent', 'payload', 'last_activity'],
            DB::table('sessions')
                ->leftJoin(self::USER_MAP.' as map', 'map.legacy_id', '=', 'sessions.user_id')
                ->select('sessions.id', 'map.ulid', 'sessions.ip_address', 'sessions.user_agent', 'sessions.payload', 'sessions.last_activity'),
        );
    }

    private function createUlidPasskeysTable(): void
    {
        if (! Schema::hasTable('passkeys')) {
            return;
        }

        $orphans = DB::table('passkeys')
            ->leftJoin(self::USER_MAP.' as map', 'map.legacy_id', '=', 'passkeys.user_id')
            ->whereNull('map.ulid')
            ->count();

        if ($orphans !== 0) {
            throw new RuntimeException('Upgrade dihentikan karena passkeys memiliki user_id tanpa owner.');
        }

        Schema::create(self::PASSKEY_MAP, static function (Blueprint $table): void {
            $table->unsignedBigInteger('legacy_id')->primary();
            $table->ulid('ulid')->unique();
        });

        DB::table('passkeys')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, static function ($passkeys): void {
                $rows = [];

                foreach ($passkeys as $passkey) {
                    $rows[] = [
                        'legacy_id' => (int) $passkey->id,
                        'ulid' => (string) Str::ulid(),
                    ];
                }

                if ($rows !== []) {
                    DB::table(self::PASSKEY_MAP)->insert($rows);
                }
            });

        Schema::create('passkeys_ulid', static function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulid('user_id');
            $table->string('name');
            $table->string('credential_id')->unique();
            $table->json('credential');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        DB::table('passkeys_ulid')->insertUsing(
            ['id', 'user_id', 'name', 'credential_id', 'credential', 'last_used_at', 'created_at', 'updated_at'],
            DB::table('passkeys')
                ->join(self::PASSKEY_MAP.' as passkey_map', 'passkey_map.legacy_id', '=', 'passkeys.id')
                ->join(self::USER_MAP.' as user_map', 'user_map.legacy_id', '=', 'passkeys.user_id')
                ->select(
                    'passkey_map.ulid',
                    'user_map.ulid',
                    'passkeys.name',
                    'passkeys.credential_id',
                    'passkeys.credential',
                    'passkeys.last_used_at',
                    'passkeys.created_at',
                    'passkeys.updated_at',
                ),
        );
    }

    private function assertCopyIsComplete(): void
    {
        if (DB::table('users')->count() !== DB::table('users_ulid')->count()) {
            throw new RuntimeException('Jumlah users hasil pemetaan ULID tidak sama dengan source.');
        }

        foreach ([['sessions', 'sessions_ulid'], ['passkeys', 'passkeys_ulid']] as [$source, $target]) {
            if (Schema::hasTable($source) && DB::table($source)->count() !== DB::table($target)->count()) {
                throw new RuntimeException("Jumlah [$target] tidak sama dengan [$source].");
            }
        }
    }

    private function swapTablesAndReferences(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            $this->updateExistingStringReferences();

            if (Schema::hasTable('passkeys_ulid')) {
                Schema::drop('passkeys');
            }

            if (Schema::hasTable('sessions_ulid')) {
                Schema::drop('sessions');
            }

            Schema::drop('users');
            Schema::rename('users_ulid', 'users');

            if (Schema::hasTable('sessions_ulid')) {
                Schema::rename('sessions_ulid', 'sessions');
            }

            if (Schema::hasTable('passkeys_ulid')) {
                Schema::rename('passkeys_ulid', 'passkeys');
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        if (Schema::hasTable('passkeys')) {
            Schema::table('passkeys', static function (Blueprint $table): void {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        Schema::dropIfExists(self::PASSKEY_MAP);
        Schema::dropIfExists(self::USER_MAP);
    }

    private function updateExistingStringReferences(): void
    {
        foreach ([
            ['model_has_roles', 'model_id', 'model_type'],
            ['model_has_permissions', 'model_id', 'model_type'],
            ['media', 'model_id', 'model_type'],
        ] as [$table, $column, $typeColumn]) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::statement(
                "UPDATE `$table` AS target INNER JOIN `".self::USER_MAP.'` AS map '
                ."ON CAST(target.`$column` AS CHAR) = CAST(map.`legacy_id` AS CHAR) "
                ."SET target.`$column` = map.`ulid` WHERE target.`$typeColumn` = ?",
                [User::class],
            );
        }

        foreach ([
            ['audit_logs', 'actor_id'],
            ['system_settings', 'updated_by'],
            ['idempotency_keys', 'actor_id'],
        ] as [$table, $column]) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::statement(
                "UPDATE `$table` AS target INNER JOIN `".self::USER_MAP.'` AS map '
                ."ON CAST(target.`$column` AS CHAR) = CAST(map.`legacy_id` AS CHAR) "
                ."SET target.`$column` = map.`ulid`",
            );
        }
    }
};
