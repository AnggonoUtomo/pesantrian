# AcademicPeriod

Source module mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md) dan
[`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas

- Namespace: `Academic`
- Module: `AcademicPeriod`
- Source: `app/Modules/Academic/AcademicPeriod/`
- Frontend: ditunda sampai backend foundation dan contract stabil.
- Status: `Active`

## Tujuan

Module `Academic/AcademicPeriod` menjadi source of truth untuk tahun akademik,
semester/term, kalender periode, dan periode akademik aktif SakaSantri.

Module ini dibangun setelah `Organization/Organization` karena periode akademik
awal berlaku global untuk seluruh aplikasi. Scope per unit organisasi ditunda
sampai ada kebutuhan operasional nyata.

## Boundary

- Memiliki:
  - identitas tahun akademik,
  - semester/term dalam tahun akademik,
  - rentang tanggal periode,
  - status draft/active/closed,
  - penanda periode aktif,
  - pembukaan dan penutupan periode.
- Tidak memiliki:
  - kelas, rombel, mata pelajaran, atau jadwal akademik,
  - absensi dan nilai,
  - pendaftaran santri,
  - tagihan atau pembayaran,
  - struktur organisasi selain referensi unit bila diperlukan,
  - kalender event umum di luar konteks periode akademik.

## Public Boundary

Public boundary dibuat ketika ada consumer nyata. Candidate consumer awal:

- `Academic/Academic` membutuhkan active period untuk kelas, rombel, jadwal,
  dan attendance;
- `Finance/StudentFinance` membutuhkan active period untuk fee/invoice;
- `Platform/Reporting` membutuhkan active period untuk filter dashboard.

Candidate public boundary:

- query active academic period;
- DTO ringkas academic year dan term;
- event perubahan status periode jika AuditTrail atau module consumer perlu
  merespons perubahan.

## Data dan Identifier

- Table kandidat:
  - `academic_years`
  - `academic_terms`
- Primary identifier: ULID.
- Business identifier:
  - `code` untuk tahun akademik, misalnya `2026-2027`;
  - `term_code` untuk semester/term, misalnya `2026-2027-GANJIL`.

## Permission dan Audit

- Permission awal:
  - `academic_period.view`
  - `academic_period.manage`
- Backend menjadi authority authorization.
- Frontend permission hanya UX bila UI ditambahkan pada increment berikutnya.
- Audit mutation dicatat melalui bridge AuditLog/AuditTrail existing untuk:
  - `academic_period.year.created`
  - `academic_period.year.updated`
  - `academic_period.term.created`
  - `academic_period.term.updated`
  - `academic_period.term.activated`
  - `academic_period.term.closed`

## Operasi

- Migration module berada di
  `app/Modules/Academic/AcademicPeriod/Database/Migrations/`.
- `ServiceProvider.php` menjadi composition root dan tidak berisi business
  logic.
- Seeder/factory hanya dibuat ketika ada data/test nyata.
- Public contract lintas module tidak dibuat sampai consumer pertama jelas.
- Active period saat ini global: hanya satu `academic_terms.is_active=true`
  dengan status `active` pada satu waktu.
- Event audit module dipublish sebagai `academic-period.activity.occurred` dan
  direkam oleh listener AuditLog existing.

## Verifikasi Utama

```bash
php artisan module:make Academic AcademicPeriod --dry-run --json --no-ansi
php artisan module:make Academic AcademicPeriod --force --yes --no-ansi
php artisan test --filter=AcademicPeriod
php artisan module:validate --no-ansi
php artisan starter:verify --no-ansi
git diff --check
```
