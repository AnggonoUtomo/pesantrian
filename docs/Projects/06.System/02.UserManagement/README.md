# System/UserManagement

## Status

`Task 12 Open Risk UI selesai untuk scope UserManagement saat ini; UI create,
edit, detail, impersonation, status, soft delete, dan role assignment sudah
diverifikasi.`

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
- [Module communication and execution](../../../03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [Project rules](../../../AGENTS.md)

## Kemampuan yang Direncanakan

- daftar dan detail user;
- membuat dan memperbarui data user;
- mengaktifkan atau menonaktifkan user;
- soft delete user;
- assignment role melalui public contract AccessControl;
- flow impersonation dengan permission, alasan, dan perlindungan
  `SuperSystem`.

## Scope Lanjutan yang Belum Dibuat

Lima scope berikut dicatat sebagai pekerjaan lanjutan. Item ini belum dianggap
selesai dan tidak boleh dibuat hanya dengan menambahkan tombol pada frontend.
Setiap item harus memiliki specification, acceptance criteria, focused test,
permission atau contract yang sesuai, browser flow bila memiliki UI, dan
execution evidence sebelum ditandai selesai.

1. **Restore user** — memulihkan user yang sudah di-soft-delete. Wajib memiliki
   policy, permission, aturan untuk `SuperSystem`, audit event, dan test untuk
   user yang masih aktif serta user yang sudah dihapus.
2. **Invitation email** — membuat user melalui email undangan, token sekali
   pakai, masa berlaku, dan alur menetapkan password. Jangan mencatat token atau
   password ke log. Integrasi email perlu diuji dengan mail fake dan failure
   path.
3. **Role revoke dan multi-role management** — mencabut role tertentu dan
   mengelola beberapa role user secara atomik. Perlu aturan agar role terakhir,
   role `SuperSystem`, dan perubahan tanpa permission tidak dapat disalahgunakan.
4. **AuditLog consumer production** — membuat consumer production untuk event
   lifecycle, role, dan impersonation. Consumer harus memiliki schema event,
   correlation ID, redaction, idempotency, retry, serta failure handling.
5. **Migration shared/production** — melakukan rehearsal migration pada salinan
   database, backup/restore test, verifikasi lock dan downtime, serta prosedur
   rollback. Workspace lokal tidak dapat membuktikan deployment production tanpa
   database nyata, backup, dan persetujuan operator.

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

### Baseline warna module

UserManagement mengikuti baseline visual AccessControl dan tidak membuat surface
sendiri:

- Light: topnav, sidebar, dan card utama putih bersih; subcard sedikit lebih
  gelap dengan abu-abu netral.
- Dark: topnav, sidebar, dan card utama mengikuti palette aktif; subcard sedikit
  lebih gelap dari card utama.
- Accent summary card, icon, badge, border, hover, dan focus mengikuti fungsi
  user. Token visual berasal dari `resources/css/app.css`.
- Tabel, modal, search, dan empty state harus memakai surface serta state yang
  sama agar UserManagement menjadi contoh module System yang konsisten.

## Keputusan Scope Awal

- Status user memakai enum `active`, `inactive`, dan `suspended`.
- Create user awal memakai password lokal/development. Invitation flow ditunda.
- Soft delete hanya berlaku untuk user selain `SuperSystem`.
- Role assignment memakai public contract terpisah `RoleAssignmentCapability`.
- Vertical slice pertama hanya daftar/detail user, search/filter sederhana, dan
  state loading/empty/error.
- Impersonation menjadi increment terakhir dengan ADR session dan audit khusus.

## Pola komunikasi module

UserManagement memakai `UserRepository` dan `ImpersonationSession` sebagai
contract internal/application boundary. Untuk lintas module, UserManagement
memakai `AuthorizationCapability`, `RoleAssignmentCapability`, dan
`RoleCatalogCapability` milik AccessControl.

`ListUsers` dan `GetUser` adalah Query internal dengan `UserData` typed.
Mutation memakai Application Action. Event impersonation saat ini adalah Domain
Event synchronous; belum ada Application Event, Integration Event, Command Bus,
Queue/Job, Facade, atau Shared Kernel domain.

Fondasi enterprise wajib tetap dipetakan pada setiap increment UserManagement.
Status saat ini menjadi acuan: Contract/Interface dan Query/Read Contract
`implemented`; Domain Event `implemented terbatas` untuk impersonation;
Application Event, Integration Event, Command Bus, Queue/Job, Facade, dan
Shared Kernel `planned` atau `not applicable` sesuai consumer nyata. Perubahan
status wajib memperbarui specification, plan, tasks, test, dan ADR terkait.

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
| 1.3 | 2026-08-06 | Mencatat lima scope lanjutan yang belum dibuat |
