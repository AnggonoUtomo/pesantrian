# Implementation Plan: System/UserManagement

## Status

`Task 12 Open Risk UI selesai; mutation status, soft delete, dan role assignment
sudah memiliki vertical slice frontend dan backend yang terverifikasi`.

## Architecture

Alur utama yang direncanakan:

```text
Route middleware
    -> Policy UserManagement
    -> Controller tipis
    -> FormRequest
    -> Application Action/Query
    -> public AuthorizationCapability AccessControl
    -> UserManagement Domain rule
    -> Infrastructure repository/model
    -> DTO/Resource
    -> Inertia page atau response
```

UserManagement memiliki ownership lifecycle user. AccessControl tetap owner
authorization dan role/permission persistence. Tidak ada concrete dependency
lintas module.

## Urutan Increment

1. **Prompt, inventory, dan dry-run generator**: gunakan prompt standar,
   verifikasi module existing dengan `module:inspect`, dan pastikan target `System/UserManagement`
   belum duplicate.
2. **Pembuatan skeleton generator**: setelah dry-run disetujui, jalankan
   `module:make` dengan `--force --yes --json`; verifikasi manifest, provider,
   path, namespace, dan struktur canonical.
3. **Permission identity dan public contract**: tetapkan permission dan contract
   lifecycle user; role assignment memakai `RoleAssignmentCapability` terpisah.
4. **Vertical slice read-only**: implementasikan user list/detail, search/filter,
   state UI, authorization visibility, dan browser flow.
5. **Domain boundary**: gunakan enum status `active`, `inactive`, `suspended`,
   serta rule soft delete untuk user selain `SuperSystem`.
6. **Application boundary**: buat action/query/DTO untuk list, create, update,
   status, soft delete, role assignment, dan impersonation.
7. **Infrastructure**: siapkan migration additive, model/repository, factory,
   dan seeder dengan ULID. Migration tetap module-local melalui
   `loadMigrationsFrom()`, sedangkan seeder module dipanggil oleh global
   `DatabaseSeeder` untuk `php artisan migrate:fresh --seed`.
8. **Authorization dan security**: policy, middleware, reason validation,
   protected `SuperSystem`, session separation, dan redaction.
9. **Presentation dan routes**: controller tipis, FormRequest, resource, dan
   route module.
10. **Frontend mutation slice**: page user list/detail, dialog mutation, role
   assignment, state loading/empty/error, Ziggy, permission visibility,
   responsive layout, dan browser accessibility test.
11. **Quality gate dan documentation evidence**: update README, task, execution
    log, test, CI, dan open risk.

## Prompt Pelaksanaan Generator

```text
Lakukan Project Intake dan Existing Module Inventory terlebih dahulu.
Verifikasi module yang sudah ada dengan module:discover, module:validate,
module:list, dan module:inspect System/AccessControl. Jangan membuat duplicate
module.

Buat module UserManagement pada domain System dengan profile default-v1
menggunakan:

php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json

Setelah hasil dry-run disetujui, jalankan pembuatan aktual dengan:

php artisan module:make UserManagement --domain=System --profile=default-v1 --force --yes --json

Ikuti struktur DDD-lite, manifest schema, permission schema, public contract,
README module, dan test contract. Generator hanya membuat skeleton. Jangan
membuat business logic sebelum skeleton lulus discovery dan validation.
```

## Hasil yang Diharapkan dari Generator

### Dry-run

- code `MODULE_PREVIEWED`;
- target `app/Modules/System/UserManagement`;
- planned file dan directory canonical terlihat;
- tidak ada perubahan filesystem;
- AccessControl tetap valid dan tidak tertimpa.

### Pembuatan aktual

- code `MODULE_CREATED`;
- module berada pada parent boundary `System`;
- `module.json`, `module.php`, `permissions.php`, provider, README, route entry
  point, dan struktur DDD-lite tersedia;
- tidak ada business logic palsu, secret, atau dependency private
  AccessControl;
- module dapat ditemukan dan divalidasi.

## First Vertical Slice yang Disarankan

Slice pertama yang paling aman adalah:

```text
User list
    -> permission user.view
    -> filter/search sederhana
    -> empty/loading/error state
    -> detail link
```

Setelah slice baca lulus, lanjut create/update, status, role assignment melalui
`RoleAssignmentCapability`, lalu impersonation. Mutation berisiko tidak dibuat
bersamaan dengan list pertama.

## Adaptasi Frontend dari Referensi

Referensi `FrontendContoh/users` digunakan untuk mempelajari struktur tabel,
summary card, search, action, shortcut, dan alur impersonation. Implementasi
nyata tetap memakai contract module `System/UserManagement`.

Keputusan UI:

- add, edit, view, dan impersonation menggunakan Dialog modal, bukan Sheet;
- route memakai Ziggy `system.users.*`;
- `FrontendContoh` tidak diubah dan tidak ikut dikomit;
- role assignment, avatar, archive, dan restore tidak ditampilkan sebelum
  contract backend tersedia;
- `/` fokus search, `Shift+A` membuka modal create, dan `Ctrl/Cmd+K` tetap
  dimiliki command palette global.

## Dependency dan Boundary

- AccessControl: public authorization capability, `RoleAssignmentCapability`,
  dan `RoleCatalogCapability`.
- Laravel starter kit: authentication, password, Passkey, 2FA, dan User model.
- UserManagement: lifecycle user, status, soft delete, route, page, dan policy
  resource user.
- AuditLog: akan menerima event/audit contract setelah module tersedia.

Jika role assignment contract belum tersedia dari AccessControl, coding task
role assignment harus berhenti dan keputusan contract harus dibuat terlebih
dahulu.

Pola eksekusi mengikuti ADR-0003. UserManagement memakai Application Action
untuk write, Application Query dan DTO untuk read, serta Domain Event internal
untuk impersonation. Command Bus, Integration Event, Queue/Job, Facade, dan
Shared Kernel belum menjadi dependency module.

## Fondasi Enterprise

Setiap fondasi berikut wajib memiliki status dan alasan sebelum implementasi
baru dimulai. Status saat ini harus tetap selaras dengan specification, tasks,
README, test, dan ADR terkait.

| Fondasi | Status saat ini | Batasan implementasi |
| --- | --- | --- |
| Contract / Interface | `implemented` | Gunakan contract publik AccessControl untuk authorization dan role assignment; jangan memakai class internal module lain. |
| Domain Event | `implemented` terbatas | Event impersonation boleh tetap menjadi fakta domain internal; payload harus typed, aman, dan tidak memuat credential. |
| Application Event | `planned` | Tambahkan hanya jika koordinasi beberapa handler memang diperlukan dan failure handler dapat diuji. |
| Integration Event | `planned` | Menunggu consumer seperti AuditLog; event harus versioned, memiliki event ID/correlation ID, dan payload tersanitasi. |
| Command | `planned` | Action saat ini dapat dievolusikan menjadi Command + Handler setelah ada kebutuhan idempotency, audit, atau async; perubahan wajib memiliki ADR/increment. |
| Query / Read Contract | `implemented` | Read list/detail memakai Query dan DTO; query tidak boleh mengubah state atau mengembalikan model persistence secara langsung. |
| Shared Kernel | `not applicable` | Jangan membuat shared model; gunakan contract/value object publik yang memiliki owner dan consumer jelas bila kebutuhan lintas module muncul. |
| Facade / Module API | `implemented` terbatas | Public capability adalah API module; facade baru boleh ditambah bila consumer dan compatibility contract sudah jelas. |
| Queue / Job (CQRS) | `planned` | Flow sinkron tetap menjadi default; Job baru wajib mendefinisikan retry, idempotency, actor/correlation, dan failure handling. |

Implementasi tidak boleh menambahkan bus, handler, event consumer, facade, atau
job hanya karena pola tersebut tersedia. Perubahan status harus disertai
acceptance criteria, focused test, verification command, dan rencana rollback.

## Risiko

| Risiko | Mitigasi |
| --- | --- |
| Tabel `users` belum memiliki status dan `deleted_at` | Risiko ditransisikan ke Task 06; gunakan migration additive setelah keputusan field disetujui, lalu uji fresh dan upgrade |
| Assignment role mencampur private AccessControl | Tambahkan public contract/capability dan architecture test |
| Impersonation membuka akses ke `SuperSystem` | Policy dan application action melakukan penolakan ganda |
| Invitation flow ditunda | Gunakan password lokal/development pada increment create awal; jangan membuat email invitation |
| UserManagement terlalu besar pada increment awal | Mulai dari read-only list vertical slice |
| AuditLog belum tersedia | Sediakan event/contract boundary; jangan membuat audit storage kedua |
| Frontend hanya selesai sebagai mock | Wajib browser flow sampai response backend |

## Rollback

- Skeleton generator dapat dihapus sebelum business implementation jika belum
  memiliki data atau route yang dipakai.
- Migration additive tidak boleh dihapus setelah dipakai shared environment;
  gunakan forward fix atau rollback runbook.
- Permission baru dapat dinonaktifkan melalui module status/registry setelah
  mekanisme lifecycle tersedia; jangan menghapus permission identity tanpa
  keputusan migrasi.
- Impersonation harus memiliki exit path dan pemulihan session actor asli.
- Setiap increment memiliki commit terpisah setelah user meminta commit.

## Definition of Ready

Coding UserManagement siap dimulai setelah:

- boundary dan keputusan scope awal disetujui;
- dry-run generator menunjukkan target yang benar;
- prompt generator dan hasil yang diharapkan sudah ditinjau;
- `RoleAssignmentCapability` disepakati sebagai contract role assignment dan
  harus tersedia dari AccessControl sebelum assignment diaktifkan;
- enum status dan aturan soft delete sudah dicatat;
- permission identity final tersedia;
- acceptance criteria dan focused test disetujui.

## Revision History

| Version | Date | Description |
| --- | --- | --- |
| 1.1 | 2026-08-06 | Menetapkan urutan vertical slice dan keputusan scope |
