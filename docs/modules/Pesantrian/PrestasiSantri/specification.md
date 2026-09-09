# Specification: Pesantrian/PrestasiSantri

## Objective

Module `Pesantrian/PrestasiSantri` menyediakan pencatatan prestasi santri yang
mudah dipakai operator pesantren. Prestasi yang dicatat menjadi riwayat positif
santri dan dapat dipakai untuk profil santri, laporan, alumni, pengumuman, atau
program apresiasi pada fase berikutnya.

Success baseline:

- Operator dapat mencatat prestasi santri secara terstruktur.
- Catatan prestasi memiliki lifecycle yang jelas dan tidak langsung menjadi
  riwayat resmi tanpa proses submit/verifikasi.
- Riwayat koreksi dan pembatalan tetap terlacak.
- Data snapshot santri dan pembina tersimpan agar riwayat lama tetap terbaca
  walau master data berubah.

## Users

- Operator Santri: mencatat dan mengelola prestasi.
- Operator Akademik: melihat atau membantu validasi prestasi akademik bila
  diberi permission.
- Pembina/Pendamping: dicatat sebagai pendamping prestasi bila tersedia.
- Auditor/Viewer: melihat riwayat prestasi sesuai permission.
- Super/System Admin: mengelola permission dan konfigurasi teknis.

## Scope

### In Scope Baseline

- Kategori prestasi.
- Catatan prestasi santri.
- Tingkat prestasi.
- Jenis/capaian prestasi.
- Status draft, submitted, verified, needs_revision, dan void.
- Snapshot santri.
- Snapshot pembina/pendamping bila ada.
- Read/list/detail API.
- Mutation API untuk kategori dan catatan.
- Web Inertia list/detail/mutation.
- Seeder demo.
- QA browser desktop/mobile.
- User manual lifecycle.

### Out of Scope Baseline

- Upload file bukti/sertifikat.
- Controlled download dokumen.
- Portal wali/santri.
- Publikasi pengumuman otomatis.
- Integrasi beasiswa/reward keuangan.
- Laporan prestasi kompleks.
- Penilaian akademik rapor.
- Sertifikat digital.

## Domain Language

| Istilah layar | Makna |
| --- | --- |
| Prestasi | Catatan capaian positif santri. |
| Kategori | Kelompok prestasi, misalnya Akademik, Tahfidz, Olahraga, Seni. |
| Tingkat | Level kegiatan atau lomba. |
| Hasil | Capaian santri, misalnya Juara 1, Finalis, Peserta Terbaik. |
| Penyelenggara | Pihak yang mengadakan kegiatan. |
| Pembina | Pegawai/guru/ustadz yang mendampingi atau memvalidasi konteks prestasi. |
| Verifikasi | Pengesahan catatan agar masuk riwayat resmi. |
| Void | Pembatalan catatan tanpa menghapus data. |

## Commands

```bash
php artisan module:make Pesantrian PrestasiSantri --dry-run --json --no-ansi
php artisan module:make Pesantrian PrestasiSantri --force --yes --no-ansi
php artisan module:validate --no-ansi
php artisan test --filter=PrestasiSantri --no-ansi
npm run types:check
npm run lint:check
npm run build
E2E_START_SERVER=true npx playwright test tests/Browser/prestasi-santri.spec.ts
```

## Project Structure

Backend candidate:

```text
app/Modules/Pesantrian/PrestasiSantri/
|-- module.json
|-- module.php
|-- permissions.php
|-- ServiceProvider.php
|-- README.md
|-- Application/
|   |-- Actions/
|   |-- Contracts/
|   |-- DTO/
|   `-- Queries/
|-- Infrastructure/
|   |-- Events/
|   |-- Models/
|   `-- Repositories/
|-- Presentation/
|   |-- Controllers/
|   |-- Requests/
|   `-- Resources/
|-- Database/
|   |-- Factories/
|   |-- Migrations/
|   `-- Seeders/
`-- Routes/
    |-- api.php
    |-- channels.php
    |-- console.php
    `-- web.php
```

Frontend candidate:

```text
resources/js/pages/Pesantrian/PrestasiSantri/
|-- pages/
|   |-- Index.tsx
|   `-- Show.tsx
|-- components/
`-- types.ts
```

Test candidate:

```text
tests/Feature/PrestasiSantri*Test.php
tests/Unit/PrestasiSantriPermissionIdentityTest.php
tests/Browser/prestasi-santri.spec.ts
tests/Browser/support/prestasi-santri-fixture.ts
```

## Data Model Candidate

### `student_achievement_categories`

- `id` ULID primary key.
- `code` unique.
- `name`.
- `description` nullable.
- `is_active`.
- `archived_at`, `archived_by`, `archive_reason` nullable.
- timestamps.

### `student_achievements`

- `id` ULID primary key.
- `achievement_no` unique.
- `category_id`.
- `student_id` nullable.
- `student_no` snapshot.
- `student_name` snapshot.
- `academic_period_id` nullable.
- `academic_period_label` nullable snapshot.
- `mentor_employee_id` nullable.
- `mentor_name` nullable snapshot.
- `title`.
- `achievement_type`.
- `level`.
- `result`.
- `organizer` nullable.
- `event_name` nullable.
- `event_location` nullable.
- `achieved_on` nullable.
- `period_started_on` nullable.
- `period_ended_on` nullable.
- `description` nullable.
- `notes` nullable.
- `status`.
- `submitted_at`, `submitted_by` nullable.
- `verified_at`, `verified_by`, `verification_note` nullable.
- `voided_at`, `voided_by`, `void_reason` nullable.
- timestamps.

### `student_achievement_revisions`

- `id` ULID primary key.
- `achievement_id`.
- `from_status` nullable.
- `to_status`.
- `reason`.
- `changed_by`.
- `changed_at`.
- `metadata` json nullable dan tidak boleh memuat secret.
- timestamps.

## Lifecycle Rules

- Catatan baru dapat dibuat sebagai `draft` atau langsung `submitted`.
- Catatan `draft` dan `needs_revision` dapat diedit.
- Catatan `submitted` menunggu verifikasi.
- Catatan `verified` menjadi riwayat resmi dan tidak diedit langsung.
- Jika ada kesalahan pada `verified`, gunakan lifecycle koreksi/void yang
  meninggalkan revision history.
- Void wajib alasan.
- Verifikasi wajib menyimpan actor dan waktu.

## Validation Rules

- Santri wajib aktif saat catatan dibuat.
- Kategori wajib aktif saat dipakai.
- Pembina/pendamping, bila dipilih, wajib berasal dari pegawai aktif.
- Minimal ada salah satu tanggal: `achieved_on` atau rentang periode.
- Jika rentang periode dipakai, tanggal selesai tidak boleh sebelum tanggal
  mulai.
- `title`, `level`, dan `result` wajib ringkas dan mudah dibaca.
- Reason untuk verify/void/revision tidak boleh memuat password, token,
  credential, atau payload sensitif.

## Permission Model

| Permission | Makna |
| --- | --- |
| `prestasi_santri.view` | Melihat daftar dan detail prestasi. |
| `prestasi_santri.manage` | Mengelola kategori prestasi. |
| `prestasi_santri.record` | Membuat, mengubah, dan submit catatan prestasi. |
| `prestasi_santri.verify` | Memverifikasi atau meminta revisi catatan. |
| `prestasi_santri.archive` | Arsip kategori atau void catatan prestasi. |

Backend adalah authority permission. Frontend hanya menyembunyikan/menampilkan
aksi untuk UX.

## API Contract Candidate

API read:

- `GET /api/v1/pesantrian/prestasi-santri`
- `GET /api/v1/pesantrian/prestasi-santri/{achievement}`

API mutation:

- `POST /api/v1/pesantrian/prestasi-santri/categories`
- `PATCH /api/v1/pesantrian/prestasi-santri/categories/{category}`
- `PATCH /api/v1/pesantrian/prestasi-santri/categories/{category}/archive`
- `POST /api/v1/pesantrian/prestasi-santri`
- `PATCH /api/v1/pesantrian/prestasi-santri/{achievement}`
- `PATCH /api/v1/pesantrian/prestasi-santri/{achievement}/submit`
- `PATCH /api/v1/pesantrian/prestasi-santri/{achievement}/verify`
- `PATCH /api/v1/pesantrian/prestasi-santri/{achievement}/void`

Semua mutation memakai idempotency key pada API dan meninggalkan audit event.

## UI Acceptance

- Page `Index.tsx` dan `Show.tsx` tetap tipis.
- Komponen business-specific berada di
  `resources/js/pages/Pesantrian/PrestasiSantri/components/`.
- UI list menampilkan summary, filter, table desktop, card mobile, empty state,
  dan pagination.
- UI detail menampilkan data santri, kategori, kegiatan, hasil, status,
  verifikasi, dan revision history.
- Mutation memakai dialog dengan validasi client ringan, tetapi validasi final
  tetap dari backend.
- Console browser bersih dari Ziggy/route error.

## Seeder Acceptance

- Seeder idempotent.
- Tidak berjalan di production.
- Memakai data demo Santri, HumanResource, dan AcademicPeriod bila tersedia.
- Menyediakan contoh status draft, submitted, verified, needs_revision, dan
  void.
- Menyediakan kategori aktif dan kategori arsip.

## Open Questions

- Format nomor prestasi final mengikuti `PRS-xxxx`, `PRES-xxxx`, atau setting
  khusus module?
- Apakah verifikasi awal cukup oleh operator/admin, atau perlu role pembina
  khusus?
- Apakah prestasi Tahfidz dicatat di module Prestasi juga, atau hanya prestasi
  eksternal yang bukan setoran hafalan?

Keputusan sementara untuk coding awal:

- Nomor prestasi memakai auto-generate sederhana dengan prefix candidate
  `PRS-`.
- Verifikasi awal dilakukan admin/operator yang punya permission
  `prestasi_santri.verify`.
- Prestasi Tahfidz boleh dicatat bila berbentuk penghargaan/lomba; setoran dan
  target hafalan tetap milik module `Tahfidz`.
