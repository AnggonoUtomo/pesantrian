<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Presentation\Support;

final class AuditLogOperatorLabels
{
    /** @var array<string, string> */
    private const ACTIONS = [
        'access_control.role.created' => 'Role ditambahkan',
        'access_control.role.deleted' => 'Role dihapus',
        'access_control.role.permissions_synced' => 'Hak akses role diperbarui',
        'authentication.email_verified' => 'Email akun diverifikasi',
        'authentication.password_reset' => 'Kata sandi diperbarui',
        'authentication.signed_in' => 'Masuk ke akun',
        'authentication.signed_out' => 'Keluar dari akun',
        'system_setting.updated' => 'Pengaturan sistem diperbarui',
        'user.created' => 'Pengguna ditambahkan',
        'user.deleted' => 'Pengguna diarsipkan',
        'user.force_deleted' => 'Pengguna dihapus permanen',
        'user.impersonation_ended' => 'Penyamaran pengguna diakhiri',
        'user.impersonation_started' => 'Penyamaran pengguna dimulai',
        'user.restored' => 'Pengguna dipulihkan',
        'user.role_assigned' => 'Role pengguna diperbarui',
        'user.status_changed' => 'Status pengguna diperbarui',
        'user.updated' => 'Data pengguna diperbarui',
    ];

    /** @var array<string, string> */
    private const SUBJECTS = [
        'account' => 'Akun',
        'role' => 'Role',
        'system_setting' => 'Pengaturan sistem',
        'user' => 'Pengguna',
    ];

    /** @var array<string, string> */
    private const MODULES = [
        'AccessControl' => 'Kontrol akses',
        'Authentication' => 'Autentikasi',
        'SystemSetting' => 'Pengaturan sistem',
        'UserManagement' => 'Manajemen pengguna',
    ];

    /** @var array<string, string> */
    private const HISTORICAL_SETTING_CATEGORIES = [
        'api.' => 'API',
        'security.password.' => 'Password',
        'security.session.' => 'Sesi',
        'mail.' => 'Email',
        'pagination.' => 'Pagination',
        'branding.' => 'Identitas aplikasi',
        'monitoring.' => 'Monitoring',
        'operations.' => 'Operasional',
    ];

    /** @var array<string, string> */
    private const HISTORICAL_SETTING_LABELS = [
        'api.rate_limit.per_minute' => 'Batas request per actor dan endpoint setiap menit.',
        'api.idempotency.retention_hours' => 'Masa simpan response idempotent dalam jam.',
        'security.session.idle_minutes' => 'Batas session tanpa aktivitas dalam menit.',
        'security.session.absolute_hours' => 'Batas maksimal umur session dalam jam.',
        'security.password.min_length' => 'Panjang minimum password.',
        'security.password.require_mixed_case' => 'Password wajib memakai huruf besar dan kecil.',
        'security.password.require_numbers' => 'Password wajib memakai angka.',
        'security.password.require_symbols' => 'Password wajib memakai simbol.',
        'pagination.per_page_options' => 'Pilihan jumlah data per halaman yang tersedia pada daftar.',
        'pagination.default_per_page' => 'Jumlah data per halaman saat pengguna belum memilih ukuran.',
        'branding.app_name' => 'Nama aplikasi global.',
        'branding.logo_path' => 'Path logo lokal default.',
        'branding.favicon_path' => 'Path favicon lokal default.',
        'branding.palette_default' => 'Palette default ketika user belum memilih.',
        'branding.typography_default' => 'Typography default aplikasi.',
        'branding.appearance_default' => 'Mode warna default ketika user belum memilih.',
        'monitoring.external_enabled' => 'Flag integrasi monitoring eksternal.',
        'operations.rto_hours' => 'Target recovery time dalam jam.',
        'operations.rpo_hours' => 'Target recovery point dalam jam.',
        'mail.mailer' => 'Mailer untuk email sistem.',
        'mail.host' => 'Host SMTP.',
        'mail.port' => 'Port SMTP.',
        'mail.username' => 'Username SMTP bila diperlukan.',
        'mail.password' => 'Password SMTP terenkripsi.',
        'mail.from_address' => 'Alamat pengirim email.',
        'mail.from_name' => 'Nama pengirim email.',
    ];

    public static function action(string $action): string
    {
        return self::ACTIONS[$action] ?? 'Aktivitas tercatat';
    }

    public static function subject(string $subjectType): string
    {
        return self::SUBJECTS[$subjectType] ?? 'Data terkait';
    }

    public static function module(string $module): string
    {
        return self::MODULES[$module] ?? 'Sistem';
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{browser: string|null, ipAddress: string|null}|null
     */
    public static function securityContext(array $metadata): ?array
    {
        $browser = is_string($metadata['browser'] ?? null) ? $metadata['browser'] : null;
        $ipAddress = is_string($metadata['ip_address'] ?? null)
            ? self::maskIpAddress($metadata['ip_address'])
            : null;

        return $browser === null && $ipAddress === null
            ? null
            : ['browser' => $browser, 'ipAddress' => $ipAddress];
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{category: string, setting: string, beforeValue: string, afterValue: string}|null
     */
    public static function settingChange(array $metadata): ?array
    {
        $category = $metadata['setting_category'] ?? null;
        $setting = $metadata['setting_label'] ?? null;

        if (is_string($category) && is_string($setting) && $category !== '' && $setting !== '') {
            return self::settingChangePayload($category, $setting, $metadata);
        }

        return self::historicalSettingChange($metadata);
    }

    /**
     * Mengubah record audit lama yang hanya memiliki key menjadi label operator.
     *
     * @param  array<string, mixed>  $metadata
     * @return array{category: string, setting: string, beforeValue: string, afterValue: string}|null
     */
    private static function historicalSettingChange(array $metadata): ?array
    {
        $key = $metadata['setting_key'] ?? null;

        if (! is_string($key) || ! isset(self::HISTORICAL_SETTING_LABELS[$key])) {
            return null;
        }

        foreach (self::HISTORICAL_SETTING_CATEGORIES as $prefix => $category) {
            if (str_starts_with($key, $prefix)) {
                return self::settingChangePayload($category, self::HISTORICAL_SETTING_LABELS[$key], $metadata);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{category: string, setting: string, beforeValue: string, afterValue: string}
     */
    private static function settingChangePayload(string $category, string $setting, array $metadata): array
    {
        return [
            'category' => $category,
            'setting' => $setting,
            'beforeValue' => self::settingValue($metadata['before_value'] ?? null),
            'afterValue' => self::settingValue($metadata['after_value'] ?? null),
        ];
    }

    private static function settingValue(mixed $value): string
    {
        return match (true) {
            $value === '[REDACTED]' => 'Disamarkan',
            $value === '[FILTERED]' => 'Disaring',
            $value === '[TRUNCATED]' => 'Dipangkas',
            $value === null => 'Belum diatur',
            $value === true => 'Aktif',
            $value === false => 'Tidak aktif',
            is_array($value) => $value === []
                ? '-'
                : implode(', ', array_map(self::settingValue(...), $value)),
            is_string($value) || is_int($value) || is_float($value) => (string) $value,
            default => '-',
        };
    }

    private static function maskIpAddress(string $ipAddress): ?string
    {
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $segments = explode('.', $ipAddress);

            return sprintf('%s.%s.%s.xxx', $segments[0], $segments[1], $segments[2]);
        }

        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return mb_substr($ipAddress, 0, 7).'…';
        }

        return null;
    }
}
