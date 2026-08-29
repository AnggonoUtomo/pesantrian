# Specification: Academic/AcademicPeriod

## Status

Draft untuk review sebelum implementasi skeleton dan backend foundation.

## Tujuan dan Scope

Bangun module `Academic/AcademicPeriod` sebagai source of truth periode akademik
SakaSantri.

Scope slice awal:

- module skeleton dan metadata;
- table `academic_years` dan `academic_terms` dengan ULID;
- permission `academic_period.view` dan `academic_period.manage`;
- read/list tahun akademik dan term;
- create/update minimum tahun akademik dan term;
- aktivasi satu periode aktif global;
- penutupan periode;
- audit mutation periode;
- candidate active-period query contract sebagai pegangan consumer berikutnya.

## Arsitektur

- Hexagon: `app/Modules/Academic/AcademicPeriod`.
- Inbound adapter:
  - HTTP API atau web route backend minimum;
  - UI/Inertia ditunda sampai backend foundation valid.
- Use case awal:
  - `ListAcademicYears`;
  - `CreateAcademicYear`;
  - `UpdateAcademicYear`;
  - `CreateAcademicTerm`;
  - `UpdateAcademicTerm`;
  - `ActivateAcademicTerm`;
  - `CloseAcademicTerm`.
- Candidate public contract:
  - `ActiveAcademicPeriodReader`;
  - `ActiveAcademicPeriodData`.
- Outbound port:
  - repository academic period bila query/mutation membutuhkan boundary
    eksplisit.
- Outbound adapter:
  - Eloquent model/repository di Infrastructure.
- Composition root:
  - `app/Modules/Academic/AcademicPeriod/ServiceProvider.php`.

Domain dibuat hanya ketika rule murni mulai bernilai, misalnya invariant satu
periode aktif, rentang tanggal term tidak overlap, atau transisi status periode.
Jika rule masih sederhana, implementasi awal boleh berada di Application dengan
test yang ketat.

## Di Luar Scope

- UI React/Inertia penuh.
- Pemindahan frontend ke path lain di luar `resources/js/pages/*`.
- Calendar event umum.
- Struktur kelas, rombel, subject, schedule, attendance, dan grade.
- Billing/fee period detail.
- Student enrollment atau lifecycle.
- Multi-yayasan/multi-tenant SaaS.
- Import/export periode akademik.

## Contract

### Input

Input tahun akademik minimum:

- `code`: string wajib, unik, contoh `2026-2027`;
- `name`: string wajib, contoh `Tahun Akademik 2026/2027`;
- `starts_on`: date wajib;
- `ends_on`: date wajib, harus setelah `starts_on`;
- `status`: draft/active/closed.

Input term minimum:

- `academic_year_id`: ULID wajib;
- `code`: string wajib, unik dalam konteks tahun akademik;
- `name`: string wajib, contoh `Semester Ganjil`;
- `sequence`: integer wajib untuk urutan;
- `starts_on`: date wajib;
- `ends_on`: date wajib, harus setelah `starts_on`;
- `status`: draft/active/closed.
- `is_active` tidak boleh diaktifkan lewat create/update umum; aktivasi wajib
  lewat lifecycle endpoint agar invariant global terjaga.

### Output

Output read/list minimum:

- `id`;
- `code`;
- `name`;
- `starts_on`;
- `ends_on`;
- `status`;
- `is_active`;
- daftar term ringkas bila query membutuhkannya.

### Failure

- Validasi gagal: `422`.
- Actor tidak punya permission: `403`.
- Record tidak ditemukan: `404`.
- Duplicate code: `422`.
- Rentang tanggal invalid: `422`.
- Aktivasi periode ditolak bila rule status/tanggal tidak terpenuhi.
- Closed term tidak bisa dijadikan active period.

## Data

Table kandidat:

- `academic_years`
  - `id` ULID primary;
  - `code` string unique;
  - `name` string;
  - `starts_on` date;
  - `ends_on` date;
  - `status` string;
  - `created_at`, `updated_at`.
- `academic_terms`
  - `id` ULID primary;
  - `academic_year_id` ULID foreign key;
  - `code` string;
  - `name` string;
  - `sequence` unsigned integer;
  - `starts_on` date;
  - `ends_on` date;
  - `status` string;
  - `is_active` boolean;
  - `created_at`, `updated_at`.

Traceability awal cukup dari audit mutation dan timestamp. History penuh
perubahan kalender/periode tidak masuk slice pertama.

## Authorization dan Audit

- `academic_period.view`: membaca daftar/detail periode.
- `academic_period.manage`: create/update/activate/close periode.
- Backend permission wajib dicek di controller atau middleware route.
- Audit mutation minimum:
  - `academic_period.year.created`;
  - `academic_period.year.updated`;
  - `academic_period.term.created`;
  - `academic_period.term.updated`;
  - `academic_period.term.activated`;
  - `academic_period.term.closed`.

Metadata audit tidak boleh menyimpan secret, credential, token, atau payload
tidak relevan.

## UI

UI penuh ditunda sampai backend foundation valid. Bila UI ditambahkan pada
increment berikutnya:

- Page canonical mengikuti keputusan roadmap Task 3:
  `resources/js/pages/Academic/AcademicPeriod/pages/Index.tsx`.
- Komponen business-specific ditempatkan di
  `resources/js/pages/Academic/AcademicPeriod/components/`.
- Routing memakai Ziggy named routes.
- State wajib mencakup loading, empty, validation error, success toast, dan
  authorization UX.

## Dependency

- `Organization/Organization`: dependency produk untuk konteks unit bila nanti
  periode perlu scoped per unit. Slice awal active period global dan tidak
  mengambil model/repository Organization secara langsung.
- `System/AccessControl` bridge: permission backend.
- `System/AuditLog` bridge: audit recorder existing sampai vocabulary final
  `Console/AuditTrail` dimigrasikan.

Tidak ada dependency ke StudentLife, HumanResource, Finance, Reporting,
Dormitory, atau Academic pada slice awal.

## Acceptance Criteria

- [x] Module `Academic/AcademicPeriod` dapat dibuat oleh generator dan valid.
- [x] Migration academic years/terms memakai ULID.
- [x] Permission `academic_period.view` dan `academic_period.manage` tersedia.
- [x] Actor dengan permission dapat list/create/update periode minimum.
- [x] Actor tanpa permission ditolak oleh backend.
- [x] Duplicate code dan rentang tanggal invalid ditolak.
- [x] Hanya satu term aktif global pada satu waktu sesuai rule yang disetujui.
- [x] Mutation periode mencatat audit/event aman.
- [x] Candidate active-period contract terdokumentasi tanpa mengekspos model
  Infrastructure.
- [x] `php artisan module:validate --no-ansi` lulus.
- [x] Focused tests AcademicPeriod lulus.

## Risiko Terbuka

- Active period diputuskan global untuk aplikasi pada slice awal; scoped per
  unit organisasi ditunda sampai ada kebutuhan nyata.
- Perlu keputusan vocabulary final status: `draft/active/closed` atau
  variasi lain.
- Candidate active-period contract sudah didokumentasikan di
  `contracts/active-period.md`; runtime interface belum dibuat sampai consumer
  pertama disetujui.
- UI path canonical sudah diputuskan melalui roadmap Task 3:
  `resources/js/pages/Academic/AcademicPeriod/` untuk UI AcademicPeriod.
