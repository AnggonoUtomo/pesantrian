# Specification: Academic/KelasRombel

## Status

Active - backend placement API. Dokumentasi awal, skeleton runtime, public read
contract minimum dari module referensi, schema inti, API read/list, API
create/update master, dan API penempatan santri Kelas/Rombel sudah tersedia.

## Objective

Membangun module `Academic/KelasRombel` sebagai pengelola kelas, rombongan
belajar, kurikulum minimum, penempatan santri, dan wali kelas pada periode
akademik SakaSantri.

## Scope

- Membuat master kurikulum minimum.
- Membuat tingkat/kelas akademik.
- Membuat rombel per unit dan periode akademik.
- Menempatkan santri aktif ke rombel.
- Mengelola perpindahan/keluar dari rombel secara aman.
- Menetapkan wali kelas dari data SDM/guru.
- Menyediakan list/detail dan selector rombel.
- Menulis audit untuk mutasi signifikan.
- Menambahkan seeder demo idempotent setelah module runtime dibuat.

## Non-Scope

- Mata pelajaran detail.
- Jadwal pelajaran.
- Presensi akademik.
- Nilai/rapor.
- Portal wali/santri.
- Import/export massal.
- Integrasi kalender umum.
- Penagihan atau biaya akademik.

## Actor

- Admin akademik.
- Operator akademik.
- Kepala unit.
- Wali kelas yang diberi permission.

## Use Case Baseline

### List dan Search Rombel

Operator dapat melihat rombel berdasarkan tahun ajaran/semester, unit,
kurikulum, status, dan kata kunci.

### Detail Rombel

Operator dapat membuka detail rombel, melihat unit, periode, wali kelas, daftar
santri, kapasitas, dan status.

Endpoint backend:

- `GET /api/v1/academic/class-groups`
- `GET /api/v1/academic/class-groups/{classGroup}`

### Kelola Kurikulum Minimum

Admin akademik dapat membuat dan mengubah kurikulum sebagai label operasional
awal untuk rombel.

### Kelola Kelas dan Rombel

Admin akademik dapat membuat tingkat kelas dan rombel, mengatur kapasitas, unit,
periode, dan status.

### Penempatan Santri

Operator akademik dapat menempatkan santri aktif ke rombel. Satu santri hanya
boleh berada pada satu rombel aktif dalam periode akademik yang sama.

### Pindah Rombel

Operator dapat memindahkan santri dari rombel lama ke rombel baru dengan alasan
dan audit.

### Wali Kelas

Operator dapat menetapkan atau mengganti wali kelas dari data SDM/guru aktif.

## Candidate Data Model

### `academic_curricula`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `code` | Business identifier, unique |
| `name` | Nama kurikulum |
| `description` | Nullable |
| `status` | draft/active/closed/archived |
| `created_at`, `updated_at` | Timestamp standar |

### `class_levels`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `unit_id` | Referensi unit organisasi |
| `code` | Misalnya VII, VIII, X |
| `name` | Nama tampil kelas |
| `sequence` | Urutan tampilan |
| `status` | draft/active/closed/archived |
| `created_at`, `updated_at` | Timestamp standar |

### `class_groups`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `academic_year_id` | Referensi tahun akademik |
| `academic_term_id` | Referensi semester/term |
| `unit_id` | Referensi unit organisasi |
| `curriculum_id` | Nullable pada slice awal |
| `class_level_id` | Referensi tingkat/kelas |
| `code` | Misalnya VII-A |
| `name` | Nama rombel |
| `capacity` | Nullable atau integer positif |
| `status` | draft/active/closed/archived |
| `created_at`, `updated_at` | Timestamp standar |

### `class_group_students`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `class_group_id` | Referensi rombel |
| `student_id` | Referensi santri |
| `student_no` | Snapshot NIS untuk audit/read cepat |
| `joined_on` | Tanggal mulai |
| `left_on` | Nullable |
| `status` | active/transferred/removed |
| `reason` | Nullable, wajib untuk transfer/remove |
| `active_period_student_key` | Unique nullable guard untuk placement aktif per santri per semester |
| `created_at`, `updated_at` | Timestamp standar |

### `class_group_homerooms`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `class_group_id` | Referensi rombel |
| `employee_id` | Referensi SDM/guru |
| `employee_name` | Snapshot nama wali kelas |
| `assigned_on` | Tanggal mulai |
| `ended_on` | Nullable |
| `status` | active/ended |
| `reason` | Nullable |
| `active_class_group_key` | Unique nullable guard untuk satu wali kelas aktif per rombel |
| `created_at`, `updated_at` | Timestamp standar |

## Invariant

- Code kurikulum unik.
- Code kelas unik dalam unit.
- Code rombel unik dalam kombinasi unit, tahun akademik, dan semester.
- Satu santri hanya boleh memiliki satu placement aktif pada periode akademik
  yang sama.
- Placement hanya boleh untuk santri aktif.
- Wali kelas hanya boleh dari pegawai/guru aktif.
- Closed/archived rombel tidak menerima placement baru.

## Contract dan Boundary

`Academic/KelasRombel` membutuhkan data referensi dari module lain. Pada coding
awal, dependency lintas module wajib memakai public contract/DTO/query yang
disetujui, bukan model Infrastructure.

Public contract minimum yang sudah tersedia:

- `Organization/Organization\Application\Contracts\EducationUnitReader`
  mengembalikan `EducationUnitOptionData` untuk unit pendidikan aktif.
- `Academic/AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader`
  mengembalikan `ActiveAcademicPeriodData` untuk tahun ajaran/semester aktif.
- `Pesantrian/Santri\Application\Contracts\ActiveStudentReader` mengembalikan
  `ActiveStudentOptionData` untuk santri aktif, dengan lookup by-id, filter
  unit, dan search.
- `HumanResource/HumanResource\Application\Contracts\ActiveEmployeeReader`
  mengembalikan `ActiveEmployeeOptionData` untuk pegawai aktif, dengan filter
  unit, jenis pegawai, dan search.

Contract di atas dibuat karena `Academic/KelasRombel` adalah consumer nyata
untuk selector dan validasi awal. KelasRombel tetap tidak boleh mengimpor model
Infrastructure module referensi.

## Permission Candidate

- `kelas_rombel.view`
- `kelas_rombel.manage`
- `kelas_rombel.placement`
- `kelas_rombel.archive`

Backend tetap menjadi authority permission. Frontend hanya menyembunyikan atau
menampilkan aksi untuk UX.

## Audit Candidate

- `kelas_rombel.curriculum.created`
- `kelas_rombel.curriculum.updated`
- `kelas_rombel.class_level.created`
- `kelas_rombel.class_level.updated`
- `kelas_rombel.class_group.created`
- `kelas_rombel.class_group.updated`
- `kelas_rombel.student.placed`
- `kelas_rombel.student.transferred`
- `kelas_rombel.student.removed`
- `kelas_rombel.homeroom.assigned`
- `kelas_rombel.homeroom.ended`
- `kelas_rombel.class_group.archived`
- `kelas_rombel.class_group.restored`

Audit tidak boleh menyimpan payload sensitif berlebihan.

## UI Baseline

UI berada di `resources/js/pages/Academic/KelasRombel/`.

Acceptance UI:

- `Index.tsx` dan `Show.tsx` tetap minimal.
- Komponen business-specific berada di folder `components`.
- List rombel memiliki filter periode, unit, kurikulum, status, dan search.
- Detail rombel menampilkan daftar santri dan wali kelas.
- Mutation destructive memakai confirmation dialog.
- Browser QA desktop/mobile wajib dilakukan pada increment UI.

## Acceptance Criteria

- [x] Module skeleton `Academic/KelasRombel` valid menurut module registry.
- [x] Schema inti memakai ULID.
- [x] Kurikulum, kelas, rombel, placement santri, dan wali kelas tersedia pada
  schema/read model awal.
- [x] Satu santri tidak bisa punya dua placement aktif pada periode yang sama
  melalui guard unique nullable.
- [x] Closed/archived rombel tidak menerima placement baru.
- [x] Actor tanpa permission ditolak backend untuk API read/list.
- [x] Mutation signifikan tercatat audit untuk create/update kurikulum, tingkat
  kelas, rombel, dan penempatan santri.
- [ ] Seeder demo idempotent tersedia setelah schema/runtime dibuat.
- [ ] UI Inertia list/detail tersedia dengan page tipis dan komponen terpisah.
- [ ] Browser QA desktop/mobile lulus sebelum module dianggap baseline complete.
- [x] Focused tests contract readiness `KelasRombel` lulus.
- [x] Focused tests backend data/read `KelasRombel` lulus.
- [x] `php artisan module:validate --no-ansi` lulus.

## Open Questions

- Vocabulary teknis final untuk `class_levels` dan `class_groups` dapat
  berubah saat coding bila pola Laravel/model existing lebih cocok.
- Apakah kurikulum cukup label minimum dulu atau langsung punya struktur mapel?
  Rekomendasi: label minimum dulu.
- Apakah rombel wajib scoped semester atau cukup tahun ajaran?
  Rekomendasi: scoped tahun ajaran + semester agar presensi/nilai nanti jelas.
