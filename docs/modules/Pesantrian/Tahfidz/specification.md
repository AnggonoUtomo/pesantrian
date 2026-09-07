# Specification: Pesantrian/Tahfidz

## Status

Active - backend setoran/murojaah mutation ready. Source module, manifest,
ServiceProvider, README source, permission identity, wiring AccessControl,
guardrail contract lintas module, migration, record model, factory minimum,
Application read query, API baca, request validation program/target/setoran,
Application action create/update program/target/setoran, dan audit mutation
program/target/setoran sudah tersedia. Belum ada review/void lifecycle, seeder
demo Tahfidz, atau UI.

## Objective

Membangun module `Pesantrian/Tahfidz` untuk mencatat dan memantau hafalan santri
secara bertahap. Module harus mudah dipakai operator/pembimbing pemula, namun
tetap menjaga validasi backend, histori data, dan audit perubahan.

## Scope

- Mengelola program atau kelompok tahfidz.
- Membuat target hafalan santri.
- Mencatat setoran hafalan baru.
- Mencatat murojaah/pengulangan.
- Menentukan status hasil setoran.
- Menyimpan catatan pembimbing.
- Menampilkan list, detail, dan ringkasan progres.
- Menyediakan demo seeder untuk uji lifecycle.
- Menyediakan UI Inertia untuk operator internal.

## Non-Scope

- Portal wali/santri.
- Upload audio/video setoran.
- Sertifikat tahfidz.
- Jadwal pelajaran detail.
- Nilai rapor formal.
- Konseling/pembinaan umum.
- Public form tanpa login.
- Integrasi perangkat absensi atau presensi tahfidz otomatis.
- Analitik BI kompleks.

## Actor

- SuperSystem.
- Operator Pesantrian.
- Operator Akademik yang diberi akses.
- Pembimbing/ustadz tahfidz yang diberi permission.
- Musyrif asrama yang diberi permission.
- Auditor/viewer yang diberi permission baca.

## Use Case Baseline

### Mengelola Program Tahfidz

Operator membuat program tahfidz, misalnya tahfidz reguler, intensif, atau
program khusus asrama. Program menjadi konteks target dan monitoring.

### Membuat Target Hafalan

Operator atau pembimbing membuat target hafalan untuk santri pada periode
tertentu. Target dapat berupa rentang juz/surah/ayat.

### Mencatat Setoran Hafalan

Pembimbing mencatat setoran hafalan baru dari santri, termasuk rentang hafalan,
tanggal, status hasil, dan catatan sederhana.

### Mencatat Murojaah

Pembimbing mencatat pengulangan hafalan agar progres santri tidak hanya terlihat
dari hafalan baru, tetapi juga dari hafalan yang dipertahankan.

### Review dan Koreksi

Catatan setoran dapat diterima, diminta ulang/perbaikan, atau dibatalkan bila
salah input. Koreksi wajib meninggalkan audit.

### Rekap Progres

Operator melihat ringkasan progres per santri, program, periode, pembimbing,
kelas/rombel, atau asrama.

## Candidate Data Model

### `tahfidz_programs`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `code` | Kode program unique |
| `name` | Nama program |
| `description` | Nullable |
| `status` | `active`, `inactive`, atau `archived` |
| `created_by` | Nullable actor id |
| `archived_at` | Nullable |
| `archived_by` | Nullable actor id |
| `created_at`, `updated_at` | Timestamp standar |

### `tahfidz_targets`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `program_id` | FK ke `tahfidz_programs` |
| `student_id` | Rujukan santri dari public contract |
| `student_no` | Snapshot NIS |
| `student_name` | Snapshot nama santri |
| `academic_period_id` | Nullable |
| `period_label` | Snapshot nama periode |
| `target_juz` | Nullable |
| `target_surah` | Nullable |
| `target_ayah_from` | Nullable |
| `target_ayah_to` | Nullable |
| `target_note` | Nullable |
| `status` | `active`, `completed`, `cancelled` |
| `created_by` | Nullable actor id |
| `created_at`, `updated_at` | Timestamp standar |

### `tahfidz_submissions`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `program_id` | FK ke `tahfidz_programs` |
| `target_id` | Nullable FK ke target |
| `student_id` | Rujukan santri dari public contract |
| `student_no` | Snapshot NIS |
| `student_name` | Snapshot nama santri |
| `supervisor_id` | Nullable rujukan pegawai/pembimbing |
| `supervisor_name` | Snapshot nama pembimbing |
| `submission_date` | Tanggal setoran |
| `type` | `new_memorization` atau `revision`/`murojaah` |
| `juz` | Nullable |
| `surah` | Nullable |
| `ayah_from` | Nullable |
| `ayah_to` | Nullable |
| `status` | `draft`, `submitted`, `accepted`, `needs_revision`, `void` |
| `quality_note` | Nullable catatan pembimbing |
| `created_by` | Nullable actor id |
| `reviewed_at` | Nullable |
| `reviewed_by` | Nullable actor id |
| `voided_at` | Nullable |
| `voided_by` | Nullable actor id |
| `void_reason` | Nullable |
| `created_at`, `updated_at` | Timestamp standar |

### `tahfidz_submission_revisions`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `submission_id` | FK ke `tahfidz_submissions` |
| `reason` | Alasan koreksi |
| `changed_by` | Actor id |
| `changed_at` | Timestamp revisi |
| `summary` | Ringkasan perubahan tanpa payload sensitif berlebihan |

## Rule Baseline

- Hanya santri aktif yang bisa dibuatkan target atau setoran.
- Program yang diarsipkan tidak boleh menerima target/setoran baru.
- Rentang ayat wajib valid: ayat mulai tidak boleh lebih besar dari ayat
  selesai.
- Setoran minimal memiliki salah satu konteks hafalan: juz, surah, atau rentang
  ayat.
- Target aktif per santri/program/periode boleh dibatasi satu target utama pada
  baseline awal agar monitoring mudah dipahami.
- Status `accepted` dan `needs_revision` hanya boleh dipakai lewat aksi review.
- Status `void` wajib alasan dan tidak menghapus catatan setoran.
- Snapshot NIS, nama santri, dan nama pembimbing disimpan agar histori tetap
  terbaca meskipun master data berubah.
- Backend menjadi authority permission, status, dan validasi.

## Context Source

| Context | Source awal | Catatan |
| --- | --- | --- |
| Santri aktif | `Pesantrian/Santri` | Selector dan validasi target/setoran. |
| Pembimbing aktif | `HumanResource/HumanResource` | Selector ustadz/guru/musyrif bila diberi tugas tahfidz. |
| Periode | `Academic/AcademicPeriod` | Opsional untuk target per semester/tahun ajaran. |
| Rombel | `Academic/KelasRombel` | Filter monitoring, bukan pemilik rule tahfidz. |
| Asrama | `Pesantrian/Asrama` | Filter monitoring, bukan pemilik rule tahfidz. |

Contract readiness awal:

- `ActiveStudentReader` dipakai untuk mengambil opsi dan validasi santri aktif.
- `ActiveEmployeeReader` dipakai untuk mengambil opsi dan validasi pembimbing
  aktif.
- `ActiveAcademicPeriodReader` dipakai untuk mengambil periode aktif.
- Tahfidz tidak boleh mengimpor model Infrastructure dari module dependency.

## Permission Candidate

- `tahfidz.view`
- `tahfidz.manage`
- `tahfidz.record`
- `tahfidz.review`
- `tahfidz.archive`

## Audit Candidate

- `tahfidz.program.created`
- `tahfidz.program.updated`
- `tahfidz.program.archived`
- `tahfidz.target.created`
- `tahfidz.target.updated`
- `tahfidz.submission.created`
- `tahfidz.submission.updated`
- `tahfidz.submission.reviewed`
- `tahfidz.submission.voided`

Audit menyimpan ringkasan perubahan dan correlation id, bukan payload lengkap
yang tidak perlu.

## API Candidate

Endpoint API internal mengikuti envelope API canonical.

Endpoint yang sudah tersedia:

- `GET /api/v1/pesantrian/tahfidz`
- `GET /api/v1/pesantrian/tahfidz/{submission}`
- `POST /api/v1/pesantrian/tahfidz/programs`
- `PATCH /api/v1/pesantrian/tahfidz/programs/{program}`
- `POST /api/v1/pesantrian/tahfidz/targets`
- `PATCH /api/v1/pesantrian/tahfidz/targets/{target}`
- `POST /api/v1/pesantrian/tahfidz/submissions`
- `PATCH /api/v1/pesantrian/tahfidz/submissions/{submission}`

Endpoint mutation kandidat berikutnya:

- `PATCH /api/v1/pesantrian/tahfidz/submissions/{submission}/review`
- `PATCH /api/v1/pesantrian/tahfidz/submissions/{submission}/void`

Route web Inertia direncanakan memakai nama `pesantrian.tahfidz.*` agar Ziggy
stabil di frontend.

## UI Baseline

UI berada di `resources/js/pages/Pesantrian/Tahfidz/`.

Acceptance UI awal:

- index tetap minimal dan delegasi ke komponen;
- list mendukung search/filter program, santri, pembimbing, periode, tipe, dan
  status;
- detail menampilkan ringkasan setoran dan target terkait;
- form mutation berada di folder `components`;
- review/void memakai confirmation atau dialog alasan;
- browser QA desktop/mobile wajib dilakukan.

## Demo Seeder Candidate

Demo seeder wajib idempotent dan tidak berjalan di `production`.

Data demo minimum:

- program tahfidz aktif;
- satu program arsip/nonaktif;
- target hafalan beberapa santri aktif;
- setoran hafalan baru dengan status accepted;
- setoran murojaah dengan status submitted;
- setoran needs_revision;
- satu setoran void;
- variasi pembimbing dari data SDM demo.

## Open Questions

- Apakah target hafalan awal wajib per semester aktif, atau boleh target bebas
  tanpa periode?
- Apakah pembimbing tahfidz wajib pegawai bertipe guru/ustadz, atau boleh
  musyrif asrama juga?
- Apakah penilaian kualitas hafalan cukup status sederhana dulu, atau perlu
  nilai seperti kelancaran/tajwid/makhraj sejak awal?
