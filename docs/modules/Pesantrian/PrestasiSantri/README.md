# Prestasi

Source module akan mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md)
dan [`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas Module

- Namespace teknis: `Pesantrian`
- Module teknis: `PrestasiSantri`
- Nama tampil: `Prestasi`
- Candidate source: `app/Modules/Pesantrian/PrestasiSantri/`
- Candidate frontend: `resources/js/pages/Pesantrian/PrestasiSantri/`
- Status: Active - data foundation ready

## Tujuan

Module `Pesantrian/PrestasiSantri` mengelola catatan prestasi santri secara
operasional. Bahasa layar memakai istilah sederhana untuk operator pesantren:
`Prestasi`.

Baseline awal dibuat sebagai pencatatan prestasi yang mudah dipakai: operator
memilih santri, mengisi jenis/kategori prestasi, tingkat, nama kegiatan, tanggal
atau periode, penyelenggara, pembina pendamping bila ada, lalu menyimpan status
catatan. Lampiran bukti dan sertifikat ditunda sampai module `Support/Document`
tersedia.

## Boundary

### Dimiliki Prestasi

- Kategori prestasi.
- Catatan prestasi santri.
- Tingkat prestasi, misalnya internal, kecamatan, kabupaten, provinsi,
  nasional, atau internasional.
- Jenis capaian, misalnya juara, peserta terbaik, penghargaan, publikasi, atau
  delegasi.
- Status verifikasi catatan prestasi.
- Snapshot santri saat prestasi dicatat.
- Snapshot pembina/pendamping bila tersedia.
- Audit pencatatan, koreksi, verifikasi, dan pembatalan catatan.

### Tidak Dimiliki Prestasi

- Data induk santri dan wali.
- Nilai akademik rapor formal.
- Target hafalan dan setoran tahfidz.
- Pelanggaran/kedisiplinan.
- Konseling atau catatan pembinaan pribadi.
- Kesehatan/klinik.
- Sertifikat digital, file bukti, dan controlled download.
- Publikasi pengumuman prestasi ke wali/santri.

## Dependency

- `Pesantrian/Santri`: sumber santri aktif untuk selector dan validasi.
- `HumanResource/HumanResource`: sumber pembina/pendamping aktif bila prestasi
  membutuhkan pendamping.
- `Academic/AcademicPeriod`: konteks tahun ajaran/periode untuk rekap.
- `System/AccessControl`: otorisasi backend.
- `System/AuditLog`: audit pencatatan dan lifecycle prestasi.
- `Support/Document`: kandidat dependency lampiran bukti nanti, belum dipakai
  pada baseline awal.

Dependency lintas module wajib melalui public contract/Application DTO yang
tersedia. PrestasiSantri tidak boleh membaca model Eloquent Infrastructure
module lain secara langsung.

Contract awal yang dapat dipakai:

- `ActiveStudentReader` dari `Pesantrian/Santri`.
- `ActiveEmployeeReader` dari `HumanResource/HumanResource`.
- `ActiveAcademicPeriodReader` dari `Academic/AcademicPeriod`.

## Konsep Data Awal

Baseline awal memakai konsep berikut:

- `kategori`: kelompok prestasi, misalnya akademik, tahfidz, olahraga, seni,
  bahasa, kepemimpinan, atau lainnya.
- `prestasi`: catatan capaian santri pada kegiatan tertentu.
- `tingkat`: level kegiatan atau penghargaan.
- `hasil`: capaian yang didapat, misalnya juara 1, juara 2, finalis, peserta
  terbaik, atau delegasi.
- `status`: status catatan prestasi.
- `verification`: proses memastikan catatan layak masuk riwayat resmi santri.

Table foundation yang sudah tersedia:

- `student_achievement_categories`
- `student_achievements`
- `student_achievement_revisions`

## Lifecycle Baseline

```text
Kategori prestasi aktif
    -> Catatan prestasi dibuat sebagai draft
    -> Catatan dilengkapi dan disubmit
    -> Operator/otoritas memverifikasi
    -> Prestasi masuk riwayat resmi santri
    -> Koreksi atau pembatalan dilakukan dengan alasan dan audit
```

Status catatan minimum:

- `draft`: catatan awal dan belum final.
- `submitted`: catatan sudah diajukan untuk dicek.
- `verified`: catatan sudah disahkan sebagai riwayat prestasi.
- `needs_revision`: catatan perlu diperbaiki.
- `void`: catatan dibatalkan karena salah input atau tidak valid.

## Public Boundary Candidate

Public boundary keluar dari PrestasiSantri ditunda sampai consumer nyata ada.
Candidate yang mungkin dibutuhkan nanti:

- ringkasan prestasi santri untuk dashboard profil;
- riwayat prestasi untuk rapor/laporan wali;
- data prestasi untuk alumni;
- indikator santri berprestasi untuk pengumuman atau beasiswa.

Public boundary internal yang direncanakan:

- `StudentAchievementReadRepository`
- `StudentAchievementMutationRepository`
- `StudentAchievementActivityPublisher`
- `ListStudentAchievements`
- `ShowStudentAchievement`
- `CreateStudentAchievementCategory`
- `UpdateStudentAchievementCategory`
- `ArchiveStudentAchievementCategory`
- `CreateStudentAchievementDraft`
- `UpdateStudentAchievementDraft`
- `SubmitStudentAchievementDraft`
- `VerifyStudentAchievement`
- `VoidStudentAchievement`

## API Candidate

API internal kandidat:

- `GET /api/v1/pesantrian/prestasi-santri`
- `GET /api/v1/pesantrian/prestasi-santri/{achievement}`
- `POST /api/v1/pesantrian/prestasi-santri/categories`
- `PATCH /api/v1/pesantrian/prestasi-santri/categories/{category}`
- `PATCH /api/v1/pesantrian/prestasi-santri/categories/{category}/archive`
- `POST /api/v1/pesantrian/prestasi-santri`
- `PATCH /api/v1/pesantrian/prestasi-santri/{achievement}`
- `PATCH /api/v1/pesantrian/prestasi-santri/{achievement}/submit`
- `PATCH /api/v1/pesantrian/prestasi-santri/{achievement}/verify`
- `PATCH /api/v1/pesantrian/prestasi-santri/{achievement}/void`

Route web Inertia kandidat:

- `GET /pesantrian/prestasi-santri`
- `GET /pesantrian/prestasi-santri/{achievement}`
- mutation web mengikuti API internal dan tetap memakai Application action yang
  sama.

## Permission Candidate

- `prestasi_santri.view`
- `prestasi_santri.manage`
- `prestasi_santri.record`
- `prestasi_santri.verify`
- `prestasi_santri.archive`

Permission dapat disesuaikan saat implementation kalau terlalu granular, tetapi
backend tetap menjadi authority otorisasi.

## UI Baseline

UI akan berada di `resources/js/pages/Pesantrian/PrestasiSantri/`.

Struktur komponen wajib menjaga page tetap tipis:

```text
resources/js/pages/Pesantrian/PrestasiSantri/
|-- pages/
|   |-- Index.tsx
|   `-- Show.tsx
`-- components/
    |-- PrestasiSantriDashboard.tsx
    |-- PrestasiSantriFilters.tsx
    |-- PrestasiSantriTable.tsx
    |-- PrestasiSantriDetailPanel.tsx
    |-- PrestasiSantriActionBar.tsx
    |-- PrestasiSantriFormFields.tsx
    |-- PrestasiSantriCategoryDialog.tsx
    |-- PrestasiSantriMutationDialog.tsx
    |-- PrestasiSantriLifecycleDialogs.tsx
    |-- PrestasiSantriStatusBadge.tsx
    |-- PrestasiSantriSummaryCards.tsx
    `-- PrestasiSantriPagination.tsx
```

Menu sidebar berada di namespace Pesantrian dengan nama tampil `Prestasi`.

UI read/list awal harus mendukung:

- halaman daftar prestasi;
- halaman detail prestasi;
- filter pencarian, kategori, tingkat, status, tahun ajaran, dan rentang
  tanggal;
- summary cards, table desktop, card mobile, empty state, dan pagination;
- menu sidebar namespace Pesantrian.

UI mutation awal harus mendukung:

- dialog buat/ubah kategori;
- dialog buat/ubah catatan prestasi;
- submit catatan;
- verifikasi catatan;
- pembatalan catatan dengan alasan.

Backend tetap menjadi authority permission dan validasi. Frontend hanya
mengatur pengalaman pengguna.

## Data Demo

Seeder demo direncanakan di
`app/Modules/Pesantrian/PrestasiSantri/Database/Seeders/PrestasiSantriDemoSeeder.php`
dan dipanggil dari `DatabaseSeeder`.

Data demo minimum:

- kategori prestasi aktif dan arsip;
- prestasi draft, submitted, verified, needs_revision, dan void;
- contoh prestasi akademik, tahfidz, olahraga/seni;
- snapshot santri aktif;
- pembina pendamping bila tersedia;
- revision history untuk verifikasi dan pembatalan.

## QA Browser dan User Manual

QA browser direncanakan di `tests/Browser/prestasi-santri.spec.ts` dengan
fixture khusus di `tests/Browser/support/prestasi-santri-fixture.ts`.

Coverage QA:

- desktop Chromium dan mobile Chromium;
- login operator fixture;
- buka list dan filter prestasi;
- buka dialog kategori dan catatan prestasi;
- buka detail prestasi submitted/verified;
- cek tombol edit, submit, verify, dan void;
- accessibility gate untuk issue critical/serious;
- console error, page error, dan response 403 harus kosong.

Manual penggunaan end-to-end akan diperbarui di
[`../../../USER-MANUAL-LIFECYCLE.md`](../../../USER-MANUAL-LIFECYCLE.md) saat UI
siap diuji manual.

Keterbatasan baseline yang sengaja belum dibuat:

- upload sertifikat/file bukti;
- publikasi prestasi ke portal wali/santri;
- integrasi alumni;
- beasiswa atau reward finansial;
- laporan prestasi kompleks.

## Dokumentasi Terkait

- [`specification.md`](specification.md)
- [`plan.md`](plan.md)
- [`tasks.md`](tasks.md)
- [`../Santri/`](../Santri/)
- [`../../../modules/HumanResource/HumanResource/`](../../../modules/HumanResource/HumanResource/)
- [`../../../modules/Academic/AcademicPeriod/`](../../../modules/Academic/AcademicPeriod/)
- [`../Tahfidz/`](../Tahfidz/)
- [`../KedisiplinanSantri/`](../KedisiplinanSantri/)

## Verifikasi Saat Implementasi

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
