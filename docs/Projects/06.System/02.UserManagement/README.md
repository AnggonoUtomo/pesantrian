# System/UserManagement

## Status

`Final quality checkpoint lulus untuk scope UserManagement; status/delete dan
role assignment menjadi scope lanjutan.`

UserManagement adalah module business pertama setelah AccessControl. Module ini
mengelola lifecycle user di atas tabel `users` starter kit. Authentication,
Passkey, 2FA, dan password flow bawaan Laravel tetap digunakan kembali.

Panduan migration shared/production tersedia pada
[`migration-runbook.md`](migration-runbook.md).

Migration tetap dimiliki module dan didaftarkan oleh `ServiceProvider` melalui
`loadMigrationsFrom()`. Bootstrap database memakai satu entry point global:

```bash
php artisan migrate:fresh --seed
```

UserManagement memiliki `UserManagementSeeder` untuk membuat 10 user dummy pada
development. Seeder tetap berada di module dan dipanggil oleh
`database/seeders/DatabaseSeeder.php` setelah `AccessControlSeeder` sesuai
dependency order. Dengan dua akun baseline, bootstrap global menghasilkan 12
user.

Password user dummy memakai `ACCESS_CONTROL_DUMMY_PASSWORD`. Jika konfigurasi
tersebut kosong, password acak dibuat hanya di runtime development dan tidak
ditulis ke source atau log.

Keputusan boundary dan scope awal sudah disetujui. Implementasi dimulai dari
vertical slice read-only user list sebelum mutation, role assignment, atau
impersonation.

## Boundary Module

UserManagement berada di dalam parent boundary `System`:

```text
app/Modules/System/
├── AccessControl/
├── UserManagement/
├── AuditLog/
└── SystemSetting/
```

Namespace module adalah `App\Modules\System\UserManagement`. Parent `System`
menjadi kelompok capability system, sedangkan UserManagement tetap memiliki
owner, permission identity, contract, test, migration, dan README sendiri.
UserManagement tidak boleh memindahkan lifecycle user ke `app/Models` atau
mengambil alih private implementation AccessControl.

## Urutan Baca

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [ADR boundary](decisions/ADR-0001-USERMANAGEMENT-BOUNDARY.md)
5. [ADR impersonation session dan audit](decisions/ADR-0002-IMPERSONATION-SESSION-AUDIT.md)

## Dokumen Terkait

- [AccessControl](../01.AccessControl/README.md)
- [AccessControl code-flow](../01-1.AccessControl-code-flow/README.md)
- [Folder structure](../../../03-IMPLEMENTATION/03.04-FOLDER-STRUCTURE.md)
- [Module contract](../../../03-IMPLEMENTATION/03.07-MODULES.md)
- [Generator specification](../../../03-IMPLEMENTATION/03.05-GENERATOR-SPEC.md)
- [Project rules](../../../AGENTS.md)

## Kemampuan yang Direncanakan

- daftar dan detail user;
- membuat dan memperbarui data user;
- mengaktifkan atau menonaktifkan user;
- soft delete user;
- assignment role melalui public contract AccessControl;
- flow impersonation dengan permission, alasan, dan perlindungan
  `SuperSystem`.

## Aturan UI UserManagement

- Referensi visual boleh diambil dari `FrontendContoh/users`, tetapi route,
  tipe data, permission, dan payload wajib mengikuti contract module saat ini.
- Add, edit, dan view wajib menggunakan `Dialog` modal. Jangan memakai `Sheet`
  untuk alur tersebut.
- Shortcut mengikuti pola AccessControl. `/` dipakai untuk fokus search dan
  `Shift+A` untuk membuka modal tambah user. `Ctrl/Cmd+K` tetap menjadi milik
  command palette global.
- Tabel wajib memiliki state kosong, error, loading request, action yang aman,
  dan indicator protected user.

## Keputusan Scope Awal

- Status user memakai enum `active`, `inactive`, dan `suspended`.
- Create user awal memakai password lokal/development. Invitation flow ditunda.
- Soft delete hanya berlaku untuk user selain `SuperSystem`.
- Role assignment memakai public contract terpisah `RoleAssignmentCapability`.
- Vertical slice pertama hanya daftar/detail user, search/filter sederhana, dan
  state loading/empty/error.
- Impersonation menjadi increment terakhir dengan ADR session dan audit khusus.

## Cara Verifikasi Awal

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json
```

## Prompt Generator Resmi

Prompt berikut menjadi acuan sebelum menjalankan generator:

```text
Lakukan Project Intake dan Existing Module Inventory terlebih dahulu.
Verifikasi module yang sudah ada dengan module:discover, module:validate,
module:list, dan module:inspect System/AccessControl. Jangan membuat duplicate
module.

Buat module UserManagement pada domain System dengan profile default-v1
menggunakan:

php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json

Setelah hasil dry-run disetujui, jalankan pembuatan aktual dengan --force --yes.
Ikuti struktur DDD-lite, manifest schema, permission schema, public contract,
README module, dan test contract pada dokumentasi repository.
Jangan membuat business logic sebelum increment sebelumnya diverifikasi.
```

Hasil yang diharapkan dari dry-run adalah output JSON dengan code
`MODULE_PREVIEWED`, target `app/Modules/System/UserManagement`, daftar planned
file, dan tidak ada file yang ditulis. Hasil pembuatan aktual diharapkan
memiliki code `MODULE_CREATED` dan struktur skeleton canonical. Generator tidak
membuat migration business, permission final, test behavior, atau frontend
business secara otomatis.

## Status Keputusan

Keputusan scope awal dan ADR impersonation session/audit sudah disetujui. Coding
Task 09 mengikuti key session, public event, route leave, dan redaction pada ADR
khusus tersebut.

## Revision History

| Version | Date | Description |
| --- | --- | --- |
| 1.1 | 2026-08-06 | Menetapkan keputusan scope awal dan vertical slice |
| 1.2 | 2026-08-06 | Menyelesaikan impersonation session, audit event, dan browser flow |
