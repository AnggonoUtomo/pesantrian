# Specification: Organization/Organization

## Status

Draft untuk review sebelum implementasi skeleton dan backend slice pertama.

## Tujuan dan Scope

Bangun module `Organization/Organization` sebagai source of truth untuk struktur
organisasi pesantren.

Scope slice awal:

- module skeleton dan metadata,
- table `organization_units` dengan ULID,
- model/persistence minimum untuk unit organisasi,
- read/list unit,
- create/update unit minimum,
- permission `organization.view` dan `organization.manage`,
- audit event/record untuk mutation struktur organisasi bila adapter existing
  siap dipakai tanpa rename `System -> Console`.

## Arsitektur

- Hexagon: `app/Modules/Organization/Organization`.
- Inbound adapter:
  - HTTP API atau web route backend minimum,
  - console route belum dibutuhkan pada slice awal.
- Use case:
  - `ListOrganizationUnits`,
  - `CreateOrganizationUnit`,
  - `UpdateOrganizationUnit`.
- Outbound port:
  - repository unit organisasi jika mutation/read orchestration membutuhkan
    boundary eksplisit.
- Outbound adapter:
  - Eloquent model/repository di Infrastructure.
- Composition root:
  - `app/Modules/Organization/Organization/ServiceProvider.php`.

Domain dibuat hanya untuk rule nyata. Untuk CRUD sederhana, Domain boleh
ditunda sampai ada invariant yang pantas dipisah dari Application/Infrastructure.

## Di Luar Scope

- UI React/Inertia penuh.
- Pemindahan frontend ke `resources/js/modules/*`.
- Rename source `System/*` ke `Console/*`.
- Struktur akademik seperti tahun ajaran, kelas, rombel, atau mata pelajaran.
- Data pegawai, santri, wali, asrama, finance, atau dokumen.
- Multi-yayasan/multi-tenant SaaS.
- Import/export organisasi.

## Contract

### Input

Input create/update unit minimum:

- `name`: string wajib.
- `code`: string wajib, unik.
- `type`: enum/string terbatas untuk tipe unit.
- `parent_id`: ULID nullable untuk hierarchy.
- `status`: active/inactive.
- `location_name`: string nullable.

### Output

Output read/list unit minimum:

- `id`,
- `name`,
- `code`,
- `type`,
- `parent_id`,
- `status`,
- `location_name`,
- timestamp ringkas bila dibutuhkan.

### Failure

- Validasi gagal: `422`.
- Actor tidak punya permission: `403`.
- Unit tidak ditemukan: `404`.
- Duplicate code: `422` atau domain/application error yang dipetakan ke
  response validasi.
- Parent invalid atau parent inactive: ditentukan saat implementasi rule
  hierarchy.

## Data

Table awal: `organization_units`.

Kolom kandidat:

- `id` ULID primary.
- `parent_id` ULID nullable self-reference.
- `code` string unique.
- `name` string.
- `type` string.
- `status` string.
- `location_name` string nullable.
- `metadata` JSON nullable bila benar-benar dibutuhkan.
- `created_at`, `updated_at`.

Traceability awal cukup dari audit mutation dan timestamp. History struktur
organisasi penuh tidak masuk slice pertama.

## Authorization dan Audit

- `organization.view`: membaca daftar/detail unit.
- `organization.manage`: create/update unit.
- Backend permission wajib dicek di controller/use case.
- Audit mutation minimum:
  - `organization.unit_created`,
  - `organization.unit_updated`.

Audit memakai bridge existing sampai vocabulary final `Console/AuditTrail`
diputuskan dalam work item migrasi terpisah.

## UI

UI penuh ditunda karena roadmap Task 3 frontend path decision masih open.

Jika backend slice membutuhkan smoke manual, gunakan route/API response.
Frontend module baru wajib menunggu keputusan `resources/js/modules/*` agar
tidak menambah drift dari `resources/js/pages/System/*`.

## Dependency

Dependency nyata slice awal:

- AccessControl/permission existing untuk authorization.
- AuditLog/AuditTrail bridge untuk audit mutation.

Tidak ada dependency ke AcademicPeriod, StudentLife, HumanResource, Finance,
Document, Communication, Notification, atau Reporting pada slice awal.

## Acceptance Criteria

- [ ] Module `Organization/Organization` dapat dibuat oleh generator dan valid.
- [ ] Migration `organization_units` memakai ULID.
- [ ] Permission `organization.view` dan `organization.manage` terdaftar.
- [ ] Actor dengan permission dapat list/create/update unit minimum.
- [ ] Actor tanpa permission ditolak oleh backend.
- [ ] Duplicate `code` ditolak.
- [ ] Mutation create/update mencatat audit atau event sesuai bridge yang
  tersedia.
- [ ] `php artisan module:validate --no-ansi` lulus.
- [ ] Focused tests Organization lulus.

## Risiko Terbuka

- Vocabulary final AuditTrail masih bridge ke source existing `System/AuditLog`.
- Frontend path decision belum selesai; UI module baru ditunda.
- Rule hierarchy lanjutan seperti cycle prevention, unit closure, dan history
  struktur belum masuk slice awal.
- Tipe unit final dapat berubah setelah discovery kebutuhan pesantren nyata.

