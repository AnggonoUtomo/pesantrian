# Specification: Pesantrian/PresensiSantri

## Status

Active - draft mutation ready. Source module, permission identity, contract
kandidat presensi awal, migration, model record, factory minimum, API read/list,
dan API create/update draft serta entry sudah tersedia. Dokumen ini tetap
menjadi acuan untuk submit/revisi/void, seeder demo, dan UI.

## Objective

Membangun module `Pesantrian/PresensiSantri` untuk mencatat kehadiran santri
secara operasional, mulai dari presensi kelas/rombel, asrama, dan kegiatan umum
pesantren. Modul harus mudah dipakai operator pemula, tetapi tetap menjaga
validasi backend, audit, dan histori revisi.

## Scope

- Membuat sesi presensi berdasarkan tanggal, konteks, dan jenis sesi.
- Mengambil daftar santri kandidat dari Santri, Kelas/Rombel, atau Asrama.
- Mencatat status kehadiran per santri.
- Menyimpan catatan sederhana untuk izin/sakit/alfa/terlambat.
- Submit presensi agar data menjadi final operasional.
- Revisi terbatas dengan alasan dan audit.
- Menampilkan list, detail, dan rekap ringkas presensi.
- Menyediakan demo seeder untuk uji lifecycle.
- Menyediakan UI Inertia untuk operator internal.

## Non-Scope

- Public form tanpa login.
- Approval perizinan santri.
- Lampiran surat izin/sakit.
- Integrasi mesin absensi, RFID, QR scanner, atau biometric.
- Jadwal pelajaran detail.
- Rekam medis klinik.
- Otomatis membuat pelanggaran kedisiplinan.
- Laporan BI kompleks.
- Portal wali/santri.

## Actor

- SuperSystem.
- Operator Pesantrian.
- Operator Akademik.
- Guru/wali kelas yang diberi permission.
- Musyrif/pembina asrama yang diberi permission.
- Auditor/viewer yang diberi permission baca.

## Use Case Baseline

### Membuat Sesi Presensi

Operator memilih tanggal, konteks presensi, nama sesi, dan sumber daftar santri.
Sesi baru mulai sebagai `draft`.

### Mengisi Kehadiran

Operator mengisi status santri satu per satu atau memakai default hadir lalu
mengubah santri yang izin/sakit/alfa/terlambat.

### Submit Presensi

Operator men-submit sesi presensi. Setelah submit, data dianggap final untuk
operasional harian.

### Revisi Presensi

Operator yang berwenang dapat merevisi sesi yang sudah submit dengan alasan.
Setiap revisi dicatat pada audit.

### Membatalkan Sesi

Sesi yang salah dibuat dapat dibatalkan sebagai `void`. Data tidak dihapus
permanen pada baseline.

### Rekap Presensi

Operator melihat ringkasan jumlah hadir, izin, sakit, alfa, dan terlambat per
sesi serta daftar detail santri.

## Candidate Data Model

### `student_attendance_sessions`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `attendance_date` | Tanggal presensi |
| `context_type` | `class_group`, `dormitory`, atau `activity` |
| `context_id` | Nullable ULID rujukan context saat ada |
| `context_name` | Snapshot nama kelas/asrama/kegiatan |
| `session_code` | Kode sesi unique per tanggal/konteks |
| `session_name` | Nama tampil, misalnya Subuh, KBM Pagi, Malam |
| `status` | `draft`, `submitted`, `revised`, `void` |
| `submitted_at` | Nullable |
| `submitted_by` | Nullable actor id |
| `voided_at` | Nullable |
| `voided_by` | Nullable actor id |
| `void_reason` | Nullable |
| `created_by` | Nullable actor id |
| `created_at`, `updated_at` | Timestamp standar |

### `student_attendance_entries`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `session_id` | FK ke `student_attendance_sessions` |
| `student_id` | Rujukan santri dari public contract |
| `student_no` | Snapshot NIS |
| `student_name` | Snapshot nama santri |
| `status` | `present`, `late`, `excused`, `sick`, `absent` |
| `minutes_late` | Nullable, hanya untuk terlambat |
| `note` | Nullable |
| `source_reference_type` | Nullable untuk integrasi izin/sakit nanti |
| `source_reference_id` | Nullable |
| `created_at`, `updated_at` | Timestamp standar |

### `student_attendance_revisions`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `session_id` | FK ke `student_attendance_sessions` |
| `reason` | Alasan revisi |
| `changed_by` | Actor id |
| `changed_at` | Timestamp revisi |
| `summary` | Ringkasan perubahan tanpa payload sensitif berlebihan |

## Rule Baseline

- Sesi presensi tidak boleh memiliki kode duplikat pada tanggal dan konteks
  yang sama.
- Detail presensi unique per `session_id` dan `student_id`.
- Hanya santri aktif yang boleh masuk entry presensi baseline.
- Status `late` wajib memiliki `minutes_late` lebih dari 0.
- Status `present` tidak boleh memiliki alasan wajib.
- Status `excused`, `sick`, dan `absent` boleh memiliki catatan.
- Sesi `submitted` tidak boleh diedit tanpa jalur revisi.
- Sesi `void` tidak boleh disubmit atau direvisi.
- Backend menjadi authority status, permission, dan validasi. Frontend hanya
  membantu UX.

## Context Source

| Context | Source awal | Catatan |
| --- | --- | --- |
| `class_group` | `Academic/KelasRombel` | Mengambil anggota rombel aktif. |
| `dormitory` | `Pesantrian/Asrama` | Mengambil penghuni aktif asrama/kamar. |
| `activity` | Input manual + selector santri aktif | Dipakai untuk kegiatan umum sampai module jadwal/kegiatan ada. |

PresensiSantri menjadi consumer contract dari Santri, KelasRombel, dan Asrama.
Jika contract yang dibutuhkan belum tersedia, increment awal harus membuat
contract pada owner module sesuai kebutuhan nyata.

Contract readiness saat ini:

- Selector santri aktif memakai `Pesantrian/Santri::ActiveStudentReader`.
- Roster rombel aktif memakai
  `Academic/KelasRombel::ActiveClassGroupRosterReader`.
- Penghuni asrama/kamar aktif memakai
  `Pesantrian/Asrama::ActiveDormitoryResidentReader`.

## Permission Candidate

- `presensi_santri.view`
- `presensi_santri.manage`
- `presensi_santri.submit`
- `presensi_santri.revise`
- `presensi_santri.archive`

Permission dapat disederhanakan saat implementasi bila terlalu granular, tetapi
backend tetap menjadi sumber otorisasi.

## Audit Candidate

- `presensi_santri.session.created`
- `presensi_santri.session.updated`
- `presensi_santri.session.submitted`
- `presensi_santri.session.revised`
- `presensi_santri.session.voided`
- `presensi_santri.entry.updated`

Audit menyimpan ringkasan perubahan dan correlation id, bukan payload lengkap
yang tidak perlu.

## API Candidate

Endpoint API internal mengikuti envelope API canonical:

- `GET /api/v1/pesantrian/student-attendances`
- `GET /api/v1/pesantrian/student-attendances/{session}`
- `POST /api/v1/pesantrian/student-attendances`
- `PATCH /api/v1/pesantrian/student-attendances/{session}`
- `PATCH /api/v1/pesantrian/student-attendances/{session}/entries`
- `PATCH /api/v1/pesantrian/student-attendances/{session}/submit`
- `PATCH /api/v1/pesantrian/student-attendances/{session}/revise`
- `PATCH /api/v1/pesantrian/student-attendances/{session}/void`

Route web Inertia memakai nama `pesantrian.student-attendances.*` agar Ziggy
stabil di frontend.

## UI Baseline

UI berada di `resources/js/pages/Pesantrian/PresensiSantri/`.

Acceptance UI awal:

- index tetap minimal dan delegasi ke komponen;
- list mendukung search/filter tanggal, konteks, status, dan pagination;
- detail menampilkan ringkasan status dan daftar santri;
- form mutation berada di folder `components`;
- submit/revisi/void memakai confirmation;
- browser QA desktop/mobile wajib dilakukan.

## Demo Seeder Candidate

Demo seeder wajib idempotent dan tidak berjalan di `production`.

Data demo minimum:

- sesi presensi kelas hari ini;
- sesi presensi asrama;
- sesi kegiatan umum;
- variasi status hadir, izin, sakit, alfa, terlambat;
- satu sesi submitted;
- satu sesi revised;
- satu sesi void.

## Open Questions

- Apakah presensi kelas dan presensi asrama memakai jadwal tetap nanti, atau
  cukup sesi manual dulu sampai module jadwal dibuat?
- Apakah status izin wajib terhubung ke `PerizinanSantri` setelah module izin
  dibuat, atau tetap boleh manual sebagai fallback?
- Apakah revisi presensi boleh mengubah semua entry, atau hanya entry tertentu
  dengan alasan per perubahan?
