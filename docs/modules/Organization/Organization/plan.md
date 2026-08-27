# Implementation Plan: Organization/Organization

## Scope

Pekerjaan ini memulai module `Organization/Organization` sebagai foundation
bisnis untuk struktur yayasan/pesantren/unit/lokasi/hierarchy.

Increment awal berhenti pada dokumentasi, skeleton module, dan backend slice
minimum tanpa UI baru.

## Increment 1: Dokumentasi module

- Perubahan:
  - `docs/modules/Organization/Organization/README.md`
  - `docs/modules/Organization/Organization/specification.md`
  - `docs/modules/Organization/Organization/plan.md`
  - `docs/modules/Organization/Organization/tasks.md`
- Dependency: module-roadmap Phase 0 selesai.
- Acceptance: scope, non-scope, acceptance criteria, dan verifikasi tertulis.
- Verifikasi: `git diff --check`.

## Increment 2: Skeleton module

- Perubahan:
  - generate `app/Modules/Organization/Organization/` dengan generator.
  - review `module.json`, `module.php`, `permissions.php`,
    `ServiceProvider.php`, `README.md`, dan `Routes/*`.
- Dependency: Increment 1 direview.
- Acceptance:
  - target path `app/Modules/Organization/Organization`,
  - tidak ada folder kosong placeholder,
  - manifest valid.
- Verifikasi:
  - `php artisan module:make Organization Organization --dry-run --json --no-ansi`
  - `php artisan module:make Organization Organization --force --yes --no-ansi`
  - `php artisan module:validate --no-ansi`
  - `git diff --check`

## Increment 3: Data foundation

- Perubahan:
  - migration `organization_units`,
  - model/persistence minimum,
  - permission identity awal.
- Dependency: Increment 2.
- Acceptance:
  - table memakai ULID,
  - unique `code`,
  - optional parent self-reference aman,
  - permission `organization.view` dan `organization.manage` tersedia.
- Verifikasi:
  - focused migration/model tests,
  - `php artisan module:validate --no-ansi`.

## Increment 4: Backend read/list dan create/update minimum

- Perubahan:
  - use case/query/action minimum,
  - controller/request/resource atau API response,
  - route backend minimum,
  - authorization backend.
- Dependency: Increment 3.
- Acceptance:
  - actor berizin dapat membaca/membuat/memperbarui unit,
  - actor tanpa izin ditolak,
  - duplicate code ditolak,
  - response tidak mengekspos field sensitif.
- Verifikasi:
  - focused feature tests Organization,
  - `php artisan module:validate --no-ansi`,
  - `php artisan starter:verify --no-ansi`.

## Increment 5: Audit mutation

- Perubahan:
  - event/audit call untuk create/update unit.
- Dependency: Increment 4 dan bridge audit existing.
- Acceptance:
  - create/update unit menghasilkan audit entry/event.
  - metadata audit tidak memuat payload sensitif.
- Verifikasi:
  - focused audit tests Organization.

## Batas Berhenti

Pekerjaan berhenti ketika backend slice minimum Organization valid dan lulus
focused tests. UI, hierarchy lanjutan, import/export, dan contract lintas module
menunggu persetujuan/increment terpisah.

## Rollback

- Revert commit per increment.
- Untuk migration yang sudah dijalankan lokal, gunakan rollback migration
  module sesuai mekanisme Laravel sebelum menghapus source.
- Jangan menghapus atau memindahkan module existing `System/*` sebagai bagian
  dari rollback Organization.

