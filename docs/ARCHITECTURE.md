# Arsitektur

## Struktur

- `packages/StarterKit`: framework dan kemampuan yang dapat digunakan ulang.
- `app/Modules/{Domain}/{SubModule}`: modul aplikasi.
- `resources/js`: frontend Inertia/React.

Arsitektur memakai DDD-lite Modular Monolith. Modul tidak boleh memakai class
konkret milik modul lain. Komunikasi lintas modul memakai public contract, DTO,
capability, atau event bila memang ada consumer nyata.

## Aturan penting

- `module.json` adalah manifest deklaratif; `module.php` adalah konfigurasi
  runtime.
- Migration dan seeder tetap dimiliki modulnya. Provider modul memuat migration;
  `DatabaseSeeder` menjadi entry point bootstrap global.
- Primary key dan foreign key memakai ULID.
- Backend menangani authorization; frontend hanya menyembunyikan atau
  menampilkan UI sesuai permission.
- Controller tipis: validasi, query, dan aturan bisnis berada pada boundary
  yang memilikinya.
- Wayfinder dan Laravel Boost tidak digunakan. Route frontend memakai Ziggy.

## Modul System

Urutan dependency baseline: `AccessControl` -> `UserManagement` -> `AuditLog`
-> `SystemSetting`. UserManagement hanya memakai public capability
AccessControl, bukan implementasi privatnya.
