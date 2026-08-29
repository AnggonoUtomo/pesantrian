# Implementation Plan: Academic/AcademicPeriod

## Scope

Pekerjaan ini memulai module `Academic/AcademicPeriod` sebagai foundation
periode akademik: tahun akademik, semester/term, periode aktif, dan lifecycle
pembukaan/penutupan periode.

Increment awal berhenti pada dokumentasi, skeleton module, data foundation,
backend behavior minimum, audit, dan contract candidate. UI penuh menunggu
increment eksplisit setelah backend stabil.

## Increment 1: Dokumentasi module

- Perubahan:
  - `docs/modules/Academic/AcademicPeriod/README.md`
  - `docs/modules/Academic/AcademicPeriod/specification.md`
  - `docs/modules/Academic/AcademicPeriod/plan.md`
  - `docs/modules/Academic/AcademicPeriod/tasks.md`
- Dependency: Organization foundation selesai dan roadmap Task 5 siap dimulai.
- Acceptance: scope, non-scope, acceptance criteria, dependency, risiko, dan
  verifikasi tertulis.
- Verifikasi: `git diff --check`.

## Increment 2: Skeleton module

- Perubahan:
  - generate `app/Modules/Academic/AcademicPeriod/` dengan generator;
  - review `module.json`, `module.php`, `permissions.php`,
    `ServiceProvider.php`, `README.md`, dan `Routes/*`.
- Dependency: Increment 1 direview.
- Acceptance:
  - target path `app/Modules/Academic/AcademicPeriod`;
  - tidak ada folder kosong placeholder;
  - manifest valid.
- Verifikasi:
  - `php artisan module:make Academic AcademicPeriod --dry-run --json --no-ansi`
  - `php artisan module:make Academic AcademicPeriod --force --yes --no-ansi`
  - `php artisan module:validate --no-ansi`
  - `git diff --check`

## Increment 3: Data foundation

- Perubahan:
  - migration `academic_years`;
  - migration `academic_terms`;
  - model/persistence minimum;
  - permission identity awal.
- Dependency: Increment 2.
- Acceptance:
  - table memakai ULID;
  - `academic_years.code` unik;
  - `academic_terms` terhubung ke tahun akademik;
  - term memiliki status dan marker aktif;
  - permission `academic_period.view` dan `academic_period.manage` tersedia.
- Verifikasi:
  - focused migration/model tests;
  - `php artisan module:validate --no-ansi`.

## Increment 4: Backend read/list dan create/update minimum

- Perubahan:
  - query/action minimum;
  - controller/request/resource atau API response;
  - route backend minimum;
  - authorization backend.
- Dependency: Increment 3.
- Acceptance:
  - actor berizin dapat membaca/membuat/memperbarui tahun akademik dan term;
  - actor tanpa izin ditolak;
  - duplicate code ditolak;
  - rentang tanggal invalid ditolak;
  - response tidak mengekspos field sensitif.
- Verifikasi:
  - focused feature tests AcademicPeriod;
  - `php artisan module:validate --no-ansi`;
  - `php artisan starter:verify --no-ansi`.

## Increment 5: Active period lifecycle

- Perubahan:
  - action aktivasi term;
  - action penutupan term;
  - rule satu active term sesuai keputusan scope.
- Dependency: Increment 4.
- Acceptance:
  - satu active term dapat ditetapkan;
  - aktivasi menjaga invariant satu active term;
  - closed term tidak aktif;
  - failure dipetakan ke validation error yang jelas.
- Verifikasi:
  - focused lifecycle tests AcademicPeriod.

## Increment 6: Audit mutation

- Perubahan:
  - audit create/update year;
  - audit create/update/activate/close term.
- Dependency: Increment 4-5 dan bridge audit existing.
- Acceptance:
  - mutation periode menghasilkan audit entry/event;
  - metadata audit aman dan tidak memuat payload sensitif.
- Verifikasi:
  - focused audit tests AcademicPeriod.

## Increment 7: Candidate active-period contract

- Perubahan:
  - dokumentasikan candidate query contract active period;
  - implementasi hanya bila consumer nyata disetujui.
- Dependency: Increment 5-6.
- Acceptance:
  - contract tidak mengekspos model Infrastructure;
  - DTO cukup ringkas untuk consumer Academic, Finance, atau Reporting;
  - tidak ada direct dependency lintas module.
- Verifikasi:
  - focused contract/query tests bila diimplementasikan;
  - `php artisan module:validate --no-ansi`.

## Increment 8: UI/Inertia read page

- Perubahan:
  - route web Inertia `academic.periods.index`;
  - controller presentation untuk props daftar tahun, daftar term, dan current
    term;
  - frontend canonical di `resources/js/modules/academic-period/`;
  - menu sidebar namespace Academic.
- Dependency: Increment 4-7.
- Acceptance:
  - page resolve dari canonical frontend module;
  - `Index.tsx` hanya menjadi komposer layout;
  - komponen business-specific berada di folder `components`;
  - backend tetap authority untuk permission.
- Verifikasi:
  - `php artisan test --filter=AcademicPeriodPresentationTest`;
  - `php artisan test --filter=NavigationSidebarTest|ZiggyRouteTest`;
  - `npm run types:check`;
  - `npm run build`.

## Batas Berhenti

Pekerjaan berhenti ketika backend foundation AcademicPeriod valid dan lulus
focused tests. Form mutation UI, import/export, calendar event umum, dan
consumer integration menunggu persetujuan/increment terpisah.

## Rollback

- Revert commit per increment.
- Untuk migration yang sudah dijalankan lokal, gunakan rollback migration
  module sesuai mekanisme Laravel sebelum menghapus source.
- Jangan mengubah atau menghapus data/module `Organization/Organization`
  sebagai bagian dari rollback AcademicPeriod.
