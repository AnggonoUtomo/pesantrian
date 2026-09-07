# Tahfidz / Hafalan

Source module akan mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md)
dan [`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas Module

- Namespace teknis: `Pesantrian`
- Module teknis: `Tahfidz`
- Nama tampil: `Tahfidz / Hafalan`
- Candidate source: `app/Modules/Pesantrian/Tahfidz/`
- Candidate frontend: `resources/js/pages/Pesantrian/Tahfidz/`
- Status: Active - demo seeder ready

## Tujuan

Module `Pesantrian/Tahfidz` mengelola target, setoran, murojaah, capaian, dan
monitoring hafalan santri. Bahasa layar memakai istilah yang umum dipahami
operator pesantren: `Tahfidz / Hafalan`.

Baseline awal dibuat sederhana dulu: pembimbing atau operator mencatat setoran
hafalan dan murojaah santri, memantau progres per santri/periode, lalu melihat
ringkasan capaian. Detail tajwid/penilaian lanjutan dapat ditambahkan setelah
alur inti stabil.

## Boundary

### Dimiliki Tahfidz

- Program atau kelompok tahfidz.
- Target hafalan per santri/periode.
- Setoran hafalan baru.
- Murojaah atau pengulangan hafalan.
- Status hasil setoran minimum.
- Catatan pembimbing.
- Ringkasan progres hafalan.
- Audit pencatatan dan koreksi setoran.

### Tidak Dimiliki Tahfidz

- Data induk santri dan wali.
- Penempatan santri ke kelas/rombel.
- Penempatan santri ke asrama/kamar.
- Presensi santri harian.
- Jadwal pelajaran/detail jam mengajar.
- Nilai akademik rapor formal.
- Sertifikat/ijazah tahfidz.
- Konseling/pembinaan non-hafalan.
- Lampiran file bukti tilawah atau audio.

## Dependency

- `Pesantrian/Santri`: sumber santri aktif untuk selector dan validasi.
- `HumanResource/HumanResource`: sumber pembimbing, ustadz, atau musyrif aktif.
- `Academic/AcademicPeriod`: periode pencatatan target dan capaian bila
  diperlukan.
- `Academic/KelasRombel`: filter konteks rombel untuk monitoring, bukan pemilik
  rule hafalan.
- `Pesantrian/Asrama`: filter konteks asrama untuk monitoring, bukan pemilik
  rule hafalan.
- `System/AccessControl`: otorisasi backend.
- `System/AuditLog`: audit pencatatan, koreksi, dan lifecycle data.

Dependency lintas module wajib melalui public contract/Application DTO yang
tersedia. Tahfidz tidak boleh membaca model Eloquent Infrastructure module lain
secara langsung.

Contract awal yang sudah siap dipakai:

- `ActiveStudentReader` dari `Pesantrian/Santri` untuk selector dan validasi
  santri aktif.
- `ActiveEmployeeReader` dari `HumanResource/HumanResource` untuk selector dan
  validasi pembimbing aktif.
- `ActiveAcademicPeriodReader` dari `Academic/AcademicPeriod` untuk periode
  aktif.

## Konsep Data Awal

Baseline awal memakai konsep berikut:

- `program`: kelompok/program tahfidz, misalnya reguler, intensif, atau asrama.
- `target`: rencana hafalan santri pada periode tertentu.
- `setoran`: catatan hafalan baru yang disetorkan santri.
- `murojaah`: catatan pengulangan hafalan yang sudah pernah disetor.
- `capaian`: ringkasan progres yang dihitung dari setoran dan murojaah.

Table foundation yang sudah tersedia:

- `tahfidz_programs`
- `tahfidz_targets`
- `tahfidz_submissions`
- `tahfidz_submission_revisions`

Satuan hafalan baseline menggunakan struktur Qur'an yang familiar:

- juz;
- surah;
- ayat mulai;
- ayat selesai.

## Lifecycle Baseline

```text
Program tahfidz aktif
    -> Target hafalan santri dibuat
    -> Santri melakukan setoran atau murojaah
    -> Pembimbing memberi status dan catatan
    -> Progres santri direkap
    -> Koreksi dilakukan dengan audit bila ada kesalahan input
```

Status hasil setoran minimum:

- `draft`: catatan belum final.
- `submitted`: setoran sudah dicatat.
- `accepted`: setoran diterima.
- `needs_revision`: perlu diulang/diperbaiki.
- `void`: catatan dibatalkan karena salah input.

## Public Boundary Candidate

Public boundary keluar dari Tahfidz ditunda sampai consumer nyata ada. Candidate
yang mungkin dibutuhkan nanti:

- ringkasan capaian tahfidz santri untuk dashboard;
- progres hafalan untuk laporan wali/santri;
- indikator santri yang perlu pembinaan lanjutan;
- data capaian untuk sertifikat atau alumni.

Public boundary internal yang sudah tersedia:

- `TahfidzReadRepository`
- `TahfidzMutationRepository`
- `TahfidzActivityPublisher`
- `ListTahfidzSubmissions`
- `ShowTahfidzSubmission`
- `CreateTahfidzProgram`
- `UpdateTahfidzProgram`
- `CreateTahfidzTarget`
- `UpdateTahfidzTarget`
- `CreateTahfidzSubmission`
- `UpdateTahfidzSubmission`
- `ReviewTahfidzSubmission`
- `VoidTahfidzSubmission`

API internal:

- `GET /api/v1/pesantrian/tahfidz`
- `GET /api/v1/pesantrian/tahfidz/{submission}`
- `POST /api/v1/pesantrian/tahfidz/programs`
- `PATCH /api/v1/pesantrian/tahfidz/programs/{program}`
- `POST /api/v1/pesantrian/tahfidz/targets`
- `PATCH /api/v1/pesantrian/tahfidz/targets/{target}`
- `POST /api/v1/pesantrian/tahfidz/submissions`
- `PATCH /api/v1/pesantrian/tahfidz/submissions/{submission}`
- `PATCH /api/v1/pesantrian/tahfidz/submissions/{submission}/review`
- `PATCH /api/v1/pesantrian/tahfidz/submissions/{submission}/void`

## Data Demo

Seeder demo tersedia di
`app/Modules/Pesantrian/Tahfidz/Database/Seeders/TahfidzDemoSeeder.php` dan
dipanggil dari `DatabaseSeeder`.

Data demo mencakup:

- program aktif `DEMO-THF-REG`;
- program nonaktif/arsip `DEMO-THF-INT`;
- target aktif dan cancelled untuk santri demo;
- setoran accepted, submitted, needs_revision, dan void;
- contoh murojaah;
- revision history untuk review dan void.

## Permission Candidate

- `tahfidz.view`
- `tahfidz.manage`
- `tahfidz.record`
- `tahfidz.review`
- `tahfidz.archive`

Permission dapat disesuaikan saat implementation kalau terlalu granular, tetapi
backend tetap menjadi authority otorisasi.

## UI Baseline

UI akan berada di `resources/js/pages/Pesantrian/Tahfidz/`.

Struktur komponen wajib menjaga page tetap tipis:

```text
resources/js/pages/Pesantrian/Tahfidz/
|-- pages/
|   |-- Index.tsx
|   `-- Show.tsx
`-- components/
    |-- TahfidzDashboard.tsx
    |-- TahfidzFilters.tsx
    |-- TahfidzTable.tsx
    |-- TahfidzDetailPanel.tsx
    |-- TahfidzMutationDialogs.tsx
    |-- TahfidzStatusBadge.tsx
    |-- TahfidzSummaryCards.tsx
    `-- TahfidzPagination.tsx
```

Menu sidebar berada di namespace Pesantrian dengan nama tampil
`Tahfidz / Hafalan`.

## Dokumentasi Terkait

- [`specification.md`](specification.md)
- [`plan.md`](plan.md)
- [`tasks.md`](tasks.md)
- [`../Santri/`](../Santri/)
- [`../../../modules/HumanResource/HumanResource/`](../../../modules/HumanResource/HumanResource/)
- [`../../../modules/Academic/AcademicPeriod/`](../../../modules/Academic/AcademicPeriod/)
- [`../../../modules/Academic/KelasRombel/`](../../../modules/Academic/KelasRombel/)
- [`../Asrama/`](../Asrama/)

## Verifikasi Saat Implementasi

```bash
php artisan module:make Pesantrian Tahfidz --dry-run --json --no-ansi
php artisan module:make Pesantrian Tahfidz --force --yes --no-ansi
php artisan module:validate
php artisan test --filter=Tahfidz
npm run types:check
npm run lint:check
npm run build
```
